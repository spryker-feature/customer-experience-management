<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Writer;

use Generated\Shared\Transfer\CustomerGroupTransfer;

interface CustomerGroupMembershipWriterInterface
{
    /**
     * @param list<int> $companyIds
     */
    public function assignCompanies(CustomerGroupTransfer $customerGroupTransfer, array $companyIds): void;

    /**
     * @param list<int> $companyIds
     */
    public function unassignCompanies(CustomerGroupTransfer $customerGroupTransfer, array $companyIds): void;

    /**
     * @param list<int> $companyIds
     */
    public function replaceCompanies(CustomerGroupTransfer $customerGroupTransfer, array $companyIds): void;

    /**
     * @param list<int> $companyBusinessUnitIds
     */
    public function assignCompanyBusinessUnits(CustomerGroupTransfer $customerGroupTransfer, array $companyBusinessUnitIds): void;

    /**
     * @param list<int> $companyBusinessUnitIds
     */
    public function unassignCompanyBusinessUnits(CustomerGroupTransfer $customerGroupTransfer, array $companyBusinessUnitIds): void;

    /**
     * @param list<int> $companyBusinessUnitIds
     */
    public function replaceCompanyBusinessUnits(CustomerGroupTransfer $customerGroupTransfer, array $companyBusinessUnitIds): void;
}
