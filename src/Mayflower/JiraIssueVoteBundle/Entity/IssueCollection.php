<?php
namespace Mayflower\JiraIssueVoteBundle\Entity;

/**
 * Collection which represent a set of issues
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class IssueCollection
{
    /**
     * Issue list
     * @var \SplObjectStorage
     */
    private $issues;

    /**
     * Constructor
     *
     * @param Issue[] $issues Issues to set
     *
     * @api
     */
    public function __construct(array $issues = array())
    {
        $this->issues = new \SplObjectStorage();
        $this->add($issues);
    }

    /**
     * Stores a set of issues
     *
     * @param Issue[] $issues Issues to set
     *
     * @return void
     *
     * @api
     */
    public function add(array $issues)
    {
        foreach ($issues as $issue) {
            $this->set($issue);
        }
    }

    /**
     * Sets one issue
     *
     * @param Issue $issue Issue to set
     *
     * @return void
     *
     * @api
     */
    public function set(Issue $issue)
    {
        $this->issues->attach($issue);
    }

    /**
     * Removes one issue
     *
     * @param Issue $issue Issue to remove
     *
     * @return void
     *
     * @api
     */
    public function remove(Issue $issue)
    {
        $this->issues->detach($issue);
    }

    /**
     * Removes one issue by its id
     *
     * @param string|integer $id Id of the issue
     *
     * @return void
     *
     * @throws \LogicException
     *
     * @api
     */
    public function removeById($id)
    {
        $issue = $this->get($id, $default = null);
        if ($default === $issue) {
            throw new \LogicException(sprintf('Issue with id %s not found!', $id));
        }

        $this->remove($issue);
    }

    /**
     * Loads one issue by its id
     *
     * @param string|integer $id      Id of the issue
     * @param mixed          $default Default value which will be returned if the issue does not exist
     *
     * @return Issue|mixed
     *
     * @api
     */
    public function get($id, $default = null)
    {
        foreach ($this->issues as $issue) {
            if ($issue->getId() === $id) {
                return $issue;
            }
        }

        return $default;
    }

    /**
     * Returns all issues
     *
     * @return \SplObjectStorage
     *
     * @api
     */
    public function all()
    {
        return $this->issues;
    }
} 