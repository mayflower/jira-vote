<?php
namespace Mayflower\JiraIssueVoteBundle\Model;

/**
 * Objects which represents an issue filter or jira project.
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class IssueSource
{
    /**
     * @var string|integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $viewUrl;

    /**
     * Builds a filter model by the raw data of the jira api
     *
     * @param array $params
     *
     * @return IssueSource
     */
    public static function fill(array $params)
    {
        $model = new self;

        $model->setId($params['id']);
        $model->setName($params['name']);
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
        $this->id = $id;
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
            'id'   => $this->getId(),
            'name' => $this->getName(),
            'url'  => $this->getViewUrl()
        );
    }
}
