<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\Location;

/**
 * Build a Subtree facet.
 *
 * If provided the search service returns a LocationFacet which
 * contains the counts for all Locations below the child Locations.
 *
 * @todo: check hierarchical facets
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in Ibexa 4.0.0.
 */
class LocationFacetBuilder extends Location
{
    /**
     * The parent location.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    public $location;
}

class_alias(LocationFacetBuilder::class, 'eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\Location\LocationFacetBuilder');
