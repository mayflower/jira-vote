<?php
namespace Mayflower\JiraIssueVoteBundle\Service\Filter;

class FilterContainer
{
    /**
     * @var \SplObjectStorage
     */
    private $storage;

    /**
     * @param \FilterIterator[] $filters
     */
    public function __construct(array $filters = array())
    {
        $this->storage = new \SplObjectStorage();
        $this->add($filters);
    }

    /**
     * @param \FilterIterator $filter
     * @return $this
     */
    public function set(\FilterIterator $filter)
    {
        $this->storage->attach($filter);
        return $this;
    }

    /**
     * @param \FilterIterator[] $filters
     * @return $this
     * @throws \LogicException
     */
    public function add(array $filters)
    {
        foreach ($filters as $element) {
            if (!$element instanceof \FilterIterator) {
                throw new \LogicException(
                    sprintf('Object %s is not an instance of FilterIterator!', get_class($element))
                );
            }

            $this->storage->attach($element);
        }

        return $this;
    }

    /**
     * @param mixed[] $data
     * @param mixed[] $attributes
     * @return mixed[]
     */
    public function process(array $data, array $attributes)
    {
        /** @var AbstractIterator $filter */
        foreach (iterator_to_array($this->storage) as $filter) {
            $filter->fill($data);
            $filter->setAttributes($attributes);

            if (!$filter->isEnabled()) {
                continue;
            }

            $list = [];
            foreach ($filter as $filtered)  {
                $list[] = $filtered;
            }

            $data = $list;
        }

        return $data;
    }
}
