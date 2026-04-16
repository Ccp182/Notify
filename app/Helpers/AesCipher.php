<?php

/**
 * AesCipher
 *
*/
class AesCipher
{
    const SECRET_KEY = '26kHunterDEVJ24t';
    public static function encryptBase64($plaintext) {
        $data = AesCipher::encryptAES($plaintext, static::SECRET_KEY);
        $data = base64_encode($data);
        return $data; 
    }
    
    public static function encryptHexStr($plaintext) {
        $data = AesCipher::encryptAES($plaintext, static::SECRET_KEY);
        $data = bin2hex($data);
        return $data; 
    }
 
    public static function decryptBase64($ciphertext) {
        $ciphertext = base64_decode($ciphertext);
        $plaintext = AesCipher::decryptAES($ciphertext, static::SECRET_KEY);	
        return $plaintext; 
    }
    public static function decryptHexStr($ciphertext) {
        $ciphertext = hex2bin($ciphertext);
        $plaintext = AesCipher::decryptAES($ciphertext, static::SECRET_KEY);	
        return $plaintext; 
    }
	
    private static function encryptAES($plaintext) {
        return openssl_encrypt($plaintext, "aes-128-ecb", static::SECRET_KEY, OPENSSL_RAW_DATA); 
    }

    private static function decryptAES($ciphertext) {
        return openssl_decrypt($ciphertext, "aes-128-ecb", static::SECRET_KEY, OPENSSL_RAW_DATA); 
    }
}
