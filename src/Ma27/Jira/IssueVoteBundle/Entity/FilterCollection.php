<?php
namespace Ma27\Jira\IssueVoteBundle\Entity;

/**
 * Collection which contains a set of filters
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class FilterCollection
{
    /**
     * Storage containing the filters
     * @var \SplObjectStorage
     */
    private $filters;

    /**
     * Constructor
     *
     * @param Filter[] $filters
     *
     * @api
     */
    public function __construct(array $filters = array())
    {
        $this->filters = new \SplObjectStorage();
        $this->add($filters);
    }

    /**
     * Sets a set of filters
     *
     * @param Filter[] $filters Stack to add
     *
     * @return void
     *
     * @api
     */
    public function add(array $filters)
    {
        foreach ($filters as $filter) {
            $this->set($filter);
        }
    }

    /**
     * Adds one filter
     *
     * @param Filter $issue Filter to add
     *
     * @return void
     *
     * @api
     */
    public function set(Filter $issue)
    {
        $this->filters->attach($issue);
    }

    /**
     * Removes one filter
     *
     * @param Filter $issue Filter to remove
     *
     * @return void
     *
     * @api
     */
    public function remove(Filter $issue)
    {
        $this->filters->detach($issue);
    }

    /**
     * Removes a filter by its id
     *
     * @param string|integer $id Id of the filter to remove
     *
     * @return void
     *
     * @throws \LogicException
     *
     * @api
     */
    public function removeById($id)
    {
        $filter = $this->get($id, $default = null);
        if ($default === $filter) {
            throw new \LogicException(sprintf('Issue with id %s not found!', $id));
        }

        $this->remove($filter);
    }

    /**
     * Returns one filter by its id
     *
     * @param string $id      Id of the filter to return
     * @param mixed  $default Default value which will be returned if the filter does not exist
     *
     * @return Filter|mixed
     *
     * @api
     */
    public function get($id, $default = null)
    {
        foreach ($this->filters as $filter) {
            /** @var $filter Filter */
            if ($filter->getId() === $id) {
                return $filter;
            }
        }

        return $default;
    }

    /**
     * Returns all stored filters
     *
     * @param boolean $toArray If this value is true, the filter storage will be converted to an php array
     *
     * @return Filter[]|\SplObjectStorage
     *
     * @api
     */
    public function getAll($toArray = false)
    {
        if (true === $toArray) {
            $result = array();
            /** @var $filter Filter */
            foreach ($this->filters as $filter) {
                $result[] = $filter->toArray();
            }

            return $result;
        }

        return $this->filters;
    }
} 