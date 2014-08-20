<?php

namespace Ma27\Jira\IssueVoteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class merges and validates the input of the bundle configuration from app/config
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ma27_jira_issue_vote');

        $rootNode
            ->children()
                ->scalarNode('consumer_key')
                    ->isRequired()
                ->end()
                ->scalarNode('consumer_secret')
                    ->isRequired()
                ->end()
                ->scalarNode('oauth_host')
                    ->isRequired()
                ->end()
                ->scalarNode('client_host')
                    ->isRequired()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
