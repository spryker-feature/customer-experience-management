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
class CompanyCustomerGroupQueryExpanderPlugin extends AbstractPlugin implements CustomerGroupQueryExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Applies when `CustomerGroupConditions.companyIds` or `CustomerGroupConditions.companyUuids` is set.
     * - Adds an inner join on the company memberships of the groups, narrowed to the given companies, a group
     *   matches when it is assigned to any of them.
     * - Narrows to the groups the companies themselves are assigned to, not the groups of their business units.
     *
     * @api
     */
    public function expand(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer {
        return $this->getBusinessFactory()
            ->createCustomerGroupQueryJoinExpander()
            ->expandWithCompanyConditions($customerGroupCriteriaTransfer, $queryJoinCollectionTransfer);
    }
}
