<?php
/*
 * This file is part of Library Framework.
 *
 * (c) Thorpe Lee(Gwangbok Lee) <koangbok@gmail.com>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

namespace CafeLatte\Libraries;

use CafeLatte\Exception\InvalidLogicException;

/**
 * @author Thorpe Lee <koangbok@gmail.com>
 */
class EnDecrypt
{


    /**
     * @var string
     */
    CONST CIPHER_AES_128_CBC = "aes-128-cbc";
    CONST CIPHER_AES_256_CBC = "aes-256-cbc";

    /**
     * @var null
     */
    private static $instance = NULL;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $iv;

    /**
     * @var string
     */
    private $cipher;


    /**
     * @param string $key
     * @param string $iv
     * @param string $cipher
     * @return EnDecrypt|null
     */
    public static function create(string $key, string $iv, $cipher = 'aes-256-cbc')
    {

        $ciphers = openssl_get_cipher_methods();
       
        if (!in_array($cipher, $ciphers)) {
            throw new InvalidLogicException("NO Support Cipher `{$cipher}`", 400);
        }


        if (self::$instance == NULL) {
            self::$instance = new EnDecrypt($key, $iv, $cipher);
        }
        return self::$instance;
    }


    /**
     * EnDecrypt constructor.
     * @param string $key
     * @param string $iv
     * @param string $cipher
     */
    public function __construct(string $key, string $iv, $cipher)
    {
        $this->key = $key;
        $this->iv = $iv;
        $this->cipher = $cipher;
    }

    /**
     * encrypt string value
     *
     * @param string $data 암호화 입력값
     * @param bool $isRandomValue 랜덤값으로 출력할지 여부 (iv값을 고정 또는 새롭게 생성하느냐)
     * @return string
     */
    public function encrypt(string $data, bool $isRandomValue = true)
    {
        $encryption_key = base64_decode($this->key);
        if ($isRandomValue == true) {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        } else {
            $iv = $this->iv;
        }


        $encrypted = openssl_encrypt($data, $this->cipher, $encryption_key, 0, $iv);
        return urlencode(base64_encode($encrypted . '::' . $iv));
    }


    /**
     * decrypt the encrypted string value
     *
     * @param $data
     * @return string
     */
    public function decrypt(string $data)
    {
        $encryption_key = base64_decode($this->key);
        list($encrypted_data, $iv) = explode('::', base64_decode(urldecode($data)), 2);
        return openssl_decrypt($encrypted_data, $this->cipher, $encryption_key, 0, $iv);
    }

}
