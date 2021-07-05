<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-singleton.php';

class O3PO_SingletonTest extends O3PO_TestCase
{
        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
    public function test___construct() {

        $reflection = new ReflectionClass('O3PO_Singleton');
        $this->assertTrue($reflection->getMethod('__construct')->isPrivate());

        $singleton = O3PO_Singleton::instance();
        $this->assertInstanceOf(O3PO_Singleton::class, $singleton);

    }

    public function test___clone() {

        $reflection = new ReflectionClass('O3PO_Singleton');
        $this->assertTrue($reflection->getMethod('__clone')->isPrivate());

    }

    public function test___sleep() {

        $this->expectException(Exception::class);
        $singleton = O3PO_Singleton::instance();
        $singleton->__sleep();

    }

    public function test___wakeup() {

        $this->expectException(Exception::class);
        $singleton = O3PO_Singleton::instance();
        $singleton->__wakeup();

    }
}
