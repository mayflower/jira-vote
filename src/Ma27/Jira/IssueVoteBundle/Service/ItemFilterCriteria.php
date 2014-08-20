<?php
namespace Ma27\Jira\IssueVoteBundle\Service;

use Ma27\Jira\IssueVoteBundle\Entity\Criteria\FilterLoop;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Object which is able to handle a filter stack to filter items from a list
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class ItemFilterCriteria
{
    /**
     * Loop object containing and executing the filters
     * @var FilterLoop
     */
    private $filterLoop;

    /**
     * Bag which contains all filter attributes
     * @var ParameterBag
     */
    private $attributes;

    /**
     * Creates the internal objects
     *
     * @api
     */
    public function __construct()
    {
        $this->filterLoop = new FilterLoop();
        $this->attributes = new ParameterBag();
    }

    /**
     * Stores an attribute
     *
     * @param string $name  Name of the attribute
     * @param mixed  $value Value of the attribute
     *
     * @return $this
     *
     * @api
     */
    public function set($name, $value)
    {
        $this->attributes->set($name, $value);
        return $this;
    }

    /**
     * Returns a list of all attributes
     *
     * @return mixed[]
     *
     * @api
     */
    public function all()
    {
        return $this->attributes->all();
    }

    /**
     * Removes an attribute
     *
     * @param string $name Name of the attribute to remove
     *
     * @return $this
     *
     * @api
     */
    public function remove($name)
    {
        $this->attributes->remove($name);
        return $this;
    }

    /**
     * Executes the filters and returns the extracted filters
     *
     * @param callable[] $closures  List of filter callables
     * @param mixed[]    $itemStack List of items to filter
     *
     * @return mixed[]
     *
     * @api
     */
    public function processFilter(array $closures, array $itemStack)
    {
        $this->filterLoop->setActions($closures);
        $this->filterLoop->setItems($itemStack);
        $this->filterLoop->setAttributeList($this->all());

        $extractedItems = $this->filterLoop->run();
        $this->filterLoop->reset();

        return $extractedItems;
    }

    /**
     * Clears the attribute
     *
     * @api
     */
    public function flush()
    {
        $this->attributes->clear();
        $this->filterLoop->reset();
    }
} 