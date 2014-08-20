<?php

namespace Ma27\Jira\IssueVoteBundle\Features\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Mink;
use Behat\Mink\Session as MinkSession;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Ma27\Jira\IssueVoteBundle\Controller\IssueController;
use Ma27\Jira\IssueVoteBundle\Entity\Filter;
use Ma27\Jira\IssueVoteBundle\Entity\FilterCollection;
use Ma27\Jira\IssueVoteBundle\Entity\Issue;
use Ma27\Jira\IssueVoteBundle\Entity\IssueCollection;
use Ma27\Jira\IssueVoteBundle\Entity\OAuthConsumer;
use Ma27\Jira\IssueVoteBundle\EventListener\JiraCredentialsListener;
use Ma27\Jira\IssueVoteBundle\Util\OAuthSecurityProxy;
use Mockery;
use PHPUnit_Framework_Assert as Test;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Behat context class.
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class FeatureContext implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var \Behat\Mink\Session
     */
    private $minkSession;

    /**
     * @var \Behat\Mink\Mink
     */
    private $mink;

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var OAuthConsumer
     */
    private $consumer;


    /**
     * @var IssueCollection
     */
    private $issues;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /** @BeforeScenario */
    public function setUp()
    {
        if (null === $this->kernel) {
            throw new \RuntimeException('Kernel not set!');
        }
        $this->container = $this->kernel->getContainer();
        $this->container->set('session', new Session(new MockArraySessionStorage()));
        $this->mink = new Mink(
            [
                'browserkit' => new MinkSession(new BrowserKitDriver(new Client($this->kernel)))
            ]
        );
        $this->mink->setDefaultSessionName('browserkit');
        $this->minkSession = $this->mink->getSession();
        if (!$this->minkSession->isStarted()) {
            $this->minkSession->start();
        }

        // container build process
        $iM = Mockery::mock('\Ma27\Jira\IssueVoteBundle\Service\IssueManager');
        $this->container->set('ma27_jira_issue_vote.issue.manager', $iM);
    }

    /** @AfterScenario */
    public function tearDown()
    {
        $this->minkSession->stop();
        $this->container = null;

        Mockery::close();
    }

    /**
     * @Given I have an account with the following values:
     */
    public function iHaveAnAccountWithTheFollowingValues(TableNode $table)
    {
        $credentialRow = $table->getHash()[0];

        $this->consumer = new OAuthConsumer();
        $this->consumer->setName($credentialRow['name']);

        $iM = $this->container->get('ma27_jira_issue_vote.issue.manager');
        $iM->shouldReceive('getCurrentUser')->andReturn($this->consumer);

        $this->container->set('ma27_jira_issue_vote.issue.manager', $iM);
    }

    /**
     * @Given there are issues:
     */
    public function thereAreIssues(TableNode $table)
    {
        $filters = array();

        foreach ($table->getHash() as $node) {
            $issue = new Issue();
            $issue->setId($node['id']);
            $issue->setDescription($node['description']);
            $issue->setSummary($node['summary']);
            $issue->setViewLink($node['viewLink'] . $node['key']);
            $issue->setReporter($node['reporter']);
            $issue->setVoteCount($node['voteCount']);
            $issue->setVoted($node['voted'] === 'true' ? true : false);
            $filters[$node['filterId']][] = $issue;
        }

        $iM = $this->container->get('ma27_jira_issue_vote.issue.manager');
        $list = new IssueCollection();
        foreach ($filters as $filterId => $issueStack) {
            $iM->shouldReceive('getIssuesByFilterId')->with($filterId)->andReturn(new IssueCollection($issueStack));
            $list->add($issueStack);
        }
        $this->container->set('ma27_jira_issue_vote.issue.manager', $iM);
        $this->issues = $list;
    }

    /**
     * @Given there are filters:
     */
    public function thereAreFilters(TableNode $table)
    {
        $filterStack = new FilterCollection();

        foreach ($table->getHash() as $node) {
            $filter = new Filter();
            $filter->setId($node['filterId']);
            $filter->setName($node['filterName']);
            $filter->setOwnerName($node['owner']);
            $filter->setViewUrl($node['viewLink']);

            $filterStack->set($filter);
        }
        $iM = $this->container->get('ma27_jira_issue_vote.issue.manager');
        $iM->shouldReceive('getFavouriteFilters')->andReturn($filterStack);
        $this->container->set('ma27_jira_issue_vote.issue.manager', $iM);

        Test::assertEquals($filterStack, $this->container->get('ma27_jira_issue_vote.issue.manager')->getFavouriteFilters());
    }

    /**
     * @When I'm logged in
     */
    public function iMLoggedIn()
    {
        $session = $this->container->get('session');
        $session->set(
            OAuthSecurityProxy::TEMP_TOKEN_ID, $user = $this->container->get('ma27_jira_issue_vote.issue.manager')->getCurrentUser()
        );

        Test::assertEquals($this->container->get('session')->get(OAuthSecurityProxy::TEMP_TOKEN_ID)->getName(), $user->getName());
    }

    /**
     * @When I'm on page :arg1
     */
    public function iMOnPage($arg1)
    {
        $this->minkSession->visit($arg1);
    }

    /**
     * @When I have selected the filter :arg1
     */
    public function iHaveSelectedTheFilter($arg1)
    {
        $session = $this->container->get('session');
        $session->set(IssueController::SELECTED_FILTER_ID, (int)$arg1);
    }

    /**
     * @Then I should see a list of issues from filter :arg1
     */
    public function iShouldSeeAListOfIssuesFromFilter($arg1)
    {
        $issues = $this->container->get('ma27_jira_issue_vote.issue.manager')->getIssuesByFilterId((int)$arg1);

        $crawler = new Crawler($this->minkSession->getPage()->getContent());
        $consumer = $this->container->get('ma27_jira_issue_vote.issue.manager')->getCurrentUser()->getName();
        /** @var $issue Issue */
        foreach (
            $issues->filter(
                false, false, $this->container->get('ma27_jira_issue_vote.issue.manager')->getCurrentUser()
            )->all() as $issue
        ) {
            $id = $issue->getId();
            Test::assertTrue(count($crawler->filter(sprintf('li#' . $id . ' b:contains("%s")', $issue->getSummary()))) > 0);
            $description = $issue->getDescription();

            if (!empty($description)) {
                Test::assertTrue(count($crawler->filter(sprintf('li#' . $id . ' span:contains("%s")', $description))) > 0);
            } else {
                Test::assertTrue(count($crawler->filter(sprintf('li#' . $id . ' i:contains("%s")', 'No description available'))) > 0);
            }

            if (!$issue->hasUserVoted()) {
                Test::assertTrue(count($crawler->filter(sprintf('li#' . $id . ' button:contains("%s")', 'Vote'))) > 0);
            } else if ($consumer === $issue->getReporter()) {
                Test::assertTrue(count($crawler->filter(sprintf('li#' . $id . ' button.disabled:contains("%s")', 'You are the reporter'))) > 0);
            } else if ($issue->hasUserVoted()) {
                Test::assertTrue(count($crawler->filter(sprintf('li#' . $id . ' button:contains("%s")', 'Unvote'))));
            }
        }
    }

    /**
     * @When my credentials are expired
     */
    public function myCredentialsAreExpired()
    {
        $this->container->get('ma27_jira_issue_vote.oauth.proxy')->removeToken();
        $this->container->get('session')->remove(JiraCredentialsListener::OAUTH_LOGIN_FLAG);
    }

    /**
     * @Then I should be redirected
     */
    public function iShouldBeRedirected()
    {
        return true;
    }

    /**
     * @When I didn't vote issue :arg1
     */
    public function iDidnTVoteIssue($arg1)
    {
        foreach ($this->issues->all() as $issue) {
            if ($arg1 == $issue->getId()) {
                Test::assertFalse(
                    (boolean)$issue->hasUserVoted()
                );
                break;
            }
        }
    }

    /**
     * @When I press :arg1
     */
    public function iPress($arg1)
    {
        switch ($arg1) {
            case 'vote':

                break;
            case 'unvote':

                break;
            default:

                break;
        }
    }

    /**
     * @Then I should have voted
     */
    public function iShouldHaveVoted()
    {
        throw new PendingException();
    }

    /**
     * @When I have voted issue :arg1
     */
    public function iHaveVotedIssue($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then I should have removed by vote
     */
    public function iShouldHaveRemovedByVote()
    {
        throw new PendingException();
    }
}
