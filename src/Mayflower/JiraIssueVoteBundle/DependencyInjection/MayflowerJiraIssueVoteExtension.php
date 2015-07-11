<?php

namespace Mayflower\JiraIssueVoteBundle\DependencyInjection;

use Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityToken;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class MayflowerJiraIssueVoteExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('host', $config['oauth_host']);
        $container->setParameter('consumer_key', $config['consumer_key']);
        $container->setParameter('consumer_secret', $config['consumer_secret']);
        $container->setParameter('client', $config['client_host']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // load the refactored components
        $loader->load('oauth_refactor.yml');
        $loader->load('model_refactor.yml');
    }
}
