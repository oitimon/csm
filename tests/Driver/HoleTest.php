<?php

namespace Csm\Tests\Driver;

use Csm\CsmException;
use Csm\CsmIdent;
use Csm\Driver\Filesystem;
use Csm\Driver\Hole;
use Csm\Tests\Stub\Stub1;

class HoleTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function testGet()
    {
        $driver = new Hole([Hole::PARAM_IS_STRICT => false]);
        $this->assertEquals('content', $driver->get($this->getIdent(), 'name'));
        $this->assertFalse($driver->get($this->getIdent(), 'error'));

        $driver = new Hole([]);
        $this->setExpectedException(CsmException::class, 'Can not read file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Can not read file');
        $driver->get($this->getIdent(), 'error');
    }

    /** @test */
    public function testSet()
    {
        $driver = new Hole([Hole::PARAM_IS_STRICT => false]);
        $this->assertTrue($driver->set($this->getIdent(), 'content', 'name'));
        $this->assertFalse($driver->set($this->getIdent(), 'content', 'error'));

        $driver = new Hole([]);
        $this->setExpectedException(CsmException::class, 'Can not save file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Can not save file');
        $driver->set($this->getIdent(), 'content', 'error');
    }

    /** @test */
    public function testDelete()
    {
        $driver = new Hole([Hole::PARAM_IS_STRICT => false]);
        $this->assertTrue($driver->delete($this->getIdent(), 'name'));
        $this->assertFalse($driver->delete($this->getIdent(), 'error'));

        $driver = new Hole([]);
        $this->setExpectedException(CsmException::class, 'Can not delete file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Can not delete file');
        $driver->delete($this->getIdent(), 'error');
    }

    /** @test */
    public function testIsPresent()
    {
        $driver = new Hole([]);
        $this->assertTrue($driver->isPresent($this->getIdent(), 'name'));
        $this->assertFalse($driver->isPresent($this->getIdent(), 'error'));
    }

    /** @test */
    public function testGetPreparedUrl()
    {
        $driver = new Hole([]);
        $this->assertEquals('http://url', $driver->getPreparedUrl($this->getIdent(), 'name'));
        $this->assertFalse($driver->getPreparedUrl($this->getIdent(), 'error'));
    }

    /** @test */
    public function testCopy()
    {
        $driver = new Hole([Hole::PARAM_IS_STRICT => false]);
        $this->assertTrue($driver->copy($this->getIdent(), $this->getIdent(), 'name1', 'name2'));
        $this->assertFalse($driver->copy($this->getIdent(), $this->getIdent(), 'error', 'name2'));

        $driver = new Hole([]);
        $this->setExpectedException(CsmException::class, 'Can not copy file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Can not copy file');
        $driver->copy($this->getIdent(), $this->getIdent(), 'error', 'name2');
    }

    /**
     * @return CsmIdent
     */
    protected function getIdent()
    {
        return CsmIdent::create()
            ->addResourceName('users')
            ->addString('Smith');
    }
}
