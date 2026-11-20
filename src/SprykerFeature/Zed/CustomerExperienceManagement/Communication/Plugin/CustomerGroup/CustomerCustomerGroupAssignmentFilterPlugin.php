<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory getBusinessFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig getConfig()
 */
class CustomerCustomerGroupAssignmentFilterPlugin extends AbstractPlugin implements CustomerGroupAssignmentFilterPluginInterface
{
    protected const string ASSIGNMENT_TYPE = 'customer';

    protected const string LABEL = 'Customer';

    /**
     * @uses \Spryker\Zed\Customer\Communication\Controller\SuggestController::indexAction()
     */
    protected const string URL_CUSTOMER_SUGGEST = '/customer/suggest';

    /**
     * {@inheritDoc}
     * - Returns `customer`.
     *
     * @api
     */
    public function getAssignmentType(): string
    {
        return static::ASSIGNMENT_TYPE;
    }

    /**
     * {@inheritDoc}
     * - Returns `Customer`.
     *
     * @api
     */
    public function getLabel(): string
    {
        return static::LABEL;
    }

    /**
     * {@inheritDoc}
     * - Returns the Back Office customer suggestion, which matches the email, first name, last name or customer reference.
     *
     * @api
     */
    public function getSuggestUrl(): string
    {
        return static::URL_CUSTOMER_SUGGEST;
    }

    /**
     * {@inheritDoc}
     * - Returns the label of the customer suggestion: the email, first and last name and the customer reference.
     *
     * @api
     */
    public function findAssignedEntityName(int $idAssignedEntity): ?string
    {
        return $this->getBusinessFactory()
            ->createAssignedEntityNameReader()
            ->findCustomerName($idAssignedEntity);
    }

    /**
     * {@inheritDoc}
     * - Adds the customer ID to `CustomerGroupConditions.customerIds`, which narrows to the groups the customer is
     *   directly assigned to, not the groups of their company or business unit.
     *
     * @api
     */
    public function expandCustomerGroupConditions(
        int $idAssignedEntity,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): CustomerGroupConditionsTransfer {
        return $customerGroupConditionsTransfer->addIdCustomer($idAssignedEntity);
    }
}
