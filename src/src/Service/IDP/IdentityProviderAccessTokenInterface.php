<?php

namespace App\Service\IDP;

use GuzzleHttp\Exception\GuzzleException;
use LogicException;

interface IdentityProviderAccessTokenInterface
{
    public function getAccessTokenAudience(): string;
    public function getAccessTokenIssuer(): string;
}
