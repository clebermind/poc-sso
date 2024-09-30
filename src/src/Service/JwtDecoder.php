<?php

namespace App\Service;

use InvalidArgumentException;

class JwtDecoder
{
    private bool $isJwe = false;

    /**
     * @throws InvalidArgumentException
     */
    public function decode(string $token): object
    {
        $tokenParts = explode(".", $token);

        return match (count($tokenParts)) {
            3 => $this->decodeJws($tokenParts),
            5 => $this->decodeJwe($tokenParts),
            default => throw new InvalidArgumentException("Unsupported token format"),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    private function decodeJws(array $tokenParts): object
    {
        return (object)[
            'header' => $this->base64Decode($tokenParts[0]),
            'payload' => $this->base64Decode($tokenParts[1]),
            'signature' => $tokenParts[2]
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function decodeJwe(array $tokenParts): object
    {
        $this->isJwe = true;

        return (object)[
            'header' => $this->base64Decode($tokenParts[0]),
            'encryptedKey' => $tokenParts[1],
            'initializationVector ' => $tokenParts[2],
            'ciphertext' => $tokenParts[3],
            'authenticationTag' => $tokenParts[4],
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function base64Decode(string $part): mixed
    {
        $decodedValue = json_decode(base64_decode($part, true), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Failed to decode");
        }

        return $decodedValue;
    }

    public function isJwe(): bool
    {
        return $this->isJwe;
    }

    public function isJws(): bool
    {
        return !$this->isJwe;
    }
}
