<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\MVC\Symfony\SiteAccess;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use function iterator_to_array;

class SiteAccessService implements SiteAccessServiceInterface, SiteAccessAware
{
    /** @var \Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface */
    private $provider;

    /** @var \Ibexa\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        SiteAccessProviderInterface $provider,
        ConfigResolverInterface $configResolver
    ) {
        $this->provider = $provider;
        $this->configResolver = $configResolver;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null): void
    {
        $this->siteAccess = $siteAccess;
    }

    public function exists(string $name): bool
    {
        return $this->provider->isDefined($name);
    }

    public function get(string $name): SiteAccess
    {
        if ($this->provider->isDefined($name)) {
            return $this->provider->getSiteAccess($name);
        }

        throw new NotFoundException('SiteAccess', $name);
    }

    public function getAll(): iterable
    {
        return $this->provider->getSiteAccesses();
    }

    public function getCurrent(): ?SiteAccess
    {
        return $this->siteAccess ?? null;
    }

    public function getSiteAccessesRelation(?SiteAccess $siteAccess = null): array
    {
        $siteAccess = $siteAccess ?? $this->siteAccess;
        $saRelationMap = [];

        /** @var \Ibexa\Core\MVC\Symfony\SiteAccess[] $saList */
        $saList = iterator_to_array($this->provider->getSiteAccesses());
        // First build the SiteAccess relation map, indexed by repository and rootLocationId.
        foreach ($saList as $sa) {
            $siteAccessName = $sa->name;

            $repository = $this->configResolver->getParameter('repository', 'ibexa.site_access.config', $siteAccessName);
            if (!isset($saRelationMap[$repository])) {
                $saRelationMap[$repository] = [];
            }

            $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id', 'ibexa.site_access.config', $siteAccessName);
            if (!isset($saRelationMap[$repository][$rootLocationId])) {
                $saRelationMap[$repository][$rootLocationId] = [];
            }

            $saRelationMap[$repository][$rootLocationId][] = $siteAccessName;
        }

        $siteAccessName = $siteAccess->name;
        $repository = $this->configResolver->getParameter('repository', 'ibexa.site_access.config', $siteAccessName);
        $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id', 'ibexa.site_access.config', $siteAccessName);

        return $saRelationMap[$repository][$rootLocationId];
    }
}

class_alias(SiteAccessService::class, 'eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService');
