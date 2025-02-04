<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Repository\Permission;

/**
 * Avoid test failure caused by time passing between generating expected & actual object.
 *
 * @return int
 */
function time()
{
    static $time = 1417624981;

    return ++$time;
}

namespace Ibexa\Tests\Core\Repository\Permission;

use Ibexa\Contracts\Core\Repository\PermissionCriterionResolver;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\User\UserReference;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Repository\Permission\CachedPermissionService;
use PHPUnit\Framework\TestCase;

/**
 * Mock test case for CachedPermissionService.
 */
class CachedPermissionServiceTest extends TestCase
{
    public function providerForTestPermissionResolverPassTrough()
    {
        $valueObject = $this
            ->getMockBuilder(ValueObject::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $userRef = $this
            ->getMockBuilder(UserReference::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            ['getCurrentUserReference', [], $userRef],
            ['setCurrentUserReference', [$userRef], null],
            ['hasAccess', ['content', 'remove', $userRef], false],
            ['canUser', ['content', 'remove', $valueObject, [new \stdClass()]], true],
            ['sudo', [static function () {}, $repository], null],
        ];
    }

    /**
     * Test for all PermissionResolver methods when they just pass true to underlying service.
     *
     * @dataProvider providerForTestPermissionResolverPassTrough
     *
     * @param $method
     * @param array $arguments
     * @param $expectedReturn
     */
    public function testPermissionResolverPassTrough($method, array $arguments, $expectedReturn)
    {
        if ($expectedReturn !== null) {
            $this->getPermissionResolverMock([$method])
                ->expects($this->once())
                ->method($method)
                ->with(...$arguments)
                ->willReturn($expectedReturn);
        } else {
            $this->getPermissionResolverMock([$method])
                ->expects($this->once())
                ->method($method)
                ->with(...$arguments);
        }

        $cachedService = $this->getCachedPermissionService();

        $actualReturn = $cachedService->$method(...$arguments);
        $this->assertSame($expectedReturn, $actualReturn);
    }

    public function testGetPermissionsCriterionPassTrough()
    {
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->getPermissionCriterionResolverMock(['getPermissionsCriterion'])
            ->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'remove')
            ->willReturn($criterionMock);

        $cachedService = $this->getCachedPermissionService();

        $actualReturn = $cachedService->getPermissionsCriterion('content', 'remove');
        $this->assertSame($criterionMock, $actualReturn);
    }

    public function testGetPermissionsCriterionCaching()
    {
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->getPermissionCriterionResolverMock(['getPermissionsCriterion'])
            ->expects($this->exactly(2))
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->willReturn($criterionMock);

        $cachedService = $this->getCachedPermissionService(2);

        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);

        // +1
        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);

        // +3, time() will be called twice and cache will be updated
        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);
    }

    public function testSetCurrentUserReferenceCacheClear(): void
    {
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->getPermissionCriterionResolverMock(['getPermissionsCriterion'])
            ->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->willReturn($criterionMock);

        $cachedService = $this->getCachedPermissionService(2);

        $actualReturn = $cachedService->getPermissionsCriterion('content', 'read');
        $this->assertSame($criterionMock, $actualReturn);

        $userRef = $this
            ->getMockBuilder(UserReference::class)
            ->getMockForAbstractClass();
        $cachedService->setCurrentUserReference($userRef);
    }

    /**
     * Returns the CachedPermissionService to test against.
     *
     * @param int $ttl
     *
     * @return \Ibexa\Core\Repository\Permission\CachedPermissionService
     */
    protected function getCachedPermissionService($ttl = 5)
    {
        return new CachedPermissionService(
            $this->getPermissionResolverMock(),
            $this->getPermissionCriterionResolverMock(),
            $ttl
        );
    }

    protected $permissionResolverMock;

    protected function getPermissionResolverMock($methods = [])
    {
        // Tests first calls here with methods set before initiating PermissionCriterionResolver with same instance.
        if ($this->permissionResolverMock !== null) {
            return $this->permissionResolverMock;
        }

        return $this->permissionResolverMock = $this
            ->getMockBuilder(PermissionResolver::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    protected $permissionCriterionResolverMock;

    protected function getPermissionCriterionResolverMock($methods = [])
    {
        // Tests first calls here with methods set before initiating PermissionCriterionResolver with same instance.
        if ($this->permissionCriterionResolverMock !== null) {
            return $this->permissionCriterionResolverMock;
        }

        return $this->permissionCriterionResolverMock = $this
            ->getMockBuilder(PermissionCriterionResolver::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}

class_alias(CachedPermissionServiceTest::class, 'eZ\Publish\Core\Repository\Tests\Permission\CachedPermissionServiceTest');
