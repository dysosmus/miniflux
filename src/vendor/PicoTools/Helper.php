<?php

namespace Helper;


function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
}


/**
 * Get the flash message if there is something
 *
 * @param string $html HTML tags of the flash message parsed with sprintf
 * return string HTML tags with the message or empty string if nothing
 */
function flash($html)
{
    $data = '';

    if (isset($_SESSION['flash_message'])) {

        $data = sprintf($html, escape($_SESSION['flash_message']));
        unset($_SESSION['flash_message']);
    }

    return $data;
}


/**
 * Get the flash error message if there is something
 *
 * @param string $html HTML tags of the flash message parsed with sprintf
 * return string HTML tags with the message or empty string if nothing
 */
function flash_error($html)
{
    $data = '';

    if (isset($_SESSION['flash_error_message'])) {

        $data = sprintf($html, escape($_SESSION['flash_error_message']));
        unset($_SESSION['flash_error_message']);
    }

    return $data;
}


function in_list($id, array $listing)
{
    if (isset($listing[$id])) {

        return escape($listing[$id]);
    }

    return '?';
}


function error_class(array $errors, $name)
{
    return ! isset($errors[$name]) ? '' : ' form-error';
}


function error_list(array $errors, $name)
{
    $html = '';

    if (isset($errors[$name])) {

        $html .= '<ul class="form-errors">';

        foreach ($errors[$name] as $error) {

            $html .= '<li>'.escape($error).'</li>';
        }

        $html .= '</ul>';
    }

    return $html;
}


function form_value($values, $name)
{
    if (isset($values->$name)) {

        return 'value="'.escape($values->$name).'"';
    }

    return isset($values[$name]) ? 'value="'.escape($values[$name]).'"' : '';
}


function form_hidden($name, $values = array())
{
    return '<input type="hidden" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).'/>';
}


function form_default_select($name, array $options, $values = array(), array $errors = array(), $class = '')
{
    $options = array('' => '?') + $options;

    return form_select($name, $options, $values, $errors, $class);
}


function form_select($name, array $options, $values = array(), array $errors = array(), $class = '')
{
    $html = '<select name="'.$name.'" id="form-'.$name.'" class="'.$class.'">';

    foreach ($options as $id => $value) {

        $html .= '<option value="'.escape($id).'"';

        if (isset($values->$name) && $id == $values->$name) $html .= ' selected="selected"';
        if (isset($values[$name]) && $id == $values[$name]) $html .= ' selected="selected"';

        $html .= '>'.escape($value).'</option>';
    }

    $html .= '</select>';
    $html .= error_list($errors, $name);

    return $html;
}


function form_label($label, $name, $class = '')
{
    return '<label for="form-'.$name.'" class="'.$class.'">'.escape($label).'</label>';
}


function form_text($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<input type="text" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).' class="'.$class.'" ';
    $html .= implode(' ', $attributes).'/>';
    $html .= error_list($errors, $name);

    return $html;
}


function form_password($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<input type="password" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).' class="'.$class.'" ';
    $html .= implode(' ', $attributes).'/>';
    $html .= error_list($errors, $name);

    return $html;
}


function form_textarea($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<textarea name="'.$name.'" id="form-'.$name.'" class="'.$class.'" ';
    $html .= implode(' ', $attributes).'>';
    $html .= isset($values->$name) ? escape($values->$name) : isset($values[$name]) ? $values[$name] : '';
    $html .= '</textarea>';
    $html .= error_list($errors, $name);

    return $html;
}