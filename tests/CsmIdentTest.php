<?php

namespace Csm\Tests;

use Csm\CsmException;
use Csm\CsmIdent;

class CssIdentTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function testCheckMinDefaultMinParamStrictNotStrict()
    {
        // not strict
        $params = [CsmIdent::PARAM_IS_STRICT => false];
        $this->assertFalse(CsmIdent::create($params)->checkIdent());

        // strict
        $params = [CsmIdent::PARAM_IS_STRICT => true];
        $this->setExpectedException(CsmException::class, 'CSM Ident must have 1 or more rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage();
        CsmIdent::create($params)->checkIdent();
    }

    /** @test */
    public function testCheckMinDefaultMinParam()
    {
        // default strict
        $this->setExpectedException(CsmException::class, 'CSM Ident must have 1 or more rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have 1 or more rows');
        CsmIdent::create()->checkIdent();
    }

    /** @test */
    public function testCheckMinCustomMinParamStrictNotStrict()
    {
        // correct
        $this->assertTrue(CsmIdent::create()->addResourceName('user')->addNumeric(18)->checkIdent());

        $params = [CsmIdent::PARAM_MIN_IDENTS => 2];
        $this->assertTrue(CsmIdent::create($params)->addResourceName('user')->addNumeric(18)->checkIdent());

        // not strict
        $params = [CsmIdent::PARAM_MIN_IDENTS => 3, CsmIdent::PARAM_IS_STRICT => false];
        $this->assertFalse(CsmIdent::create($params)->addResourceName('user')->addNumeric(18)->checkIdent());

        // strict
        $params = [CsmIdent::PARAM_MIN_IDENTS => 3, CsmIdent::PARAM_IS_STRICT => true];
        $this->setExpectedException(CsmException::class, 'CSM Ident must have 3 or more rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have 3 or more rows');
        CsmIdent::create($params)->addResourceName('user')->addNumeric(18)->checkIdent();
    }

    /** @test */
    public function testCheckMinCustomMinParam()
    {
        // default strict
        $params = [CsmIdent::PARAM_MIN_IDENTS => 3];
        $this->setExpectedException(CsmException::class, 'CSM Ident must have 3 or more rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have 3 or more rows');
        CsmIdent::create($params)->addResourceName('user')->addNumeric(18)->checkIdent();
    }

    /** @test */
    public function testCheckMaxDefaultMaxParamNotStrict()
    {
        $params = [CsmIdent::PARAM_IS_STRICT => false];

        // ok with 100 rows
        $ident = CsmIdent::create($params);
        for ($i = 1; $i <= 100; $i++) {
            $ident->addNumeric($i);
        }
        $this->assertTrue($ident->checkIdent());

        // and error with one more
        $ident->addNumeric(101);

        // not strict
        $this->assertFalse($ident->checkIdent());
    }

    /** @test */
    public function testCheckMaxDefaultMaxParamStrict()
    {
        $params = [CsmIdent::PARAM_IS_STRICT => true];

        // ok with 100 rows
        $ident = CsmIdent::create($params);
        for ($i = 1; $i <= 100; $i++) {
            $ident->addNumeric($i);
        }
        $this->assertTrue($ident->checkIdent());

        // and CsmException with one more
        $ident->addNumeric(101);

        // strict
        $this->setExpectedException(CsmException::class, 'CSM Ident must have not more than 100 rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have not more than 100 rows');
        $ident->checkIdent();
    }

    /** @test */
    public function testCheckMaxDefaultMaxParam()
    {
        // ok with 100 rows
        $ident = CsmIdent::create();
        for ($i = 1; $i <= 100; $i++) {
            $ident->addNumeric($i);
        }
        $this->assertTrue($ident->checkIdent());

        // and CsmException with one more
        $ident->addNumeric(101);

        // strict
        $this->setExpectedException(CsmException::class, 'CSM Ident must have not more than 100 rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have not more than 100 rows');
        $ident->checkIdent();
    }

    /** @test */
    public function testCheckMaxCustomMaxParamNotStrict()
    {
        $params = [CsmIdent::PARAM_MAX_IDENTS => 5, CsmIdent::PARAM_IS_STRICT => false];

        // ok with 5 rows
        $ident = CsmIdent::create($params);
        for ($i = 1; $i <= 5; $i++) {
            $ident->addNumeric($i);
        }
        $this->assertTrue($ident->checkIdent());

        // and error with one more
        $ident->addNumeric(6);

        // not strict
        $this->assertFalse($ident->checkIdent());
    }

    /** @test */
    public function testCheckMaxCustomMaxParamStrict()
    {
        $params = [CsmIdent::PARAM_MAX_IDENTS => 5, CsmIdent::PARAM_IS_STRICT => true];

        // ok with 5 rows
        $ident = CsmIdent::create($params);
        for ($i = 1; $i <= 5; $i++) {
            $ident->addNumeric($i);
        }
        $this->assertTrue($ident->checkIdent());

        // and CsmException with one more
        $ident->addNumeric(6);

        // strict
        $this->setExpectedException(CsmException::class, 'CSM Ident must have not more than 5 rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have not more than 5 rows');
        $ident->checkIdent();
    }

    /** @test */
    public function testCheckMaxCustomMaxParam()
    {
        $params = [CsmIdent::PARAM_MAX_IDENTS => 5];

        // ok with 100 rows
        $ident = CsmIdent::create($params);
        for ($i = 1; $i <= 5; $i++) {
            $ident->addNumeric($i);
        }
        $this->assertTrue($ident->checkIdent());

        // and CsmException with one more
        $ident->addNumeric(6);

        // strict
        $this->setExpectedException(CsmException::class, 'CSM Ident must have not more than 5 rows');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('CSM Ident must have not more than 5 rows');
        $ident->checkIdent();
    }

    /** @test */
    public function testHydratingSerializing()
    {
        $hashStr = 'sdlfjhsd;ofuosdufodsnf';
        $expected = [
            ['t' => 'd', 'i' => 'user'],
            ['t' => 's', 'i' => 'John Smith'],
            ['t' => 'n', 'i' => 18],
            ['t' => 'h', 'i' => md5($hashStr)]
        ];
        $ident = CsmIdent::create()
            ->addResourceName('user')
            ->addString('John Smith')
            ->addNumeric('18')
            ->addHash($hashStr);
        $this->assertEquals(4, $ident->count());
        $this->assertEquals($expected, $ident->toArray());
        $this->assertEquals(json_encode($expected), $ident->toString());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), (string) $ident);
    }

    /** @test */
    public function testBasic()
    {
        $ident = CsmIdent::create()
            ->addResourceName('user');
        $this->assertTrue(true);
    }
}
