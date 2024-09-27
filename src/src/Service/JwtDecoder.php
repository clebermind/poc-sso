<?php

namespace App\Service;

use InvalidArgumentException;

class JwtDecoder
{
    /**
     * @throws InvalidArgumentException
     */
    public function decode(string $jwt): object
    {
        $tokenParts = explode(".", $jwt);

        if (count($tokenParts) !== 3) {
            throw new InvalidArgumentException('Invalid JWT token');
        }

        $header = json_decode(base64_decode($tokenParts[0]), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to decode JWT payload');
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to decode JWT payload');
        }

        return (object)[
            'header' => $header,
            'payload' => $payload,
        ];
    }

    public function getPayload(string $jwt): object
    {
        $decodedJwt = $this->decode($jwt);

        return (object)$decodedJwt->payload;
    }
}
