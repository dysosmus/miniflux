<?php

namespace PicoFeed;

abstract class Writer
{
    public $items = array();

    abstract public function execute($filename = '');

    public function checkRequiredProperties()
    {
        foreach ($this->required_properties as $property) {

            if (! isset($this->$property)) {

                throw new \RuntimeException('Required property missing: '.$property);
            }
        }
    }
}