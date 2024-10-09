<?php

namespace App\Service;

class Encryptor
{
    private const CIPHER = 'AES-256-CBC';
    private string $key;
    private int|false $ivLength;

    public function __construct()
    {
        $this->key = hash('sha256', ']iz?~_ud3~E_gh#Qx)Pypgb?7{l+K,');
        $this->ivLength = openssl_cipher_iv_length(self::CIPHER);
    }

    public function encrypt($data): string
    {
        $iv = openssl_random_pseudo_bytes($this->ivLength);
        $encryptedData = openssl_encrypt($data, self::CIPHER, $this->key, 0, $iv);

        return base64_encode($iv . $encryptedData);
    }
    public function decrypt($encryptedData): bool|string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, $this->ivLength);
        $encryptedData = substr($data, $this->ivLength);

        return openssl_decrypt($encryptedData, self::CIPHER, $this->key, 0, $iv);
    }
}
