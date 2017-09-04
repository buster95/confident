<?php

namespace Confident\Utilities;

class SecuritytHelper
{
    public static function Authorization()
    {
        if (!isset(apache_request_headers()['Authorization'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(array('message'=>'Token not sended'));
            exit;
            // $auth = "Token not Sended";
        } else {
            $auth = apache_request_headers()['Authorization'];
            if (is_null($auth) || $auth=='') {
                $auth = "Token Sended Null or Empty";
            }
        }
        return $auth;
    }

    public static function token($size = 0)
    {
        $tokens=crypt('tokens', rand().uniqid());
        if ($size>0) {
            for ($i=0; $i<$size; $i++) {
                $tokens.=crypt('tokens', rand().uniqid());
                if (strlen($tokens)>$size) {
                    $tokens=substr($tokens, 0, $size);
                    break;
                }
            }
        }
        $tokens = str_replace(array('$', '.', '/'), '', $tokens);
        return $tokens;
    }

    public static function cifrar($cadena, $llave)
    {
        $result = '';
        for ($i = 0; $i < strlen($cadena); $i++) {
            $char = substr($cadena, $i, 1);
            $keychar = substr($llave, ($i % strlen($llave)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result.=$char;
        }
        return base64_encode($result);
    }

    public static function descifrar($cadena, $llave)
    {
        $result = '';
        $cadena = base64_decode($cadena);
        for ($i = 0; $i < strlen($cadena); $i++) {
            $char = substr($cadena, $i, 1);
            $keychar = substr($llave, ($i % strlen($llave)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result.=$char;
        }
        return $result;
    }
    
    // $algorithm = MCRYPT_BLOWFISH;
    // $key = 'That golden key that opens the palace of eternity.';
    // $data = 'The chicken escapes at dawn. Send help with Mr. Blue.';
    // $mode = MCRYPT_MODE_CBC;
    // $iv = mcrypt_create_iv(mcrypt_get_iv_size($algorithm, $mode), MCRYPT_DEV_URANDOM);
    // $encrypted_data = mcrypt_encrypt($algorithm, $key, $data, $mode, $iv);
    // $plain_text = base64_encode($encrypted_data);
    // echo $plain_text . "\n";
    // $encrypted_data = base64_decode($plain_text);
    // $decoded = mcrypt_decrypt($algorithm, $key, $encrypted_data, $mode, $iv);
    // echo $decoded . "\n";

    /**
     * ENCRIPTADO DE TEXTO CON LLAVE
     * @param  String $cadena        TEXTO A ENCRIPTAR
     * @param  String $llave_cifrado LLAVE DE CIFRADO
     * @return String                TEXTO CIFRADO
     */
    public function encriptar($cadena, $llave_cifrado)
    {
        $encrypted = mcrypt_ecb(MCRYPT_DES, $llave_cifrado, $cadena, MCRYPT_ENCRYPT);
        return $encrypted;
    }

    /**
     * DESENCRIPTADO DE DATOS CON LLAVE
     * @param  String $cadena           TEXTO A DESCIFRAR
     * @param  String $llave_descifrado LLAVE DE DESENCRIPTADO
     * @return String                   TEXTO DESENCRIPTADO
     */
    public function desencriptar($cadena, $llave_descifrado)
    {
        $decrypted = mcrypt_ecb(MCRYPT_DES, $llave_descifrado, $cadena, MCRYPT_DECRYPT);
        return $decrypted;
    }
}
