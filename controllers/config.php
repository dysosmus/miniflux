<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;
use PicoDb\Database;

// try to update miniflux
Router\get_action('auto-update', function() {
    /* Since copying files can be a long process, we do not want the script 
       shuts down unexpectedly due to a lack of time. */
    set_time_limit(0);

    $rollback_name = \Model\Update\freeze(); 

    if($rollback_name == null) {
        Session\flash_error(t('Unable to update miniflux, can not freeze the current file structure.'));
        Response\redirect('?action=config');
    }

    $url      = Model\Config\get('update_url');
    $zip_path = Model\Update\fetch($url); 

    if($zip_path == null) {
        Session\flash_error(t("Unable to update miniflux, can not fetch a zip file at {$url}."));
        Response\redirect('?action=config');
    }

    $update_name = Model\Update\uncompress($zip_path);

    if($update_name == null) {
        Session\flash_error(t('Unable to update miniflux, can not uncompress fetched zip file.'));
        Response\redirect('?action=config');
    }

    $success = Model\Update\update($update_name);

    if($success == false) {
        if(Model\Update\rollback($rollback_name)) {
            Session\flash_error(t("Unable to update miniflux, error during the writing, files are successfully rolled back."));
        } else {
            Session\flash_error(t("Unable to update miniflux, error during the writing, files are not rolled back."));
        }
        Response\redirect('?action=config');
    }

    Session\flash(t('Miniflux is updated.'));
    Response\redirect('?action=config');
});

// Re-generate tokens
Router\get_action('generate-tokens', function() {

    Model\Config\new_tokens();
    Response\redirect('?action=config#api');
});

// Optimize the database manually
Router\get_action('optimize-db', function() {

    Database::get('db')->getConnection()->exec('VACUUM');
    Response\redirect('?action=config');
});

// Download the compressed database
Router\get_action('download-db', function() {

    Response\force_download('db.sqlite.gz');
    Response\binary(gzencode(file_get_contents(DB_FILENAME)));
});

// Display preferences page
Router\get_action('config', function() {

    Response\html(Template\layout('config', array(
        'errors' => array(),
        'values' => Model\Config\get_all(),
        'db_size' => filesize(DB_FILENAME),
        'languages' => Model\Config\get_languages(),
        'timezones' => Model\Config\get_timezones(),
        'autoflush_options' => Model\Config\get_autoflush_options(),
        'paging_options' => Model\Config\get_paging_options(),
        'theme_options' => Model\Config\get_themes(),
        'sorting_options' => Model\Config\get_sorting_directions(),
        'redirect_nothing_to_read_options' => Model\Config\get_nothing_to_read_redirections(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});

// Update preferences
Router\post_action('config', function() {

    $values = Request\values() + array('nocontent' => 0);
    list($valid, $errors) = Model\Config\validate_modification($values);

    if ($valid) {

        if (Model\Config\save($values)) {
            Session\flash(t('Your preferences are updated.'));
        }
        else {
            Session\flash_error(t('Unable to update your preferences.'));
        }

        Response\redirect('?action=config');
    }

    Response\html(Template\layout('config', array(
        'errors' => $errors,
        'values' => Model\Config\get_all(),
        'db_size' => filesize(DB_FILENAME),
        'languages' => Model\Config\get_languages(),
        'timezones' => Model\Config\get_timezones(),
        'autoflush_options' => Model\Config\get_autoflush_options(),
        'paging_options' => Model\Config\get_paging_options(),
        'theme_options' => Model\Config\get_themes(),
        'sorting_options' => Model\Config\get_sorting_directions(),
        'redirect_nothing_to_read_options' => Model\Config\get_nothing_to_read_redirections(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});
