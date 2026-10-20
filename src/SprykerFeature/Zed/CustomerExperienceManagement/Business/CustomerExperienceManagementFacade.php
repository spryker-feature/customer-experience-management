<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business;

use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory getFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface getRepository()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface getEntityManager()
 */
class CustomerExperienceManagementFacade extends AbstractFacade implements CustomerExperienceManagementFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getCustomerGroupCompanyCollection(
        CustomerGroupCompanyCriteriaTransfer $customerGroupCompanyCriteriaTransfer
    ): CustomerGroupCompanyCollectionTransfer {
        return $this->getRepository()->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getCustomerGroupCompanyBusinessUnitCollection(
        CustomerGroupCompanyBusinessUnitCriteriaTransfer $customerGroupCompanyBusinessUnitCriteriaTransfer
    ): CustomerGroupCompanyBusinessUnitCollectionTransfer {
        return $this->getRepository()->getCustomerGroupCompanyBusinessUnitCollection($customerGroupCompanyBusinessUnitCriteriaTransfer);
    }
}
