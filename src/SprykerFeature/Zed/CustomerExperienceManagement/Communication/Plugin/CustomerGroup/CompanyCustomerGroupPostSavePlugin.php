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
class CompanyCustomerGroupPostSavePlugin extends AbstractPlugin implements CustomerGroupPostSavePluginInterface
{
    /**
     * {@inheritDoc}
     * - Applies `CustomerGroupTransfer.companyAssignment` when it is set.
     * - When `CustomerGroupTransfer.companyAssignment.isDeAssignAll` is true, removes with one delete every company membership of the customer group except those of the companies in `idsCompanyToAssign`, which are kept as they are; `idsCompanyToDeAssign` is not used then.
     * - Otherwise removes the companies listed in `CustomerGroupTransfer.companyAssignment.idsCompanyToDeAssign` from the customer group.
     * - Assigns the companies listed in `CustomerGroupTransfer.companyAssignment.idsCompanyToAssign` that are not assigned yet.
     *
     * @api
     */
    public function postSave(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer
    {
        return $this->getBusinessFactory()
            ->createCustomerGroupCompanyRelationWriter()
            ->saveCustomerGroupCompanyRelations($customerGroupTransfer);
    }
}
