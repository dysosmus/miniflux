<?php

namespace Helper;


function css()
{
    $theme = \Model\get_config_value('theme');

    if ($theme !== 'original') {

        $css_file = THEME_DIRECTORY.'/'.$theme.'/css/app.css';

        if (file_exists($css_file)) {
            return $css_file.'?version='.filemtime($css_file);
        }
    }

    return 'assets/css/app.css?version='.filemtime('assets/css/app.css');
}