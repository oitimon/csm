<?php

namespace Csm\Tests;

use Csm\CsmType;

class CsmTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function testSettersGetters()
    {
        $type = CsmType::create();
        $this->assertEquals('any', $type->getType());
        $this->assertNull($type->getSubType());

        $type->setType(1);
        $this->assertEquals('1', $type->getType());
        $this->assertNull($type->getSubType());

        $type->setType(2, 3);
        $this->assertEquals('2', $type->getType());
        $this->assertEquals('3', $type->getSubType());

        $type->setSubType(4);
        $this->assertEquals('2', $type->getType());
        $this->assertEquals('4', $type->getSubType());

        $type->setType(5);
        $this->assertEquals('5', $type->getType());
        $this->assertEquals('4', $type->getSubType());
    }

    /** @test */
    public function testCreatingEmpty()
    {
        $type = CsmType::create();
        $this->assertEquals(['any', null], $type->toArray());
        $this->assertEquals('any', $type->toString());
        $this->assertEquals('any', (string) $type);
    }

    /** @test */
    public function testCreatingType()
    {
        $type = CsmType::create(CsmType::TYPE_VIDEO);
        $this->assertEquals(['video', null], $type->toArray());
        $this->assertEquals('video', (string) $type);
    }

    /** @test */
    public function testCreatingTypeSubtype()
    {
        $type = CsmType::create(CsmType::TYPE_IMAGE, 'jpg');
        $this->assertEquals(['img', 'jpg'], $type->toArray());
        $this->assertEquals('img_jpg', (string) $type);
    }

    /** @test */
    public function testHydrate()
    {
        $type = CsmType::createHydrate(null);
        $this->assertEquals('any', $type->getType());
        $this->assertNull($type->getSubType());

        $type = CsmType::createHydrate('1');
        $this->assertEquals('1', $type->getType());
        $this->assertNull($type->getSubType());

        $type = CsmType::createHydrate('1_2');
        $this->assertEquals('1', $type->getType());
        $this->assertEquals('2', $type->getSubType());

        $type = CsmType::createHydrate([]);
        $this->assertEquals('any', $type->getType());
        $this->assertNull($type->getSubType());

        $type = CsmType::createHydrate([1]);
        $this->assertEquals('1', $type->getType());
        $this->assertNull($type->getSubType());

        $type = CsmType::createHydrate([1, 2]);
        $this->assertEquals('1', $type->getType());
        $this->assertEquals('2', $type->getSubType());
    }
}
