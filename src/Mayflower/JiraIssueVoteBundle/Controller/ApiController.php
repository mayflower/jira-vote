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

        return [
            'issues'      => $issueManager->findRecentByFilterId($filterId, $offset),
            'currentUser' => $user,
            'filterName'  => $session->get(self::SELECTED_FILTER_NAME),
        ];
    }

    /**
     * Loads all favourite filters of a specific user
     *
     * @return \Mayflower\JiraIssueVoteBundle\Model\Filter[]
     *
     * @View()
     */
    public function loadFiltersAction()
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Model\FilterManager $filterManager */
        $filterManager = $this->get('mayflower_model_manager_filter');

        return $filterManager->findFavouriteFilters();
    }

    /**
     * Selects a single resource
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="filterId", requirements="\d+", description="Id of the filter to load")
     */
    public function selectFilterAction(ParamFetcher $paramFetcher)
    {
        $filterId = $paramFetcher->get('filterId');

        /** @var \Mayflower\JiraIssueVoteBundle\Model\FilterManager $filterManager */
        $filterManager = $this->get('mayflower_model_manager_filter');
        $allFilters    = $filterManager->findFavouriteFilters();

        foreach ($allFilters as $filter) {
            if ($filterId !== $filter->getId()) {
                continue;
            }

            /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
            $session = $this->get('session');

            $session->set(self::SELECTED_FILTER_ID, $filter->getId());
            $session->set(self::SELECTED_FILTER_NAME, $filter->getName());

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
