<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Resolver;

use Generated\Shared\Transfer\CustomerGroupTransfer;

interface CustomerGroupMembershipIdResolverInterface
{
    /**
     * @param list<string> $companyUuids
     *
     * @return list<int>
     */
    public function getCompanyIdsToAssign(array $companyUuids): array;

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return list<int>
     */
    public function getCompanyBusinessUnitIdsToAssign(array $companyBusinessUnitUuids): array;

    public function findMemberIdCompany(CustomerGroupTransfer $customerGroupTransfer, string $companyUuid): ?int;

    public function findMemberIdCompanyBusinessUnit(CustomerGroupTransfer $customerGroupTransfer, string $companyBusinessUnitUuid): ?int;
}
