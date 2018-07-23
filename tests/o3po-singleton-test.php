<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-singleton.php';

class O3PO_SingletonTest extends PHPUnit_Framework_TestCase
{

    public function test___construct() {
        $exception = null;
        try
        {
            $singleton = new O3PO_Singleton();
        }
        catch (Exception $e) {
            $exception = $e;
        }
        
        $this->assertInstanceOf(Error::class, $exception);

        $singleton = O3PO_Singleton::instance();

        $this->assertInstanceOf(O3PO_Singleton::class, $singleton);
    }

    public function test___clone() {
        $exception = null;
        try
        {
            $singleton = O3PO_Singleton::instance();
            $clone = clone $singleton;
        }
        catch (Exception $e) {
            $exception = $e;
        }
        
        $this->assertInstanceOf(Error::class, $exception);
    }    

}
