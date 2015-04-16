<?php

namespace Mayflower\JiraIssueVoteBundle\Service\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractIterator extends \FilterIterator
{
    /**
     * @var ParameterBag
     */
    protected $attributes;

    public function __construct()
    {
        $this->attributes = new ParameterBag();
    }

    /**
     * @return boolean
     */
    abstract public function isEnabled();

    /**
     * @param array $data
     * @return $this
     */
    public function fill(array $data)
    {
        parent::__construct(new \ArrayIterator($data));
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addAttribute($key, $value)
    {
        $this->attributes->set($key, $value);

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes->add($attributes);
        return $this;
    }
}
