<?php

namespace Mayflower\JiraIssueVoteBundle\EventListener;

use GuzzleHttp\Exception\RequestException;
use Mayflower\JiraIssueVoteBundle\Controller\AuthorizeController;
use Mayflower\JiraIssueVoteBundle\Exception\InvalidTokenException;
use Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * OAuthCredentialsListener
 *
 * Listener which checks the credentials and if the access is no longer approved,
 * a safe login will be processed
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class OAuthCredentialsListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    /**
     * Listens on kernel exceptions
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception  = $event->getException();
        $isInternal = $exception instanceof InvalidTokenException;
        if (!$exception instanceof RequestException && !$isInternal) {
            return;
        }

        if (
            !$isInternal && in_array($exception->getResponse()->getStatusCode(), [401, 403])
            || !$event->getRequest()->getSession()->has(TokenFetcher::OAUTH_TOKEN)
            || $isInternal
        ) {
            $request = $event->getRequest()->duplicate();
            $request->attributes->set('_controller', 'MayflowerJiraIssueVoteBundle:Authorize:invalidateTokens');

            $request->attributes->add(
                [
                    'type'       => AuthorizeController::TOKEN_WARNING,
                    'error_text' => 'You were logged out automatically. ' .
                        'In order to continue using Jira Vote, please re-login.'
                ]
            );

            $event->setResponse($event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST));
        }
    }
}
