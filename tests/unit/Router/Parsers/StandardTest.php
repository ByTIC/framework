<?php


namespace Nip\Tests\Unit\Router\Parsers;

use Nip\Router\Parser\Standard;

/**
 * Test class for Nip_Route_Abstract.
 * Generated by PHPUnit on 2010-11-17 at 15:16:44.
 */
class StandardTest extends  \Codeception\TestCase\Test
{

    /**
     * @var Standard
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
		$this->object = new Standard();
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
//            'controller' => 'lorem',
//            'action' => 'ipsum',
//            'company' => 'dolo&rem',
//        );
//		static::assertEquals('lorem/ipsum?company=dolo%26rem', $this->object->assemble($params));
//
//        $this->object->setMap('admin/:controller/:action');
//		static::assertEquals('admin/lorem/ipsum?company=dolo%26rem', $this->object->assemble($params));
//
//        unset ($params['action']);
//		static::assertEquals('admin/lorem/?company=dolo%26rem', $this->object->assemble($params));
//    }
//
//    public function testMatch()
//    {
//		static::assertFalse($this->object->match('shop/category_cast/asdasd'));
//		static::assertTrue($this->object->match('shop/category_cast/'));
//
//		static::assertTrue($this->object->match('shop/cart'));
//		static::assertEquals(array('controller' => 'shop', 'action' => 'cart'), $this->object->getParams());
//
//		static::assertTrue($this->object->match('shop/'));
//		static::assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//
//		static::assertTrue($this->object->match('shop'));
//		static::assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//    }
//
//    public function testMatchCustom()
//    {
//        $this->object->setMap('admin/:controller/:action');
//        
//		static::assertFalse($this->object->match('shop/category_cast/asdasd'));
//		static::assertFalse($this->object->match('shop/category_cast/'));
//
//		static::assertFalse($this->object->match('admin/test/asd/category_cast/'));
//
//		static::assertTrue($this->object->match('admin/shop/cart'));
//		static::assertEquals(array('controller' => 'shop', 'action' => 'cart'), $this->object->getParams());
//
//		static::assertTrue($this->object->match('admin/shop/'));
//		static::assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//
//		static::assertTrue($this->object->match('admin/shop'));
//		static::assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//    }


}