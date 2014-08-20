<?php
namespace Ma27\Jira\IssueVoteBundle\Entity\Criteria;

use Closure;
use ArrayObject;

/**
 * Loop aggregate which filters a set of items by a stack of closures
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class FilterLoop
{
    /**
     * List of filter handlers
     * @var Closure[]
     */
    private $closureStack  = array();

    /**
     * List of items to filter
     * @var mixed[]
     */
    private $itemsToFilter = array();

    /**
     * List of attributes of the filters
     * @var mixed[]
     */
    private $attributes    = array();

    /**
     * Adds a new action to the loop stack
     *
     * @param callable $action
     *
     * @return void
     *
     * @api
     */
    public function addAction(Closure $action)
    {
        $this->closureStack[] = $action;
    }

    /**
     * Sets a list of actions for the loop
     *
     * @param callable[] $closures
     *
     * @return void
     *
     * @api
     */
    public function setActions(array $closures)
    {
        foreach ($closures as $closure) {
            $this->addAction($closure);
        }
    }

    /**
     * Adds one item to the filter list
     *
     * @param mixed $item
     *
     * @return void
     *
     * @api
     */
    public function addItemToFilter($item)
    {
        $this->itemsToFilter[] = $item;
    }

    /**
     * Sets a list of items
     *
     * @param mixed[] $items
     *
     * @return void
     *
     * @api
     */
    public function setItems(array $items)
    {
        $this->itemsToFilter = array_merge(
            $items, $this->itemsToFilter
        );
    }

    /**
     * Sets a list of attributes for the filter closures
     *
     * @param string[] $attributes
     *
     * @return void
     *
     * @api
     */
    public function setAttributeList(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Resets the configuration of the object
     *
     * @return void
     *
     * @api
     */
    public function reset()
    {
        $this->closureStack  = array();
        $this->itemsToFilter = array();
        $this->attributes    = array();
    }

    /**
     * Executes the filter and returns a list of extracted items
     *
     * @return mixed[]
     *
     * @api
     */
    public function run()
    {
        $dataToRemove = array();
        $objMap = array();
        foreach ($this->itemsToFilter as $item) {
            foreach ($this->closureStack as $closure) {
                if (true === $closure($item, $this->attributes)) {
                    $objId = uniqid(rand(), true);
                    $dataToRemove[] = $objId;
                    $objMap[$objId] = $item;
                }
            }
        }

        $uniqFilter = array_unique($dataToRemove);
        $endResult = array();
        foreach ($uniqFilter as $element) {
            $endResult[] = $objMap[$element];
        }

        return $endResult;
    }
} 