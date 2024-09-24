<?php

namespace App\Service;

use InvalidArgumentException;

class JwtDecoder
{
    /**
     * @throws InvalidArgumentException
     */
    public function decode(string $jwt): array
    {
        $tokenParts = explode(".", $jwt);

        if (count($tokenParts) !== 3) {
            throw new InvalidArgumentException('Invalid JWT token');
        }

        $payloadBase64 = $tokenParts[1];
        $payload = json_decode(base64_decode($payloadBase64), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to decode JWT payload');
        }

        $tokenParts[1] = $payload;

        return $tokenParts;
    }

    public function getPayload(string $jwt): object
    {
        $decodedJwt = $this->decode($jwt);

        return (object)$decodedJwt[1];
    }
}
