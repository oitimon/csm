<?php

namespace Csm\Tests\Type;

use Csm\Type\TypeImage;

class CsmTypeImageTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function testBasic()
    {
        $type = TypeImage::create();
        $this->assertEquals('img', $type->toString());

        $type = TypeImage::createImage(TypeImage::SUBTYPE_GIF);
        $this->assertEquals('img_gif', (string) $type);
    }
}
