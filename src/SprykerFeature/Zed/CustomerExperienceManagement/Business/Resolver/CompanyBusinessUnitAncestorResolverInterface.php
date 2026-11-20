<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Resolver;

interface CompanyBusinessUnitAncestorResolverInterface
{
    /**
     * @param array<int, int|null> $parentCompanyBusinessUnitIds
     *
     * @return list<int>
     */
    public function resolveCompanyBusinessUnitIdsWithAncestors(int $idCompanyBusinessUnit, array $parentCompanyBusinessUnitIds): array;
}
