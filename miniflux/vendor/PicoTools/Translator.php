<?php

/*
 * This file is part of PicoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools\Translator {

    const PATH = 'locales/';


    function translate($identifier)
    {
        $args = \func_get_args();

        \array_shift($args);
        \array_unshift($args, get($identifier));

        return \call_user_func_array(
            'sprintf',
            $args
        );
    }


    function number($number)
    {
        return number_format(
            $number,
            get('number.decimals', 2),
            get('number.decimals_separator', '.'),
            get('number.thousands_separator', ',')
        );
    }


    function currency($amount)
    {
        $position = get('currency.position', 'before');
        $symbol = get('currency.symbol', '$');
        $str = '';

        if ($position === 'before') {

            $str .= $symbol;
        }

        $str .= number($amount);

        if ($position === 'after') {

            $str .= ' '.$symbol;
        }

        return $str;
    }


    function get($identifier, $default = '')
    {
        $locales = container();

        if (isset($locales[$identifier])) {

            return $locales[$identifier];
        }
        else {

            return $default;
        }
    }


    function load($language)
    {
        $path = PATH.$language;
        $locales = array();

        if (is_dir($path)) {

            $dir = new \DirectoryIterator($path);

            foreach ($dir as $fileinfo) {

                if (strpos($fileinfo->getFilename(), '.php') !== false) {

                    $locales = array_merge($locales, include $fileinfo->getPathname());
                }
            }
        }

        container($locales);
    }


    function container($locales = null)
    {
        static $values = array();

        if ($locales) {

            $values = $locales;
        }

        return $values;
    }
}


namespace {

    function t() {

        return call_user_func_array('\PicoTools\Translator\translate', func_get_args());
    }
}