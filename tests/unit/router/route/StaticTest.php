<?php

/**
 * Test class for Nip_Route_Abstract.
 * Generated by PHPUnit on 2010-11-17 at 15:16:44.
 */
class Nip_Route_StaticTest extends  \Codeception\TestCase\Test
{

    /**
     * @var Nip_Route_Abstract
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
		$this->object = new Nip_Route_Static();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

//    public function testAssemble()
//    {
//        $params = array(
//            'url' => 'lorem',
//            'name' => 'ipsum',
//            'company' => 'dolo&rem',
//        );
//		$this->assertEquals('?url=lorem&name=ipsum&company=dolo%26rem', $this->object->assemble($params));
//
//        $this->object->setMap('shop/cart');
//		$this->assertEquals('shop/cart?url=lorem&name=ipsum&company=dolo%26rem', $this->object->assemble($params));
//    }
//
//    public function testMatch()
//    {
//        $map = 'shop/cart';
//		$this->object->setMap($map);
//		$this->assertFalse($this->object->match('shop/category_cast/'));
//		$this->assertTrue($this->object->match('shop/cart'));
//    }

}