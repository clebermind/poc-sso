<?php

namespace App\Factory;

use App\Service\OpenIDConnect;

final class OpenIDConnectFactory
{
    public function create(): OpenIDConnect
    {
        $providerUrl = 'https://login.microsoftonline.com/{---tenant---}/v2.0';
        $clientId = '{---client-id---}';
        $clientSec = '{---client---}';
        $redirectUri = 'http://localhost:8080/login/sso/callback';

        $openIDConnect = new OpenIDConnect($providerUrl, $clientId, $clientSec, $redirectUri);

        return $openIDConnect;
    }
}
