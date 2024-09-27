<?php

namespace App\Service\IDP;

use GuzzleHttp\Exception\GuzzleException;
use LogicException;

final class MicrosoftEntraId extends IdentityProviderAbstract implements IdentityProviderInterface
{
    private string $providerUrl = 'https://login.microsoftonline.com/{tenant}';
    private string $version = 'v2.0';

    private function getBaseUrl(): string
    {
        if ($this->tenant) {
            return str_replace('{tenant}', $this->tenant, $this->providerUrl);
        }

        throw new LogicException('Microsoft Entra ID Provider "tenant" is not configured.');
    }

    /**
     * @throws LogicException
     */
    public function getProviderUrl(): string
    {
        return  "{$this->getBaseUrl()}/{$this->version}";
    }

    /**
     * @throws GuzzleException
     */
    public function getConfiguration(): array
    {
        $url = "{$this->getProviderUrl()}/.well-known/openid-configuration";
        $response = $this->httpClient->request('GET', $url);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getAccessTokenIssuer(): string
    {
        return sprintf('https://sts.windows.net/%s/', $this->tenant);
    }

    public function getAccessTokenAudience(): string
    {
        return '00000003-0000-0000-c000-000000000000';
    }
}