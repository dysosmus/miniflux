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
