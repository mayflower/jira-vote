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
        $issueDomain->setReporter($issue['fields']['reporter']['name']);
        $issueDomain->setVoted($issue['fields']['votes']['hasVoted']);
        $issueDomain->setVoteCount($issue['fields']['votes']['votes']);
        $issueDomain->setResolution(null === $issue['fields']['resolution']);
        $issueDomain->setCreated(new \DateTime($issue['fields']['created']));

        return $issueDomain;
    }

    /**
     * Sets the creation date
     *
     * @param \DateTime $created
     *
     * @return void
     *
     * @api
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * Returns the creation date
     *
     * @return \DateTime
     *
     * @api
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Formats the creation date to a readable date output
     *
     * @param string $scheme Scheme to transform
     *
     * @return string
     *
     * @api
     */
    public function formatCreationDate($scheme = 'd.m.Y H:i')
    {
        return $this->created->format($scheme);
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
} 