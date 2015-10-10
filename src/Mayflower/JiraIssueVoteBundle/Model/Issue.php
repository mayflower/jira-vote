<?php
namespace Mayflower\JiraIssueVoteBundle\Model;

/**
 * Object which represents an issue of the issue tracker
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class Issue
{
    /**
     * Issue id
     * @var integer
     */
    private $id;

    /**
     * Issue text
     * @var string
     */
    private $description;

    /**
     * Issue title
     * @var string
     */
    private $summary;

    /**
     * Link to the actual issue
     * @var string
     */
    private $viewLink;

    /**
     * Name of the reporter
     * @var string
     */
    private $reporter;

    /**
     * Number of votes
     * @var integer
     */
    private $voteCount;

    /**
     * Has user voted
     * @var boolean
     */
    private $hasVoted = false;

    /**
     * Is issue resolved
     * @var boolean
     */
    private $resolution = false;

    /**
     * Creation date
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $issueType = 'Story';

    /**
     * @var string
     */
    private $issueKey;

    /**
     * @var string
     */
    private $status;

    /**
     * Factory which creates a issue with from the decoded result of the jira api
     *
     * @param array $issue
     * @param string $host
     *
     * @return self
     */
    public static function fill(array $issue, $host)
    {
        $issueDomain = new self;

        $issueDomain->setId((int)$issue['id']);
        $issueDomain->setSummary($issue['fields']['summary']);
        $issueDomain->setDescription($issue['fields']['description']);
        $issueDomain->setViewLink($host . '/browse/' . $issue['key']);
        $issueDomain->setReporter($issue['fields']['reporter']['displayName']);
        $issueDomain->setVoted($issue['fields']['votes']['hasVoted']);
        $issueDomain->setVoteCount($issue['fields']['votes']['votes']);
        $issueDomain->setResolution(null === $issue['fields']['resolution']);
        $issueDomain->setCreated((new \DateTime($issue['fields']['created']))->format('m/d/Y h:i A'));
        $issueDomain->setIssueKey($issue['key']);
        $issueDomain->setIssueType($issue['fields']['issuetype']['name']);
        $issueDomain->setStatus($issue['fields']['status']['name']);

        return $issueDomain;
    }

    /**
     * Sets the creation date
     *
     * @param string $created
     *
     * @return void
     *
     * @api
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Returns the creation date
     *
     * @return string
     *
     * @api
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return integer
     */
    public function getCreationDateTimestamp()
    {
        return $this->created->getTimestamp();
    }

    /**
     * Sets the resolution state
     *
     * @param boolean $resolution
     *
     * @return void
     *
     * @api
     */
    public function setResolution($resolution)
    {
        $this->resolution = (bool)$resolution;
    }

    /**
     * Returns the resolution state
     *
     * @return boolean
     *
     * @api
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * Sets the reporter of the issue
     *
     * @param string $reporter
     *
     * @return void
     *
     * @api
     */
    public function setReporter($reporter)
    {
        $this->reporter = (string)$reporter;
    }

    /**
     * Returns the reporter of the issue
     *
     * @return string
     *
     * @api
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Sets the view link of the issue
     *
     * @param string $viewLink
     *
     * @return void
     *
     * @api
     */
    public function setViewLink($viewLink)
    {
        $this->viewLink = (string)$viewLink;
    }

    /**
     * Returns the view link of the issue
     *
     * @return string
     *
     * @api
     */
    public function getViewLink()
    {
        return $this->viewLink;
    }

    /**
     * Sets the title of the issue
     *
     * @param string $summary
     *
     * @return void
     *
     * @api
     */
    public function setSummary($summary)
    {
        $this->summary = (string)$summary;
    }

    /**
     * Returns the title of the issue
     *
     * @return string
     *
     * @api
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Sets the content of the issue
     *
     * @param string $description
     *
     * @return void
     *
     * @api
     */
    public function setDescription($description)
    {
        $this->description = (string)$description;
    }

    /**
     * Returns the content of the issue
     *
     * @return string
     *
     * @api
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the id of the issue
     *
     * @param string|integer $id
     *
     * @return void
     *
     * @api
     */
    public function setId($id)
    {
        $this->id = (string)$id;
    }

    /**
     * Returns the id of the issue
     *
     * @return string|integer
     *
     * @api
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Checks the user has voted already this issue
     *
     * @return boolean
     *
     * @api
     */
    public function hasUserVoted()
    {
        return $this->hasVoted;
    }

    /**
     * Sets the voted flag
     *
     * @param boolean $voted
     *
     * @return void
     *
     * @api
     */
    public function setVoted($voted)
    {
        $this->hasVoted = (boolean)$voted;
    }

    /**
     * Sets the count of votes
     *
     * @param integer $voteCount
     *
     * @return void
     *
     * @api
     */
    public function setVoteCount($voteCount)
    {
        $this->voteCount = (int)$voteCount;
    }

    /**
     * Returns the vote count
     *
     * @return integer
     *
     * @api
     */
    public function getVoteCount()
    {
        return $this->voteCount;
    }

    /**
     * @return string
     */
    public function getIssueKey()
    {
        return $this->issueKey;
    }

    /**
     * @param string $issueKey
     */
    public function setIssueKey($issueKey)
    {
        $this->issueKey = (string) $issueKey;
    }

    /**
     * @return string
     */
    public function getIssueType()
    {
        return $this->issueType;
    }

    /**
     * @param string $issueType
     */
    public function setIssueType($issueType)
    {
        $this->issueType = $issueType;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
} 