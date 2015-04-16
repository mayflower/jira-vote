<?php
namespace Mayflower\JiraIssueVoteBundle\Util\Generator;

/**
 * Simple url generator which merges a server path with the current stored host
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class Url
{
    /**
     * Current host
     * @var string
     */
    private $host;

    /**
     * Stores a new host
     *
     * @param string $host New host
     *
     * @api
     */
    public function __construct($host)
    {
        $this->host = (string)$host;
    }

    /**
     * Creates the url
     *
     * @param string $path Path to merge
     *
     * @return string
     *
     * @api
     */
    public function generateUrl($path)
    {
        return $this->host . '/' . $path;
    }
}
