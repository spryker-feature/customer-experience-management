<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupPostSavePluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory getBusinessFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CompanyBusinessUnitCustomerGroupPostSavePlugin extends AbstractPlugin implements CustomerGroupPostSavePluginInterface
{
    /**
     * {@inheritDoc}
     * - Applies `CustomerGroupTransfer.companyBusinessUnitAssignment` when it is set.
     * - When `CustomerGroupTransfer.companyBusinessUnitAssignment.isDeAssignAll` is true, removes with one delete every business unit membership of the customer group except those of the business units in `idsCompanyBusinessUnitToAssign`, which are kept as they are; `idsCompanyBusinessUnitToDeAssign` is not used then.
     * - Otherwise removes the business units listed in `CustomerGroupTransfer.companyBusinessUnitAssignment.idsCompanyBusinessUnitToDeAssign` from the customer group.
     * - Assigns the business units listed in `CustomerGroupTransfer.companyBusinessUnitAssignment.idsCompanyBusinessUnitToAssign` that are not assigned yet.
     *
     * @api
     */
    public function postSave(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer
    {
        return $this->getBusinessFactory()
            ->createCustomerGroupCompanyBusinessUnitRelationWriter()
            ->saveCustomerGroupCompanyBusinessUnitRelations($customerGroupTransfer);
    }
}
