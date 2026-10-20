<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence;

use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;

interface CustomerExperienceManagementRepositoryInterface
{
    public function getCustomerGroupCompanyCollection(
        CustomerGroupCompanyCriteriaTransfer $customerGroupCompanyCriteriaTransfer
    ): CustomerGroupCompanyCollectionTransfer;

    public function getCustomerGroupCompanyBusinessUnitCollection(
        CustomerGroupCompanyBusinessUnitCriteriaTransfer $customerGroupCompanyBusinessUnitCriteriaTransfer
    ): CustomerGroupCompanyBusinessUnitCollectionTransfer;

    /**
     * @param list<int> $companyIds
     *
     * @return list<int>
     */
    public function getExistingCompanyIds(array $companyIds): array;

    /**
     * @param list<int> $companyBusinessUnitIds
     *
     * @return list<int>
     */
    public function getExistingCompanyBusinessUnitIds(array $companyBusinessUnitIds): array;

    /**
     * @param list<int> $companyIds
     *
     * @return list<int>
     */
    public function getAssignedCompanyIds(int $idCustomerGroup, array $companyIds): array;

    /**
     * @param list<int> $companyBusinessUnitIds
     *
     * @return list<int>
     */
    public function getAssignedCompanyBusinessUnitIds(int $idCustomerGroup, array $companyBusinessUnitIds): array;
}
