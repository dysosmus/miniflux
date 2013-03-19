<?php

/*
 * This file is part of PicoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools\Crypto;


/**
 * Generate a random token
 *
 * @return string Random token
 */
function token()
{
    return hash('sha256', uniqid('', true).microtime());
}


/**
 * Generate a signature with a key
 *
 * @param string $data Data
 * @param string $key Key
 * @return string Signature
 */
function signature($data, $key)
{
    return hash_hmac('sha256', $data, $key);
}


// Import of the PHP 5.5 password hashing method
// https://github.com/ircmaxell/password_compat/blob/master/lib/password.php

function password_verify($password, $hash)
{
    $ret = crypt($password, $hash);

    if (! is_string($ret) || strlen($ret) != strlen($hash) || strlen($ret) < 13) {

        return false;
    }

    $status = 0;

    for ($i = 0; $i < strlen($ret); $i++) {

        $status |= (ord($ret[$i]) ^ ord($hash[$i]));
    }

    return $status === 0;
}


function password_hash($password)
{
    $cost = 10;
    $required_salt_len = 22;
    $hash_format = sprintf("$2y$%02d$", $cost);

    $hash = $hash_format.password_salt($required_salt_len);

    $ret = crypt($password, $hash);

    if (! is_string($ret) || strlen($ret) < 13) {

        return false;
    }

    return $ret;
}


function password_salt($required_salt_len)
{
    $buffer = '';
    $raw_length = (int) ($required_salt_len * 3 / 4 + 1);
    $buffer_valid = false;

    if (function_exists('mcrypt_create_iv')) {

        $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);

        if ($buffer) {

            $buffer_valid = true;
        }
    }

    if (! $buffer_valid && function_exists('openssl_random_pseudo_bytes')) {

        $buffer = openssl_random_pseudo_bytes($raw_length);

        if ($buffer) {

            $buffer_valid = true;
        }
    }

    if (! $buffer_valid && file_exists('/dev/urandom')) {

        $f = @fopen('/dev/urandom', 'r');

        if ($f) {

            $read = strlen($buffer);

            while ($read < $raw_length) {

                $buffer .= fread($f, $raw_length - $read);
                $read = strlen($buffer);
            }

            fclose($f);

            if ($read >= $raw_length) {

                $buffer_valid = true;
            }
        }
    }

    if (! $buffer_valid || strlen($buffer) < $raw_length) {

        $bl = strlen($buffer);

        for ($i = 0; $i < $raw_length; $i++) {

            if ($i < $bl) {

                $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
            }
            else {

                $buffer .= chr(mt_rand(0, 255));
            }
        }
    }

    $salt = str_replace('+', '.', base64_encode($buffer));

    return substr($salt, 0, $required_salt_len);
}
