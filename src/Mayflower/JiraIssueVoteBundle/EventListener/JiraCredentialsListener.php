<?php
namespace Mayflower\JiraIssueVoteBundle\EventListener;

use Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityToken as OAuthSecurityProxy;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event-subscriber which checks the credentials of the oauth consumer and
 * configures the provider and oauth factory by injecting the consumer tokens
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class JiraCredentialsListener implements EventSubscriberInterface, ContainerAwareInterface
{
    /**
     * Key of the flag which checks the login state
     *
     * @var string
     *
     * @api
     */
    const OAUTH_LOGIN_FLAG = 'oauth.login.internal.flag';

    /**
     * OAuth security proxy
     * @var OAuthSecurityProxy
     */
    private $proxy;

    /**
     * Symfony service container
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the security proxy
     *
     * @param OAuthSecurityProxy $proxy Security proxy
     *
     * @api
     */
    public function __construct(OAuthSecurityProxy $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * Sets the service container of symfony
     *
     * @param ContainerInterface $container Service container
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns a list with subscribed events
     *
     * @return mixed[]
     *
     * @static
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onHttpRequest')
        );
    }

    /**
     * Checks the user credentials and configures the oauth rest provider.<br />
     * <b>This event will be triggered at the kernel.request event</b>
     *
     * @param GetResponseEvent $event
     *
     * @api
     */
    public function onHttpRequest(GetResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();

        /** @var \Mayflower\JiraIssueVoteBundle\Util\OAuthPluginFactory $factory */
        $factory = $this->container->get('ma27_jira_issue_vote.oauth.factory');
        /** @var \Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityAccessProvider $provider */
        $provider = $this->container->get('ma27_jira_issue_vote.oauth.provider');

        if ($this->proxy->hasToken() && $session->has(static::OAUTH_LOGIN_FLAG)) {
            /** @var \Mayflower\JiraIssueVoteBundle\Entity\OAuthToken $tokens */
            $tokens = $session->get(OAuthSecurityProxy::TEMP_TOKEN_ID);

            $factory->setTokens($tokens);
            $this->container->set('ma27_jira_issue_vote.oauth.factory', $factory);

            $provider->setPluginFactory($factory);
        } else {
            $provider->setPluginFactory($factory);
        }
        $this->container->set('ma27_jira_issue_vote.oauth.provider', $provider);
    }
} 
