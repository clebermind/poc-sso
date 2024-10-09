<?php

namespace App\EventListener;

use App\Factory\OpenIDConnectFactory;
use App\Service\TokenManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use \Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
 * If the last time an access token was required is more than EXPIRATION_TIME
 * we try to require a new one using the refresh token.
 * If it is not possible to get a new access token, it means the refresh token is invalid
 * so we log the user out.
*/
class TokenExpirationListener implements EventSubscriberInterface
{
    private const EXPIRATION_TIME = 900; // seconds
    public function __construct(
        private readonly OpenIDConnectFactory $openIDConnectFactory,
        private readonly TokenManager $tokenManager,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $session = $this->requestStack->getSession();
        try {
            $openIDConnect = $this->openIDConnectFactory->create();
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return;
        }

        if (!($this->security->getToken() instanceof PreAuthenticatedToken)) {
            return;
        }

        $lastTimeAccessTokenWasGenerated = (int)$session->get('last_time_ac_was_generated', 0);
        if ($lastTimeAccessTokenWasGenerated === 0) {
            $session->set('last_time_ac_was_generated', time());
        } elseif (time() - self::EXPIRATION_TIME < $lastTimeAccessTokenWasGenerated) {
            return;
        }

        $this->tokenManager->setSession($session);

        $isAnewAccessTokenGenerated = $openIDConnect->refreshToken(
            $this->tokenManager->getRefreshTokenToken()
        );

        if ($isAnewAccessTokenGenerated) {
            $this->tokenManager->storeTokens(
                $openIDConnect->getAccessToken(),
                $openIDConnect->getRefreshToken(),
            );
            $session->set('last_time_ac_was_generated', time());
        } else {
            $session->clear();
            $event->setResponse(new Response('Unauthorized', Response::HTTP_UNAUTHORIZED));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
