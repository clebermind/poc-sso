<?php

namespace App\Service\IDP;

use LogicException;

final class MicrosoftEntraId extends IdentityProviderAbstract
{
    private string $providerUrl = 'https://login.microsoftonline.com/{tenant}';
    private string $version = 'v2.0';

    /**
     * @throws LogicException
     */
    public function getProviderUrl(): string
    {
        return  "{$this->getBaseUrl()}/{$this->version}";
    }

    /**
     * @throws LogicException
     */
    private function getBaseUrl(): string
    {
        return str_replace('{tenant}', $this->getTenant(), $this->providerUrl);
    }

    private function getTenant(): ?string
    {
        $tenant = $this->extraFields['tenant'] ?? null;
        if (is_null($tenant)) {
            throw new LogicException('Microsoft Entra ID Provider "tenant" is not configured.');
        }

        return $tenant;
    }
}
