<?php

/**
 * A implementation of the Singleton Pattern in php
 * that is compatible with PHP >= 8.0.
 *
 * Originally I had a nicer solution from
 * https://stackoverflow.com/a/37800033/3424521 that also
 * had __sleep() and __wakeup() private. This is no longer
 * possible under PHP 8.0.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * A modern implementation of the Singleton Pattern in php.
 *
 * @since      0.1.0
 * @since      0.4.0+ __wakeup() and __sleep() no longer private
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Singleton
{
    /**
     * Retuns the singleton
     *
     * @since    0.1.0
     */
    public static function instance()
    {
      static $instance = false;
      if( $instance === false )
      {
        // Late static binding (PHP 5.3+)
        $instance = new static();
      }

      return $instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function __sleep() {
        throw new Exception('This is a singleton, thus you should not call __sleep.');
    }

    public function __wakeup() {
        throw new Exception('This is a singleton, thus you should not call __wakeup.');
    }

}
