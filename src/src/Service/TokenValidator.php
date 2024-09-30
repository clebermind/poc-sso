<?php

namespace App\Service;

use App\Factory\OpenIDConnectFactory;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Exception;

class TokenValidator
{
    private OpenIDConnect $openIdConnect;
    private object $decodedToken;
    private array $identityProviderConfiguration;

    public function __construct(
        private readonly JwtDecoder $jwtDecoder,
        OpenIDConnectFactory $openIdConnectFactory)
    {
        $this->openIdConnect = $openIdConnectFactory->create();
    }

    /**
     * @throws AuthenticationException
     */
    public function validate(string $token): void
    {
        $this->decodeToken($token);
        $this->loadIdentityProviderConfiguration();

        if ($this->isExpired()) {
            throw new AuthenticationException('Token has expired.');
        }

        if (!$this->isValidIssuer()) {
            throw new AuthenticationException('Invalid issuer.');
        }

        if (!$this->isValidAudience()) {
            throw new AuthenticationException('Invalid audience.');
        }
    }

    private function decodeToken(string $token): void
    {
        $this->decodedToken = $this->jwtDecoder->decode($token);
    }

    /**
     * @throws AuthenticationException
     */
    private function loadIdentityProviderConfiguration(): void
    {
        try {
            $this->identityProviderConfiguration = $this->openIdConnect->getIdentityProviderConfiguration();
        } catch (GuzzleException|Exception $exception) {
            error_log($exception->getMessage());
            throw new AuthenticationException('Not possible to get the identity provider configuration');
        }
    }

    private function isValidIssuer(): bool
    {
        if (isset($this->identityProviderConfiguration['issuer'], $this->decodedToken->payload['iss'])) {
            return $this->decodedToken->payload['iss'] === $this->identityProviderConfiguration['issuer'];
        }

        return false;
    }

    private function isValidAudience(): bool
    {
        if (isset($this->decodedToken->payload['aud'], $this->decodedToken->payload['aud'])) {
            if (is_array( $this->decodedToken->payload['aud'])) {
                return in_array($this->openIdConnect->getClientId(), $this->decodedToken->payload['aud']);
            } else {
                return $this->decodedToken->payload['aud'] === $this->openIdConnect->getClientId();
            }
        }

        return false;
    }

    private function isExpired(): bool
    {
        if (isset($decodedToken->exp) && $decodedToken->exp < time()) {
            return true;
        }

        return false;
    }
}
