<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Core\Repository\Values\Filter;

use Ibexa\Contracts\Core\Persistence\Filter\Doctrine\FilteringQueryBuilder;

/**
 * Extension point to build filtering query for a given Criterion.
 *
 * Follows visitor pattern using buildQuery method to visit an implementation.
 */
interface CriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool;

    /**
     * Apply necessary Doctrine Query clauses & return part to be used for WHERE constraints.
     *
     * @return string|null string injected as WHERE constraints, null to skip injecting.
     */
    public function buildQueryConstraint(FilteringQueryBuilder $queryBuilder, FilteringCriterion $criterion): ?string;
}

class_alias(CriterionQueryBuilder::class, 'eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder');
