<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupQueryExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig getConfig()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory getBusinessFactory()
 */
class CompanyBusinessUnitCustomerGroupQueryExpanderPlugin extends AbstractPlugin implements CustomerGroupQueryExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Applies when `CustomerGroupConditions.companyBusinessUnitIds` or `CustomerGroupConditions.companyBusinessUnitUuids` is set.
     * - Adds an inner join on the business unit memberships of the groups, narrowed to the given business units, a
     *   group matches when it is assigned to any of them.
     * - Narrows to the groups the business units themselves are assigned to, not the groups of their company.
     *
     * @api
     */
    public function expand(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer {
        return $this->getBusinessFactory()
            ->createCustomerGroupQueryJoinExpander()
            ->expandWithCompanyBusinessUnitConditions($customerGroupCriteriaTransfer, $queryJoinCollectionTransfer);
    }
}
