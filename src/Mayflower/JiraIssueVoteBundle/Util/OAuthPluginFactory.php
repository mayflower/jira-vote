<?php
namespace Mayflower\JiraIssueVoteBundle\Util;

use Guzzle\Plugin\Oauth\OauthPlugin;
use Mayflower\JiraIssueVoteBundle\Entity\OAuthToken;

/**
 * Manager which handles and manages the oauth plugin
 * and its configuration
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 *
 * @deprecated
 */
class OAuthPluginFactory
{
    /**
     * List containing the configuration
     * @var mixed[]
     */
    private $pluginConfig;

    /**
     * Sets the default configuration by consumer key and consumer secret path
     *
     * @param string $consumerKey        oAuth consumer key
     * @param string $consumerSecretPath Path to the oauth consumer secret path
     * @param string $token              (optional) Token of the current user
     * @param string $tokenSecret        (optional) Token secret of the current user
     *
     * @api
     */
    public function __construct($consumerKey, $consumerSecretPath, $token = null, $tokenSecret = null)
    {
        $this->resetConfiguration($consumerKey, $consumerSecretPath);

        if (!in_array(null, array($token, $tokenSecret))) {
            $this->appendConfiguration(array('token' => $token, 'token_secret' => $tokenSecret));
        }
    }

    /**
     * Shortcut to change the tokens
     *
     * @param OAuthToken $token Token wrapper
     *
     * @return void
     *
     * @api
     */
    public function setTokens(OAuthToken $token)
    {
        $this->appendConfiguration(
            array(
                'token' => $token->getToken(),
                'token_secret' => $token->getTokenSecret()
            )
        );
    }

    /**
     * Resets the configuration of the plugin
     *
     * @param string $consumerKey        Consumer key
     * @param string $consumerSecretPath Path of the consumer secret
     *
     * @return void
     *
     * @api
     */
    public function resetConfiguration($consumerKey, $consumerSecretPath)
    {
        $this->pluginConfig = array(
            'consumer_key'       => $consumerKey,
            'consumer_secret'    => $this->getConsumerSecret($consumerSecretPath),
            'token'              => null,
            'token_secret'       => null,
            'signature_method'   => 'RSA-SHA1',
            'signature_callback' => function ($stringToSign, $key) use ($consumerSecretPath) {
                    $privateKey = openssl_pkey_get_private('file://' . $consumerSecretPath);
                    $keyId      = openssl_get_privatekey($privateKey);

                    $signature = null;

                    openssl_sign($stringToSign, $signature, $keyId);
                    openssl_free_key($keyId);

                    return $signature;
                }
        );
    }

    /**
     * Returns the current configuration stack
     *
     * @return mixed[]
     *
     * @api
     */
    public function getPluginConfig()
    {
        return $this->pluginConfig;
    }

    /**
     * Merges the current configuration with a new stack
     *
     * @param string[] $config New config stack
     *
     * @return void
     *
     * @api
     */
    public function appendConfiguration(array $config)
    {
        $this->pluginConfig = array_merge($this->pluginConfig, $config);
    }

    /**
     * Creates a new plugin by the current configuration
     *
     * @return OauthPlugin
     *
     * @api
     */
    public function getPlugin()
    {
        return new OauthPlugin($this->pluginConfig);
    }

    /**
     * Loads the consumer secret by its path (if the path doesn't exist, the consumer secret will be used raw)
     *
     * @param string  $path  Path or raw string of the consumer key
     * @param boolean $throw If this param is true, an exception will be thrown if the path of the consumer secret not exist
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the param $throw is true and the secret path does not exist
     *
     * @api
     */
    private function getConsumerSecret($path, $throw = false)
    {
        if (!file_exists($path)) {
            if ($throw) {
                throw new \InvalidArgumentException(sprintf('Path %s not exist!', $path));
            }
            return $path;
        }

        return file_get_contents($path);
    }
}
