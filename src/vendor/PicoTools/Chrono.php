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
 * Chrono, tool for benchmarking
 * Calculate the duration of your code
 *
 * @author Frédéric Guillot
 */
class Chrono
{
    /**
     * Chronos values
     *
     * @access private
     * @static
     * @var array
     */
    private static $chronos = array();


    /**
     * Start a chrono
     *
     * @access public
     * @static
     * @param string $name Chrono name
     */
    public static function start($name)
    {
        self::$chronos[$name] = array(
            'start' => microtime(true),
            'finish' => 0
        );
    }


    /**
     * Stop a chrono
     *
     * @access public
     * @static
     * @param string $name Chrono name
     */
    public static function stop($name)
    {
        if (! isset(self::$chronos[$name])) {

            throw new \RuntimeException('Chrono not started!');
        }

        self::$chronos[$name]['finish'] = microtime(true);
    }


    /**
     * Get a duration of a chrono
     *
     * @access public
     * @static
     * @return float
     */
    public static function duration($name)
    {
        if (! isset(self::$chronos[$name])) {

            throw new \RuntimeException('Chrono not started!');
        }

        return self::$chronos[$name]['finish'] - self::$chronos[$name]['start'];
    }


    /**
     * Show all durations
     *
     * @access public
     * @static
     */
    public static function show()
    {
        foreach (self::$chronos as $name => $values) {

            echo $name.' = ';
            echo round($values['finish'] - $values['start'], 2).'s';
            echo PHP_EOL;
        }
    }
}