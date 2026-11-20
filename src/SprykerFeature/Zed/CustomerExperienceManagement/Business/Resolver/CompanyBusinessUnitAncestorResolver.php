<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Resolver;

class CompanyBusinessUnitAncestorResolver implements CompanyBusinessUnitAncestorResolverInterface
{
    /**
     * @param array<int, int|null> $parentCompanyBusinessUnitIds
     *
     * @return list<int>
     */
    public function resolveCompanyBusinessUnitIdsWithAncestors(int $idCompanyBusinessUnit, array $parentCompanyBusinessUnitIds): array
    {
        $companyBusinessUnitIds = [];
        $idCurrentCompanyBusinessUnit = $idCompanyBusinessUnit;

        while (
            $idCurrentCompanyBusinessUnit !== null
            && $this->isUnvisitedCompanyBusinessUnitOfCompany($idCurrentCompanyBusinessUnit, $companyBusinessUnitIds, $parentCompanyBusinessUnitIds)
        ) {
            $companyBusinessUnitIds[] = $idCurrentCompanyBusinessUnit;
            $idCurrentCompanyBusinessUnit = $parentCompanyBusinessUnitIds[$idCurrentCompanyBusinessUnit] ?? null;
        }

        return $companyBusinessUnitIds;
    }

    /**
     * @param list<int> $companyBusinessUnitIds
     * @param array<int, int|null> $parentCompanyBusinessUnitIds
     */
    protected function isUnvisitedCompanyBusinessUnitOfCompany(
        int $idCompanyBusinessUnit,
        array $companyBusinessUnitIds,
        array $parentCompanyBusinessUnitIds
    ): bool {
        return array_key_exists($idCompanyBusinessUnit, $parentCompanyBusinessUnitIds)
            && !in_array($idCompanyBusinessUnit, $companyBusinessUnitIds, true);
    }
}
