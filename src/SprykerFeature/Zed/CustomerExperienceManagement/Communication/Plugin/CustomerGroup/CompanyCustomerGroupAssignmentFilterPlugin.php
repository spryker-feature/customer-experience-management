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
class CompanyCustomerGroupAssignmentFilterPlugin extends AbstractPlugin implements CustomerGroupAssignmentFilterPluginInterface
{
    protected const string ASSIGNMENT_TYPE = 'company';

    protected const string LABEL = 'Company';

    /**
     * @uses \Spryker\Zed\CompanyGui\Communication\Controller\SuggestController::indexAction()
     */
    protected const string URL_COMPANY_SUGGEST = '/company-gui/suggest';

    /**
     * {@inheritDoc}
     * - Returns `company`.
     *
     * @api
     */
    public function getAssignmentType(): string
    {
        return static::ASSIGNMENT_TYPE;
    }

    /**
     * {@inheritDoc}
     * - Returns `Company`.
     *
     * @api
     */
    public function getLabel(): string
    {
        return static::LABEL;
    }

    /**
     * {@inheritDoc}
     * - Returns the company suggestion, which matches the company name.
     *
     * @api
     */
    public function getSuggestUrl(): string
    {
        return static::URL_COMPANY_SUGGEST;
    }

    /**
     * {@inheritDoc}
     * - Returns the label of the company suggestion: the company name and ID.
     *
     * @api
     */
    public function findAssignedEntityName(int $idAssignedEntity): ?string
    {
        return $this->getBusinessFactory()
            ->createAssignedEntityNameReader()
            ->findCompanyName($idAssignedEntity);
    }

    /**
     * {@inheritDoc}
     * - Adds the ID to `CustomerGroupConditions.companyIds`. The condition narrows the list only when
     *   `CompanyCustomerGroupQueryExpanderPlugin` is registered.
     *
     * @api
     */
    public function expandCustomerGroupConditions(
        int $idAssignedEntity,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): CustomerGroupConditionsTransfer {
        return $customerGroupConditionsTransfer->addIdCompany($idAssignedEntity);
    }
}
