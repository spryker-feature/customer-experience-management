<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupViewExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CompanyBusinessUnitCustomerGroupViewExpanderPlugin extends AbstractPlugin implements CustomerGroupViewExpanderPluginInterface
{
    protected const string TEMPLATE_PATH = '@CustomerExperienceManagement/_partials/customer-group/company-business-unit-view-block.twig';

    protected const string TEMPLATE_VARIABLE_COMPANY_BUSINESS_UNIT_TABLE = 'companyBusinessUnitTable';

    /**
     * {@inheritDoc}
     * - Returns the name of the "Business Units" tab of the Add and Edit pages, so the panel takes the tab's position.
     *
     * @api
     */
    public function getBlockName(): string
    {
        return CompanyBusinessUnitCustomerGroupFormTabExpanderPlugin::TAB_NAME;
    }

    /**
     * {@inheritDoc}
     * - Returns the template of the "Business Units" panel.
     *
     * @api
     */
    public function getTemplatePath(): string
    {
        return static::TEMPLATE_PATH;
    }

    /**
     * {@inheritDoc}
     * - Returns the read-only table of the business units assigned to the customer group.
     * - Each row has an `Edit` button that opens the business unit's Back Office edit page.
     *
     * @api
     *
     * @return array<string, mixed>
     */
    public function getTemplateData(CustomerGroupTransfer $customerGroupTransfer): array
    {
        return [
            static::TEMPLATE_VARIABLE_COMPANY_BUSINESS_UNIT_TABLE => $this->getFactory()
                ->createAssignedCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroupOrFail())
                ->render(),
        ];
    }
}
