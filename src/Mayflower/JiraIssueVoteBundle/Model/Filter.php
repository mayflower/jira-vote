<?php
namespace Mayflower\JiraIssueVoteBundle\Model;

/**
 * Objects which represents an issue filter
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class Filter
{
    /**
     * Filter id
     * @var string|integer
     */
    private $id;

    /**
     * Filter name
     * @var string
     */
    private $name;

    /**
     * Name of the filter owner
     * @var string
     */
    private $ownerName;

    /**
     * Url of the filter
     * @var string
     */
    private $viewUrl;

    /**
     * Builds a filter model by the raw data of the jira api
     *
     * @param array $params
     *
     * @return Filter
     */
    public static function fill(array $params)
    {
        $model = new self;

        $model->setId($params['id']);
        $model->setName($params['name']);
        $model->setOwnerName($params['owner']['name']);
        $model->setViewUrl($params['viewUrl']);

        return $model;
    }

    /**
     * Sets the filter id
     *
     * @param string|integer $id Id of the filter
     *
     * @return void
     *
     * @api
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * Returns the current filter id
     *
     * @return integer
     *
     * @api
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name of the filter
     *
     * @param string $name Name of the filter
     *
     * @return void
     *
     * @api
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * Returns the name of the filter
     *
     * @return string
     *
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the filter-owner
     *
     * @param string $ownerName Name of the owner
     *
     * @api
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = (string)$ownerName;
    }

    /**
     * Returns the name of the owner
     *
     * @return string
     *
     * @api
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * Sets the view url of the filter
     *
     * @param string $viewUrl View url of the filter
     *
     * @return void
     *
     * @api
     */
    public function setViewUrl($viewUrl)
    {
        $this->viewUrl = (string)$viewUrl;
    }

    /**
     * Returns the view url
     *
     * @return string
     *
     * @api
     */
    public function getViewUrl()
    {
        return $this->viewUrl;
    }

    /**
     * Converts the properties to an array
     *
     * @return string[]
     *
     * @api
     */
    public function toArray()
    {
        return array(
            'id'    => $this->getId(),
            'owner' => $this->getOwnerName(),
            'name'  => $this->getName(),
            'url'   => $this->getViewUrl()
        );
    }
} 