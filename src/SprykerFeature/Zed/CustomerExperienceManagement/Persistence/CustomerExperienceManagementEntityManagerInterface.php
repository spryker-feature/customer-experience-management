<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence;

interface CustomerExperienceManagementEntityManagerInterface
{
    /**
     * @param array<int, array<int>> $companyIdsByIdCustomerGroup
     */
    public function createCustomerGroupToCompanyRelations(array $companyIdsByIdCustomerGroup): void;

    /**
     * @param array<int> $companyIds
     */
    public function deleteCustomerGroupToCompanyRelations(int $idCustomerGroup, array $companyIds): void;

    /**
     * @param array<int> $companyIdsToKeep
     */
    public function deleteCustomerGroupToCompanyRelationsExcept(int $idCustomerGroup, array $companyIdsToKeep): void;

    /**
     * @param array<int, array<int>> $companyBusinessUnitIdsByIdCustomerGroup
     */
    public function createCustomerGroupToCompanyBusinessUnitRelations(array $companyBusinessUnitIdsByIdCustomerGroup): void;

    /**
     * @param array<int> $companyBusinessUnitIds
     */
    public function deleteCustomerGroupToCompanyBusinessUnitRelations(int $idCustomerGroup, array $companyBusinessUnitIds): void;

    /**
     * @param array<int> $companyBusinessUnitIdsToKeep
     */
    public function deleteCustomerGroupToCompanyBusinessUnitRelationsExcept(int $idCustomerGroup, array $companyBusinessUnitIdsToKeep): void;

    /**
     * @param array<int, array<int>> $customerIdsByIdCustomerGroup
     */
    public function createCustomerGroupToCustomerRelations(array $customerIdsByIdCustomerGroup): void;
}
