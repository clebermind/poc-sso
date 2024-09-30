<?php

namespace App\Service\IDP;

use LogicException;

final class Auth0 extends IdentityProviderAbstract
{
    /**
     * @throws LogicException
     */
    public function getProviderUrl(): string
    {
        return  $this->getDomain();
    }

    private function getDomain(): ?string
    {
        $domain = $this->extraFields['domain'] ?? null;
        if (is_null($domain)) {
            throw new LogicException('Auth0 "domain" is not configured.');
        }

        return $domain;
    }
}
