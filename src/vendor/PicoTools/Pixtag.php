<?php

/*
 * This file is part of picoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools;


/**
 * A wrapper around exiv2 command line utility
 * You can write and read IPTC, XMP and EXIF metadata inside a picture
 *
 * @author Frédéric Guillot
 */
class Pixtag implements \ArrayAccess, \Iterator
{
    /**
     * Filename
     *
     * @access private
     * @var string
     */
    private $filename;


    /**
     * Container
     *
     * @access private
     * @var array
     */
    private $container = array();

    
    /**
     * Constructor
     *
     * @access public
     * @param string $filename Path to the picture
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }


    /**
     * Read metadata from the picture
     *
     * @access public
     */
    public function read()
    {
        $c = new Command('exiv2 -PIEXkt '.$this->filename);
        $c->execute();
        $this->parse($c->getStdout());
    }


    /**
     * Parse metadata bloc from exiv2 output command
     *
     * @access public
     * @param string $data Raw command output of exiv2
     */
    public function parse($data)
    {
        $lines = explode("\n", trim($data));

        foreach ($lines as $line) {

            $results = preg_split('/ /', $line, -1, PREG_SPLIT_OFFSET_CAPTURE);

            if (isset($results[0][0])) {

                $key = $results[0][0];
                $value = '';

                for ($i = 1, $ilen = count($results); $i < $ilen; ++$i) {

                    if ($results[$i][0] !== '') {

                        $value = substr($line, $results[$i][1]);
                        break;
                    }
                }

                if ($value === '(Binary value suppressed)') {

                    $value = '';
                }

                $this->container[$key] = $value;
            }
        }
    }


    /**
     * Write metadata to the picture
     * This method erase all keys and then add them to the picture
     *
     * @access public
     */
    public function write()
    {
        $commands = array();

        foreach ($this->container as $key => $value) {

            $commands[] = sprintf('-M "del %s"', $key);
            $commands[] = sprintf('-M "add %s %s"', $key, $value);
        }

        $c = new Command(sprintf(
            'exiv2 %s %s',
            implode(' ', $commands),
            $this->filename
        ));

        $c->execute();

        if ($c->getReturnValue() !== 0) {

            throw new \RuntimeException('Unable to write metadata');
        }
    }


    /**
     * Set a metadata
     *
     * @access public
     * @param string $offset Key name, see exiv2 documentation for keys list
     * @param string $value Key value
     */
    public function offsetSet($offset, $value)
    {
        $this->container[$offset] = $value;
    }


    /**
     * Check if a key exists
     *
     * @access public
     * @param string $offset Key name, see exiv2 documentation for keys list
     * @return boolean True if the key exists
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }


    /**
     * Remove a metadata
     *
     * @access public
     * @param string $offset Key name, see exiv2 documentation for keys list
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }


    /**
     * Get a metadata
     *
     * @access public
     * @param string $offset Key name, see exiv2 documentation for keys list
     * @return string Key value
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }


    /**
     * Reset the position of the container
     *
     * @access public
     */
    public function rewind()
    {
        reset($this->container);
    }


    /**
     * Current
     *
     * @access public
     * @return string Current value
     */
    public function current()
    {
        return current($this->container);
    }


    /**
     * Key
     *
     * @access public
     * @return string Current key
     */
    public function key()
    {
        return key($this->container);
    }


    /**
     * Next
     *
     * @access public
     */
    public function next()
    {
        next($this->container);
    }


    /**
     * Valid
     *
     * @access public
     * @return boolean True if the current key is valid
     */
    public function valid()
    {
        return isset($this->container[key($this->container)]);
    }
}