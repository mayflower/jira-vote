<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Util\Codes;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * ApiController
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class ApiController extends Controller
{
    const SELECTED_FILTER_ID   = 'issue.filter.current';
    const SELECTED_FILTER_NAME = 'issue.filter.current.name';
    const SELECTED_FILTER_TYPE = 'issue.filter.current.type';

    /**
     * Renders the issues as json
     *
     * @param Request $request
     *
     * @return array
     *
     * @View()
     */
    public function loadIssuesAction(Request $request)
    {
        $user   = $this->getOAuthUser();
        $offset = $request->query->get('issue_offset', 0);

        /** @var \Mayflower\JiraIssueVoteBundle\Model\IssueManager $issueManager */
        $issueManager = $this->get('mayflower_model_manager_issue');
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        $filterId = $session->get(self::SELECTED_FILTER_ID);
        if (!$filterId) {
            throw new HttpException(
                Codes::HTTP_FORBIDDEN,
                'Cannot access this resource without configuring a filter id!'
            );
        }

        $issues      = $issueManager->findRecent($filterId, $offset, $request->getSession()->get(self::SELECTED_FILTER_TYPE));
        $issueTypes  = [];
        $issueStates = [];

        foreach ($issues as $issue) {
            $type = $issue->getIssueType();
            if (!in_array($type, $issueTypes)) {
                $issueTypes[] = $type;
            }
            if (!in_array($issue->getStatus(), $issueStates) && !empty($issue->getStatus())) {
                $issueStates[] = $issue->getStatus();
            }
        }

        return [
            'issues'      => $issues,
            'currentUser' => $user,
            'filterName'  => $session->get(self::SELECTED_FILTER_NAME),
            'types'       => $issueTypes,
            'states'      => $issueStates,
        ];
    }

    /**
     * Loads all favourite filters of a specific user
     *
     * @param string $type
     *
     * @return \Mayflower\JiraIssueVoteBundle\Model\IssueSource[]
     *
     * @View()
     */
    public function loadIssueSourceAction($type)
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Model\IssueSourceManager $sourceManager */
        $sourceManager = $this->get('mayflower_model_manager_filter');

        return $sourceManager->findIssueSourceByType($type);
    }

    /**
     * Selects a single resource
     *
     * @param string       $type
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="filterId", requirements="[A-z0-9]+", description="Id of the filter to load")
     */
    public function storeFavouriteFilterAction($type, ParamFetcher $paramFetcher)
    {
        $filterId = $paramFetcher->get('filterId');

        /** @var \Mayflower\JiraIssueVoteBundle\Model\IssueSourceManager $filterManager */
        $filterManager = $this->get('mayflower_model_manager_filter');
        $allFilters    = $filterManager->findIssueSourceByType($type);

        foreach ($allFilters as $filter) {
            if ($filterId !== $filter->getId()) {
                continue;
            }

            /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
            $session = $this->get('session');

            $session->set(self::SELECTED_FILTER_ID, $filter->getId());
            $session->set(self::SELECTED_FILTER_NAME, $filter->getName());
            $session->set(self::SELECTED_FILTER_TYPE, $type);

            return;
        }

        throw new HttpException(Codes::HTTP_BAD_REQUEST, 'Invalid filter id given!');
    }

    /**
     * Fetches the current oauth user
     *
     * @return \Mayflower\JiraIssueVoteBundle\Model\OAuthConsumer
     */
    private function getOAuthUser()
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Model\OAuthConsumerManager $userManager */
        $userManager = $this->get('mayflower_model_manager_user');

        try {
            $user = $userManager->findCurrent();
        } catch (RequestException $ex) {
            throw new HttpException(Codes::HTTP_UNAUTHORIZED, 'You are currently not authorized. Please login again!');
        }

        return $user;
    }
}
