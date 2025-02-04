<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Core\Limitation;

use Ibexa\Contracts\Core\Persistence\Content\ObjectState\Handler as SPIHandler;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\NewObjectStateLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ObjectStateLimitation;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Limitation\NewObjectStateLimitationType;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ObjectState\ObjectState;

/**
 * Test Case for LimitationType.
 */
class NewObjectStateLimitationTypeTest extends Base
{
    /** @var \Ibexa\Contracts\Core\Persistence\Content\ObjectState\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $objectStateHandlerMock;

    /**
     * Setup Handler mock.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectStateHandlerMock = $this->createMock(SPIHandler::class);
    }

    /**
     * Tear down Handler mock.
     */
    protected function tearDown(): void
    {
        unset($this->objectStateHandlerMock);
        parent::tearDown();
    }

    /**
     * @return \Ibexa\Core\Limitation\NewObjectStateLimitationType
     */
    public function testConstruct()
    {
        return new NewObjectStateLimitationType($this->getPersistenceMock());
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return [
            [new NewObjectStateLimitation()],
            [new NewObjectStateLimitation([])],
            [new NewObjectStateLimitation(['limitationValues' => [0, PHP_INT_MAX, '2', 's3fdaf32r']])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation\NewObjectStateLimitation $limitation
     * @param \Ibexa\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testAcceptValue(NewObjectStateLimitation $limitation, NewObjectStateLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException()
    {
        return [
            [new ObjectStateLimitation()],
            [new NewObjectStateLimitation(['limitationValues' => [true]])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation $limitation
     * @param \Ibexa\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, NewObjectStateLimitationType $limitationType)
    {
        $this->expectException(InvalidArgumentException::class);

        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return [
            [new NewObjectStateLimitation()],
            [new NewObjectStateLimitation([])],
            [new NewObjectStateLimitation(['limitationValues' => [2]])],
        ];
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation\NewObjectStateLimitation $limitation
     */
    public function testValidatePass(NewObjectStateLimitation $limitation)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('objectStateHandler')
                ->will($this->returnValue($this->objectStateHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->objectStateHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value);
            }
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate($limitation);
        self::assertEmpty($validationErrors);
    }

    /**
     * @return array
     */
    public function providerForTestValidateError()
    {
        return [
            [new NewObjectStateLimitation(), 0],
            [new NewObjectStateLimitation(['limitationValues' => [0]]), 1],
            [new NewObjectStateLimitation(['limitationValues' => [0, PHP_INT_MAX]]), 2],
        ];
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation\NewObjectStateLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError(NewObjectStateLimitation $limitation, $errorCount)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('objectStateHandler')
                ->will($this->returnValue($this->objectStateHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->objectStateHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value)
                    ->will($this->throwException(new NotFoundException('contentType', $value)));
            }
        } else {
            $this->getPersistenceMock()
                ->expects($this->never())
                ->method($this->anything());
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    /**
     * @depends testConstruct
     *
     * @param \Ibexa\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testBuildValue(NewObjectStateLimitationType $limitationType)
    {
        $expected = ['test', 'test' => 9];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(NewObjectStateLimitation::class, $value);
        self::assertIsArray($value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return [
            // ContentInfo, no access
            [
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => false,
            ],
            // Content, no access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2]]),
                'object' => new Content(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => false,
            ],
            // Content, no access  (both must match!)
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2, 22]]),
                'object' => new Content(),
                'targets' => [new ObjectState(['id' => 2]), new ObjectState(['id' => 66])],
                'expected' => false,
            ],
            // ContentInfo, with access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [66]]),
                'object' => new ContentInfo(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => true,
            ],
            // VersionInfo, with access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2, 66]]),
                'object' => new VersionInfo(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => true,
            ],
            // Content, with access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2, 66]]),
                'object' => new Content(),
                'targets' => [new ObjectState(['id' => 66]), new ObjectState(['id' => 2])],
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        NewObjectStateLimitation $limitation,
        ValueObject $object,
        array $targets,
        $expected
    ) {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $persistenceMock = $this->getPersistenceMock();
        $persistenceMock
            ->expects($this->never())
            ->method($this->anything());

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );

        self::assertIsBool($value);
        self::assertEquals($expected, $value);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument()
    {
        return [
            // invalid limitation
            [
                'limitation' => new ObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => [new Location()],
            ],
            // invalid object
            [
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => [new Location()],
            ],
            // empty targets
            [
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluateInvalidArgument
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        array $targets
    ) {
        $this->expectException(InvalidArgumentException::class);

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $persistenceMock = $this->getPersistenceMock();
        $persistenceMock
            ->expects($this->never())
            ->method($this->anything());

        $v = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );
        var_dump($v); // intentional, debug in case no exception above
    }

    /**
     * @depends testConstruct
     *
     * @param \Ibexa\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testGetCriterion(NewObjectStateLimitationType $limitationType)
    {
        $this->expectException(NotImplementedException::class);

        $limitationType->getCriterion(
            new NewObjectStateLimitation([]),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \Ibexa\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testValueSchema(NewObjectStateLimitationType $limitationType)
    {
        $this->expectException(NotImplementedException::class);

        self::assertEquals(
            [],
            $limitationType->valueSchema()
        );
    }
}

class_alias(NewObjectStateLimitationTypeTest::class, 'eZ\Publish\Core\Limitation\Tests\NewObjectStateLimitationTypeTest');
