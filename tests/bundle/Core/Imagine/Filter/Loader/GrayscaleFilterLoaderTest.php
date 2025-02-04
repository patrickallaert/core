<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Core\Imagine\Filter\Loader;

use Ibexa\Bundle\Core\Imagine\Filter\Loader\GrayscaleFilterLoader;
use Imagine\Effects\EffectsInterface;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class GrayscaleFilterLoaderTest extends TestCase
{
    public function testLoad()
    {
        $image = $this->createMock(ImageInterface::class);
        $effects = $this->createMock(EffectsInterface::class);
        $image
            ->expects($this->once())
            ->method('effects')
            ->will($this->returnValue($effects));
        $effects
            ->expects($this->once())
            ->method('grayscale')
            ->will($this->returnValue($effects));

        $loader = new GrayscaleFilterLoader();
        $this->assertSame($image, $loader->load($image));
    }
}

class_alias(GrayscaleFilterLoaderTest::class, 'eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader\GrayscaleFilterLoaderTest');
