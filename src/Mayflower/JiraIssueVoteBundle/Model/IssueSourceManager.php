<?php

namespace Mayflower\JiraIssueVoteBundle\Model;

use Mayflower\JiraIssueVoteBundle\Jira\RestHandler;

/**
 * Model manager for issue filters
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class IssueSourceManager
{
    /**
     * @var RestHandler
     */
    private $handler;

    /**
     * @var string
     */
    private $instance;

    /**
     * Constructor
     *
     * @param RestHandler $handler
     * @param string      $jiraInstance
     */
    public function __construct(RestHandler $handler, $jiraInstance)
    {
        $this->handler  = $handler;
        $this->instance = $jiraInstance;
    }

    /**
     * Finds all favourite issues of the current user
     *
     * @param string $type
     *
     * @return IssueSource[]
     */
    public function findIssueSourceByType($type)
    {
        switch ($type) {
            case 'filters':
                $rawFilters = $this->handler->executeApiCall('rest/api/2/filter/favourite');

                return array_map(
                    function (array $data) {
                        return IssueSource::fill($data);
                    },
                    $rawFilters
                );
            case 'projects':
                $projects = $this->handler->executeApiCall('rest/api/2/project');
                $jira     = $this->instance;

                return array_map(
                    function (array $data) use ($jira) {
                        $projectData            = [];
                        $projectData['id']      = $data['key'];
                        $projectData['name']    = $data['name'];
                        $projectData['viewUrl'] = sprintf('%s/browse/%s', $jira, $data['key']);

                        return IssueSource::fill($projectData);
                    },
                    $projects
                );
            default:
                return [];
        }
    }
}
