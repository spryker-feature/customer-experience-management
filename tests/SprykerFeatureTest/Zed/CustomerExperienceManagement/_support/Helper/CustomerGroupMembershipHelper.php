<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompany;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyBusinessUnit;

class CustomerGroupMembershipHelper extends Module
{
    public function haveCompanyMembership(CustomerGroupTransfer $customerGroupTransfer, CompanyTransfer $companyTransfer): void
    {
        (new SpyCustomerGroupToCompany())
            ->setFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
            ->setFkCompany($companyTransfer->getIdCompanyOrFail())
            ->save();
    }

    public function haveCompanyBusinessUnitMembership(
        CustomerGroupTransfer $customerGroupTransfer,
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): void {
        (new SpyCustomerGroupToCompanyBusinessUnit())
            ->setFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
            ->setFkCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail())
            ->save();
    }
}
