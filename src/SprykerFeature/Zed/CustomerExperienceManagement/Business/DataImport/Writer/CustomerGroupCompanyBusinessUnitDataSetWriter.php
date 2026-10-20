<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Writer;

use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;

class CustomerGroupCompanyBusinessUnitDataSetWriter extends AbstractCustomerGroupMembershipDataSetWriter
{
    protected function getMemberIdDataSetKey(): string
    {
        return CustomerGroupMembershipDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT;
    }

    /**
     * @param array<int, array<int>> $memberIdsByIdCustomerGroup
     */
    protected function createMemberships(array $memberIdsByIdCustomerGroup): void
    {
        $this->customerExperienceManagementEntityManager->createCustomerGroupToCompanyBusinessUnitRelations($memberIdsByIdCustomerGroup);
    }
}
