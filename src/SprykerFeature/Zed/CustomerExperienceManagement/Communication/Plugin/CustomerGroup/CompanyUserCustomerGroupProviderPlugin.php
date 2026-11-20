<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupProviderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory getBusinessFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CompanyUserCustomerGroupProviderPlugin extends AbstractPlugin implements CustomerGroupProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Uses only the company user the customer currently shops as, `CustomerTransfer.companyUserTransfer`, the
     *   customer's other company users are never considered.
     * - Takes the company and business unit from `CustomerTransfer.companyUserTransfer.fkCompany` and `.fkCompanyBusinessUnit`.
     * - When `fkCompany` is not set, reads them by `idCompanyUser`, and returns no groups when that company user belongs
     *   to another customer, is inactive, or its company is inactive or not approved.
     * - Returns no groups when `CustomerTransfer.idCustomer` or the company user is not set.
     * - Returns the customer groups the company is assigned to.
     * - Returns the customer groups the business unit, or any ancestor business unit up the
     *   `fk_parent_company_business_unit` chain, is assigned to, the unit's own first. A business unit membership never
     *   applies to the unit's parent or siblings.
     *
     * @api
     */
    public function provideCustomerGroups(CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer
    {
        return $this->getBusinessFactory()
            ->createCompanyUserCustomerGroupReader()
            ->getCustomerGroupCollectionByCustomer($customerTransfer);
    }
}
