<?php
namespace Mayflower\JiraIssueVoteBundle\Service;

/**
 * Interface which defines the method of an loader which loads issues and votes from an
 * issue tracker like JIRA
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
interface JiraPropertyLoaderInterface
{
    /**
     * Returns a list which contains the favourite issue filters of the user
     *
     * @return mixed[]
     *
     * @api
     */
    public function loadFavouriteIssues();

    /**
     * Loads the issues from a filter
     *
     * @param string|integer $filterId Id of the filter
     *
     * @return mixed[]
     *
     * @api
     */
    public function loadIssuesByFilterId($filterId);

    /**
     * Returns the host name of the issue tracker
     *
     * @return string
     *
     * @api
     */
    public function getJiraHost();

    /**
     * Returns the current user session
     *
     * @return mixed[]
     *
     * @api
     */
    public function getCurrentUser();

    /**
     * Votes an issue by its id
     *
     * @param string|integer $issueId Id of the issue to vote
     *
     * @return mixed[]
     *
     * @api
     */
    public function voteIssue($issueId);

    /**
     * Removes the vote of the current user by one id
     *
     * @param string|integer $issueId Id of the issue to unvote
     *
     * @return mixed[]
     *
     * @api
     */
    public function unvoteIssue($issueId);
} 