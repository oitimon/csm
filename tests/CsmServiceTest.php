<?php

namespace Csm\Tests;

use Csm\Containers\ChainElement;
use Csm\CsmException;
use Csm\CsmService;
use Csm\Driver\Filesystem;
use Csm\Driver\Hole;
use Csm\Tests\Stub\Helper;

class CmsServiceTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    /**
     * @var array
     */
    protected $chainElements = [];

    /** @test */
    public function testNotCorrectDefaultDriver1()
    {
        $this->setExpectedException(
            CsmException::class,
            'defaultDriver parameter is not set for CsmService'
        );
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('defaultDriver parameter is not set for CsmService');
        new CsmService($this->getParams([CsmService::PARAM_DEFAULT_DRIVER => '']));
    }

    /** @test */
    public function testNotCorrectDefaultDriver2()
    {
        $this->setExpectedException(
            CsmException::class,
            'Drivername "stub" is not present in CsmService params'
        );
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Drivername "stub" is not present in CsmService params');
        $service = new CsmService($this->getParams([CsmService::PARAM_DEFAULT_DRIVER => 'stub']));
        $service->getDriver();
    }

    /** @test */
    public function testNotCorrectDefaultDriver3()
    {
        $this->setExpectedException(
            CsmException::class,
            'Drivername "stub" has no driver type in CsmService params'
        );
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Drivername "stub" has no driver type in CsmService params');
        $service = new CsmService($this->getParams([
            CsmService::PARAM_DEFAULT_DRIVER => 'stub',
            CsmService::PARAM_DRIVERS => [
                'stub' => ''
            ]
        ]));
        $service->getDriver();
    }

    /** @test */
    public function testBasicDefaultDriver()
    {
        $fullFilename = $this->getFullPath() . '/' . $this->getFilename();
        $service = new CsmService($this->getParams());

        // file and url is absent berfore creating
        $this->assertFalse($service->isPresent($this->getIdent(), $this->getFilename()));
        $this->assertFalse($service->getPreparedUrl($this->getIdent(), $this->getFilename()));

        // saving file
        $this->chainElements = [];
        $this->assertTrue($service->set($this->getIdent(), $this->getContent(), $this->getFilename()));
        $this->assertEquals(['20_0_0', '20_20_1'], $this->chainElements);

        // present file?
        $this->assertTrue($service->isPresent($this->getIdent(), $this->getFilename()));

        // try to get content
        $this->chainElements = [];
        $this->assertEquals($this->getContent(), file_get_contents($fullFilename));
        $this->assertEquals($this->getContent(), $service->get($this->getIdent(), $this->getFilename()));
        $this->assertEquals(['20_0_0', '20_20_1'], $this->chainElements);

        // get url
        $this->assertEquals(
            $this->getUrl($this->getFilename()),
            $service->getPreparedUrl($this->getIdent(), $this->getFilename())
        );
        $this->assertFalse($service->getPreparedUrl($this->getIdent(), '2.txt'));

        // copy file (to same and other Ident)
        $this->assertTrue($service->copy($this->getIdent(), $this->getIdent(), $this->getFilename(), '2.txt'));
        $this->assertTrue($service->copy($this->getIdent(), $this->getIdent('next'), '2.txt'));
        $this->assertTrue($service->copy($this->getIdent('next'), $this->getIdent('nextNext'), '2.txt', '3.txt'));
        $this->assertTrue($service->isPresent($this->getIdent('nextNext'), '3.txt'));

        // deleting file
        $this->assertTrue($service->delete($this->getIdent(), $this->getFilename()));
        // check after deleting
        $this->assertFalse($service->isPresent($this->getIdent(), $this->getFilename()));
        $this->assertFalse($service->getPreparedUrl($this->getIdent(), $this->getFilename()));
        $this->assertFalse(@file_get_contents($fullFilename));
        $this->setExpectedException(CsmException::class, 'Can not read file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Can not read file');
        $service->get($this->getIdent(), $this->getFilename());
    }

    /** @test */
    public function testCanNotReadNotExistingFileDefaultDriver()
    {
        $service = new CsmService($this->getParams());
        $this->setExpectedException(CsmException::class, 'Can not read file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessageRegExp('/Can not read file/');
        $this->assertFalse($service->get($this->getIdent(), $this->getFilename()));
    }

    /** @test  */
    public function testDeleteErrorsDefaultDriver()
    {
        $service = new CsmService($this->getParams());
        $this->setExpectedException(CsmException::class, 'Can not delete file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessageRegExp('/Can not delete file/');
        $this->assertFalse($service->delete($this->getIdent(), 'as'));
    }

    /** @test  */
    public function testCopyErrorsDefaultDriver()
    {
        $service = new CsmService($this->getParams());
        $this->setExpectedException(CsmException::class, 'Can not copy file');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessageRegExp('/Can not copy file/');
        $this->assertFalse($service->copy($this->getIdent(), $this->getIdent(), 'as'));
    }

    /** @test  */
    public function testDriverFromParamsError()
    {
        $service = new CsmService($this->getParams());
        $this->setExpectedException(CsmException::class, 'Custom driver has no driver type in params');
        //$this->expectException(CsmException::class);
        //$this->expectExceptionMessage('Custom driver has no driver type in params');
        $service->getDriver([]);
    }

    /** @test  */
    public function testDriverFromDriver()
    {
        $service = new CsmService($this->getParams());
        $driver = new Hole([]);
        $this->assertTrue($service->getDriver($driver) instanceof Hole);
        $this->assertTrue($service->getDriver(md5(serialize($driver->getParams()))) instanceof Hole);
    }

    /** @test  */
    public function testDriverFromParams()
    {
        $service = new CsmService($this->getParams());
        $this->assertTrue($service->getDriver(['type' => Hole::class]) instanceof Hole);
    }

    /**
     * @param array $moreParams
     * @return array
     */
    protected function getParams(array $moreParams = array())
    {
        return array_merge([
            'drivers' => [
                'main' => [
                    'type'         => Filesystem::class,
                    'resourcePath' => $this->getReourceDirPath(true),
                    'resourceUrl'  => 'http://localhost/test'
                ],
                'all' => [
                    'readEvent' => function (ChainElement $chainElement) {
                        $this->chainElements[] = (string)$chainElement;
                        return true;
                    },
                    'writeEvent' => function (ChainElement $chainElement) {
                        $this->chainElements[] = (string)$chainElement;
                        return true;
                    },
                ]
            ]
        ], $moreParams);
    }
}
