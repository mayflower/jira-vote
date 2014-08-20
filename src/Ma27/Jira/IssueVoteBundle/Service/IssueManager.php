<?php
namespace Ma27\Jira\IssueVoteBundle\Service;

use Ma27\Jira\IssueVoteBundle\Entity\Filter;
use Ma27\Jira\IssueVoteBundle\Entity\FilterCollection;
use Ma27\Jira\IssueVoteBundle\Entity\Issue;
use Ma27\Jira\IssueVoteBundle\Entity\IssueCollection;
use Ma27\Jira\IssueVoteBundle\Entity\OAuthConsumer;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Manager which handles the issues and votes of any issue tracker
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class IssueManager
{
    /**
     * Loader which returns the raw data from the source
     * @var JiraPropertyLoaderInterface
     */
    private $propertyLoader;

    /**
     * Sets the dependencies of the class
     *
     * @param JiraPropertyLoaderInterface $propertyLoader
     *
     * @api
     */
    public function __construct(JiraPropertyLoaderInterface $propertyLoader)
    {
        $this->propertyLoader = $propertyLoader;
    }

    /**
     * Returns a list of favourite filters
     *
     * @return FilterCollection
     *
     * @api
     */
    public function getFavouriteIssues()
    {
        $list = $this->propertyLoader->loadFavouriteIssues();
        $collection = new FilterCollection();

        foreach ($list as $issueFilter) {
            $filter = new Filter();

            $filter->setId($issueFilter['id']);
            $filter->setName($issueFilter['name']);
            $filter->setOwnerName($issueFilter['owner']['name']);
            $filter->setViewUrl($issueFilter['viewUrl']);

            $collection->set($filter);
        }
        return $collection;
    }

    /**
     * Returns a list of issues by a filter id
     *
     * @param integer $filterId Id of the filter
     *
     * @return IssueCollection
     *
     * @api
     */
    public function getIssuesByFilterId($filterId)
    {
        $issues = $this->propertyLoader->loadIssuesByFilterId($filterId);
        $collection = new IssueCollection();
        $host = $this->propertyLoader->getJiraHost();

        foreach ($issues as $issue) {
            $issueDomain = new Issue();

            $issueDomain->setId((int)$issue['id']);
            $issueDomain->setSummary($issue['fields']['summary']);
            $issueDomain->setDescription($issue['fields']['description']);
            $issueDomain->setViewLink($host . '/browse/' . $issue['key']);
            $issueDomain->setReporter($issue['fields']['reporter']['name']);
            $issueDomain->setVoted($issue['fields']['votes']['hasVoted']);
            $issueDomain->setVoteCount($issue['fields']['votes']['votes']);
            $issueDomain->setResolution(null === $issue['fields']['resolution']);
            $issueDomain->setCreated(new \DateTime($issue['fields']['created']));
            $collection->set($issueDomain);
        }
        return $collection;
    }

    /**
     * Votes an issue
     *
     * @param string|integer $id Id of the issue to vote
     *
     * @return boolean
     *
     * @api
     */
    public function voteIssue($id)
    {
        return $this->propertyLoader->voteIssue($id);
    }

    /**
     * Unvotes an issue
     *
     * @param string|integer $id Id of the issue to unvote
     *
     * @return boolean
     *
     * @api
     */
    public function unvoteIssue($id)
    {
        return $this->propertyLoader->unvoteIssue($id);
    }

    /**
     * Returns the current user in the rest api
     *
     * @return OAuthConsumer
     *
     * @api
     */
    public function getCurrentUser()
    {
        $userObject = new OAuthConsumer();
        $userObject->setName($this->propertyLoader->getCurrentUser()['name']);

        return $userObject;
    }
} 