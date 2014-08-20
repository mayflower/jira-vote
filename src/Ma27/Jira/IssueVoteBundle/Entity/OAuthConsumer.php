<?php
namespace Ma27\Jira\IssueVoteBundle\Entity;

/**
 * Value object which contains the name of the oauth consumer
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class OAuthConsumer
{
    /**
     * Name of the oauth consumer
     * @var string
     */
    private $name;

    /**
     * Sets the name of the consumer
     *
     * @param string $name Name to set
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
     * Returns the name of the oauth consumer
     *
     * @return string
     *
     * @api
     */
    public function getName()
    {
        return $this->name;
    }
}