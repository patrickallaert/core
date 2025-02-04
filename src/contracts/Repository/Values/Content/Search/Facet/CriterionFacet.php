<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet;

/**
 * This class holds the count of content matching the criterion.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in Ibexa 4.0.0.
 */
class CriterionFacet extends Facet
{
    /**
     * The count of objects matching the criterion.
     *
     * @var int
     */
    public $count;
}

class_alias(CriterionFacet::class, 'eZ\Publish\API\Repository\Values\Content\Search\Facet\CriterionFacet');
