<?php

namespace Csm\Tests\Driver;

use Csm\Containers\ChainElement;
use Csm\CsmException;
use Csm\Driver\Filesystem;
use Csm\Tests\Stub\Helper;
use Csm\Tests\Stub\Stub1;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    /**
     * @var array
     */
    protected $chainElements;

    /** @test */
    public function testBasic()
    {
        $fullFilename = $this->getFullPath() . '/' . $this->getFilename();
        $driver = new Filesystem($this->getParams());
        $driverNs = new Filesystem($this->getParams(['strict' => false]));

        // file and url is absent berfore creating
        $this->assertFalse($driver->isPresent($this->getIdent(), $this->getFilename()));
        $this->assertFalse($driver->getPreparedUrl($this->getIdent(), $this->getFilename()));

        // saving file
        $this->assertTrue($driver->set($this->getIdent(), $this->getContent(), $this->getFilename()));

        // present file?
        $this->assertTrue($driver->isPresent($this->getIdent(), $this->getFilename()));

        // try to get content
        $this->assertEquals($this->getContent(), file_get_contents($fullFilename));
        $this->assertEquals($this->getContent(), $driver->get($this->getIdent(), $this->getFilename()));

        // get url (strict not strict)
        $this->assertEquals(
            $this->getUrl($this->getFilename()),
            $driver->getPreparedUrl($this->getIdent(), $this->getFilename())
        );
        $this->assertFalse($driver->getPreparedUrl($this->getIdent(), '2.txt'));
        $this->assertEquals(
            $this->getUrl('2.txt'),
            $driverNs->getPreparedUrl($this->getIdent(), '2.txt')
        );

        // copy file (to same and other Ident)
        $this->assertTrue($driver->copy($this->getIdent(), $this->getIdent(), $this->getFilename(), '2.txt'));
        $this->assertTrue($driver->copy($this->getIdent(), $this->getIdent('next'), '2.txt'));
        $this->assertTrue($driver->copy($this->getIdent('next'), $this->getIdent('nextNext'), '2.txt', '3.txt'));
        $this->assertTrue($driver->isPresent($this->getIdent('nextNext'), '3.txt'));

        // deleting file
        $this->assertTrue($driver->delete($this->getIdent(), $this->getFilename()));
        // check after deleting
        $this->assertFalse($driver->isPresent($this->getIdent(), $this->getFilename()));
        $this->assertFalse($driver->getPreparedUrl($this->getIdent(), $this->getFilename()));
        $this->assertFalse($driverNs->get($this->getIdent(), $this->getFilename()));
        $this->assertFalse(@file_get_contents($fullFilename));
    }

    /** @test */
    public function testCanNotReadNotExistingFile()
    {
        // not strict
        $driver = new Filesystem(array_merge($this->getParams(), ['strict' => false]));
        $this->assertFalse($driver->get($this->getIdent(), $this->getFilename()));

        // strict
        $driver = new Filesystem($this->getParams());
        //$this->setExpectedException(CsmException::class, 'Can not read file');
        $this->expectException(CsmException::class);
        $this->expectExceptionMessageRegExp('/Can not read file/');
        $this->assertFalse($driver->get($this->getIdent(), $this->getFilename()));
    }

    /** @test  */
    public function testCanNotSaveErrors()
    {
        // not strict
        $driver = new Stub1($this->getParams(['strict' => false]));
        $this->assertFalse($driver->set($this->getIdent(), 'as', 'as'));

        // strict
        $driver = new Stub1($this->getParams());
        //$this->setExpectedException(CsmException::class, 'Can not save file');
        $this->expectException(CsmException::class);
        $this->expectExceptionMessageRegExp('/Can not save file/');
        $this->assertFalse($driver->set($this->getIdent(), 'as', 'as'));
    }

    /** @test  */
    public function testDeleteErrors()
    {
        // not strict
        $driver = new Filesystem($this->getParams(['strict' => false]));
        $this->assertFalse($driver->delete($this->getIdent(), 'as'));

        // strict
        $driver = new Filesystem($this->getParams());
        //$this->setExpectedException(CsmException::class, 'Can not delete file');
        $this->expectException(CsmException::class);
        $this->expectExceptionMessageRegExp('/Can not delete file/');
        $this->assertFalse($driver->delete($this->getIdent(), 'as'));
    }

    /** @test  */
    public function testCopyErrors()
    {
        // not strict
        $driver = new Filesystem($this->getParams(['strict' => false]));
        $this->assertFalse($driver->copy($this->getIdent(), $this->getIdent(), 'as'));

        // strict
        $driver = new Filesystem($this->getParams());
        //$this->setExpectedException(CsmException::class, 'Can not copy file');
        $this->expectException(CsmException::class);
        $this->expectExceptionMessageRegExp('/Can not copy file/');
        $this->assertFalse($driver->copy($this->getIdent(), $this->getIdent(), 'as'));
    }

    /** @test  */
    public function testNotCorrectResourcePath()
    {
        $driver = new Filesystem($this->getParams(['resourcePath' => '\no', 'noFileCache' => true]));
        //$this->setExpectedException(CsmException::class, 'no is not a directory');
        $this->expectException(CsmException::class);
        $this->expectExceptionMessage('\no is not a directory');
        $driver->set($this->getIdent(), 'as', 'as');
    }

    /** @test */
    public function testEvents()
    {
        $content = $this->getContentLong();
        $driver = new Filesystem($this->getParams(['strict' => false,
            'readEvent' => function (ChainElement $chainElement) {
                $this->chainElements[] = $chainElement->toArray();
                return true;
            },
            'writeEvent' => function (ChainElement $chainElement) {
                $this->chainElements[] = $chainElement->toArray();
                return true;
            },
        ]));

        // saving file
        $this->chainElements = [];
        $this->assertTrue($driver->set($this->getIdent(), $content, $this->getFilename()));
        $this->assertEquals([
            ['size' => 40842, 'bytesProceed' => 0, 'completed' => 0],
            ['size' => 40842, 'bytesProceed' => 40842, 'completed' => 1]
        ], $this->chainElements);

        // try to get content
        $this->chainElements = [];
        $this->assertEquals($content, $driver->get($this->getIdent(), $this->getFilename()));
        $this->assertEquals([
            ['size' => 40842, 'bytesProceed' => 0, 'completed' => 0],
            ['size' => 40842, 'bytesProceed' => 40842, 'completed' => 1]
        ], $this->chainElements);

        // try small chain size
        $driver->setParam('chainSize', 8192);
        $driver->setParam('readEvent', function (ChainElement $chainElement) {
            $this->chainElements[] = (string)$chainElement;
            return true;
        });
        $driver->setParam('writeEvent', function (ChainElement $chainElement) {
            $this->chainElements[] = (string)$chainElement;
            return true;
        });
        $this->chainElements = [];
        $this->assertTrue($driver->set($this->getIdent(), $content, $this->getFilename()));
        $this->assertEquals(
            ['40842_0_0', '40842_8192_0.20057783654082', '40842_16384_0.40115567308163',
                '40842_24576_0.60173350962245', '40842_32768_0.80231134616326', '40842_40842_1'],
            $this->chainElements
        );
        $this->chainElements = [];
        $this->assertEquals($content, $driver->get($this->getIdent(), $this->getFilename()));
        $this->assertEquals(
            ['40842_0_0', '40842_8192_0.20057783654082', '40842_16384_0.40115567308163',
                '40842_24576_0.60173350962245', '40842_32768_0.80231134616326', '40842_40842_1'],
            $this->chainElements
        );

        // try break reading by event
        $driver->setParam('strict', false);
        // when start
        $driver->setParam('readEvent', function (ChainElement $chainElement) {
            $this->chainElements[] = (string)$chainElement;
            return false;
        });
        $this->chainElements = [];
        $this->assertFalse($driver->get($this->getIdent(), $this->getFilename()));
        $this->assertEquals(
            ['40842_0_0'],
            $this->chainElements
        );
        // when works
        $driver->setParam('readEvent', function (ChainElement $chainElement) {
            $this->chainElements[] = (string)$chainElement;
            return $chainElement->completed > 0.6 ? false : true;
        });
        $this->chainElements = [];
        $this->assertFalse($driver->get($this->getIdent(), $this->getFilename()));
        $this->assertEquals(
            ['40842_0_0', '40842_8192_0.20057783654082', '40842_16384_0.40115567308163',
                '40842_24576_0.60173350962245'],
            $this->chainElements
        );

        // try break writing by event
        $this->assertTrue($driver->isPresent($this->getIdent(), $this->getFilename()));
        // when start
        $driver->setParam('writeEvent', function (ChainElement $chainElement) {
            $this->chainElements[] = (string)$chainElement;
            return false;
        });
        $this->chainElements = [];
        $this->assertFalse($driver->set($this->getIdent(), $content, $this->getFilename()));
        $this->assertEquals(
            ['40842_0_0'],
            $this->chainElements
        );
        $this->assertFalse($driver->isPresent($this->getIdent(), $this->getFilename()));
        // when works
        $driver->setParam('writeEvent', function (ChainElement $chainElement) {
            $this->chainElements[] = (string)$chainElement;
            return $chainElement->completed > 0.6 ? false : true;
        });
        $this->chainElements = [];
        $this->assertFalse($driver->set($this->getIdent(), $content, $this->getFilename()));
        $this->assertEquals(
            ['40842_0_0', '40842_8192_0.20057783654082', '40842_16384_0.40115567308163',
                '40842_24576_0.60173350962245'],
            $this->chainElements
        );
        $this->assertFalse($driver->isPresent($this->getIdent(), $this->getFilename()));
    }
}
