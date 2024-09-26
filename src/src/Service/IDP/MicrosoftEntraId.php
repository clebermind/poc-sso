<?php

namespace App\Service\IDP;

use LogicException;

final class MicrosoftEntraId extends IdentityProviderAbstract implements IdentityProviderInterface
{
    private string $providerUrl = 'https://login.microsoftonline.com/{tenant}/v2.0';

    /**
     * @throws LogicException
     */
    public function getProviderUrl(): string
    {
        if ($this->tenant) {
            return str_replace('{tenant}', $this->tenant, $this->providerUrl);
        }

        throw new LogicException('Microsoft Entra ID Provider "tenant" is not configured.');
    }
}