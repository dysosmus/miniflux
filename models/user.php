<?php

namespace Model\User;

require_once __DIR__.'/../vendor/SimpleValidator/Validator.php';
require_once __DIR__.'/../vendor/SimpleValidator/Base.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/Required.php';
require_once __DIR__.'/../vendor/SimpleValidator/Validators/MaxLength.php';

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoDb\Database;

// Get a user by username
function get($username)
{
    return Database::get('db')
        ->table('config')
        ->columns('username', 'password', 'language')
        ->eq('username', $username)
        ->findOne();
}

// Validate authentication
function validate_login(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('username', t('The user name is required')),
        new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
        new Validators\Required('password', t('The password is required'))
    ));

    $result = $v->execute();
    $errors = $v->getErrors();

    if ($result) {

        $user = get($values['username']);

        if ($user && \password_verify($values['password'], $user['password'])) {

            unset($user['password']);

            $_SESSION['user'] = $user;
            $_SESSION['config'] = \Model\Config\get_all();
        }
        else {

            $result = false;
            $errors['login'] = t('Bad username or password');
        }
    }

    return array(
        $result,
        $errors
    );
}
