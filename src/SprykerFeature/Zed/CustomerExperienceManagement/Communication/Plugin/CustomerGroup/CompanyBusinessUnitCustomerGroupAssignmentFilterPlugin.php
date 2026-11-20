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
class CompanyBusinessUnitCustomerGroupAssignmentFilterPlugin extends AbstractPlugin implements CustomerGroupAssignmentFilterPluginInterface
{
    protected const string ASSIGNMENT_TYPE = 'company-business-unit';

    protected const string LABEL = 'Business Unit';

    /**
     * @uses \Spryker\Zed\CompanyBusinessUnitGui\Communication\Controller\SuggestController::allCompaniesAction()
     */
    protected const string URL_COMPANY_BUSINESS_UNIT_SUGGEST = '/company-business-unit-gui/suggest/all-companies';

    /**
     * {@inheritDoc}
     * - Returns `company-business-unit`.
     *
     * @api
     */
    public function getAssignmentType(): string
    {
        return static::ASSIGNMENT_TYPE;
    }

    /**
     * {@inheritDoc}
     * - Returns `Business Unit`.
     *
     * @api
     */
    public function getLabel(): string
    {
        return static::LABEL;
    }

    /**
     * {@inheritDoc}
     * - Returns the business unit suggestion across all companies, which matches the business unit name.
     *
     * @api
     */
    public function getSuggestUrl(): string
    {
        return static::URL_COMPANY_BUSINESS_UNIT_SUGGEST;
    }

    /**
     * {@inheritDoc}
     * - Returns the label of the business unit suggestion: the business unit name and ID and the name of its company.
     *
     * @api
     */
    public function findAssignedEntityName(int $idAssignedEntity): ?string
    {
        return $this->getBusinessFactory()
            ->createAssignedEntityNameReader()
            ->findCompanyBusinessUnitName($idAssignedEntity);
    }

    /**
     * {@inheritDoc}
     * - Adds the ID to `CustomerGroupConditions.companyBusinessUnitIds`. The condition narrows the list only when
     *   `CompanyBusinessUnitCustomerGroupQueryExpanderPlugin` is registered.
     *
     * @api
     */
    public function expandCustomerGroupConditions(
        int $idAssignedEntity,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): CustomerGroupConditionsTransfer {
        return $customerGroupConditionsTransfer->addIdCompanyBusinessUnit($idAssignedEntity);
    }
}
