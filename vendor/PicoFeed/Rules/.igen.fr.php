<?php
return array(
    'test_url' => 'http://www.igen.fr/iphone/ios-7-cree-des-milliers-de-requetes-fantomes-sur-le-web-110130',
    'body' => array(
        '//div[contains(@id, "news")]',
    ),
    'strip' => array(
        '//*[contains(@class, "submitted")]',
        '//*[contains(@class, "clear-block")]',
    ),
);