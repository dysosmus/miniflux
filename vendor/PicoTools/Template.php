<?php

/*
 * This file is part of PicoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools\Template;

const PATH = 'templates/';


// Template\load('template_name', ['bla' => 'value']);
function load()
{
    if (func_num_args() < 1 || func_num_args() > 2) {

        die('Invalid template arguments');
    }

    if (! file_exists(PATH.func_get_arg(0).'.php')) {

        die('Unable to load the template: "'.func_get_arg(0).'"');
    }

    if (func_num_args() === 2) {

        if (! is_array(func_get_arg(1))) {

            die('Template variables must be an array');
        }

        extract(func_get_arg(1));
    }

    ob_start();

    include PATH.func_get_arg(0).'.php';

    return ob_get_clean();
}


function layout($template_name, array $template_args = array())
{
    $output = load('app_header', $template_args);
    $output .= load($template_name, $template_args);
    $output .= load('app_footer', $template_args);

    return $output;
}