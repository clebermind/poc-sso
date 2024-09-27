<?php

namespace App\Service;

use App\Factory\OpenIDConnectFactory;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Exception;

class TokenValidator
{
    private OpenIDConnect $openIDConnect;
    private object $decodedToken;
    private array $configuration;
    private array $publicKeys;

    public function __construct(
        private readonly JwtDecoder $jwtDecoder,
        OpenIDConnectFactory $openIDConnectFactory)
    {
        $this->openIDConnect = $openIDConnectFactory->create();
    }

    /**
     * @throws AuthenticationException
     */
    public function validateIdToken(string $idToken): void
    {
        $this->validate($idToken);

        if (!$this->isValidIssuer($this->configuration['issuer'])) {
            throw new AuthenticationException('Invalid issuer.');
        }

        if (!$this->isValidAudience($this->openIDConnect->getClientId())) {
            throw new AuthenticationException('Invalid audience.');
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function validateAccessToken(string $accessToken): void
    {
        $this->validate($accessToken);

        if (!$this->isValidIssuer($this->openIDConnect->getAccessTokenIssuer())) {
            throw new AuthenticationException('Invalid issuer.');
        }

        if (!$this->isValidAudience($this->openIDConnect->getAccessTokenAudience())) {
            throw new AuthenticationException('Invalid audience.');
        }
    }

    /**
     * @throws AuthenticationException
     */
    private function validate(string $token): void
    {
        $this->decodedToken = $this->jwtDecoder->decode($token);

        if ($this->isExpired()) {
            throw new AuthenticationException('Token has expired.');
        }

        try {
            $this->configuration = $this->openIDConnect->getIdentityProviderConfiguration();
        } catch (GuzzleException|Exception $exception) {
            error_log($exception->getMessage());
            throw new AuthenticationException('Not possible to get the identity provider configuration');
        }
    }

    private function isValidIssuer(string $expectedIssuer): bool
    {
        return $this->decodedToken->payload['iss'] === $expectedIssuer;
    }

    private function isValidAudience(string $expectedAudience): bool
    {
        if (isset($this->decodedToken->payload['aud'])) {
            if (is_array( $this->decodedToken->payload['aud'])) {
                return in_array($expectedAudience, $this->decodedToken->payload['aud']);
            } else {
                return $this->decodedToken->payload['aud'] === $expectedAudience;
            }
        }

        return false;
    }

    private function isExpired(): bool
    {
        $currentTime = time();
        if (isset($decodedToken->exp) && $decodedToken->exp < $currentTime) {
            return true;
        }

        return false;
    }
}
