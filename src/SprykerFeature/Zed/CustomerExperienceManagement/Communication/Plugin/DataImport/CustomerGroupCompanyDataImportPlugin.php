<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport;

use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use Generated\Shared\Transfer\DataImporterReportTransfer;
use Spryker\Zed\DataImport\Dependency\Plugin\DataImportPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory getBusinessFactory()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig getConfig()
 */
class CustomerGroupCompanyDataImportPlugin extends AbstractPlugin implements DataImportPluginInterface
{
    /**
     * {@inheritDoc}
     * - Reads `customer_group_name` and `company_key` from each row of `customer_group_to_company.csv`.
     * - Assigns the company to the customer group the way the Back Office does, a company that is already assigned is skipped.
     * - Matches the customer group name case-insensitively, when several customer groups match, uses the one with exactly that name, or rejects the row when none has it.
     * - Rejects a row with an empty or unknown customer group name or company key, or with a company key that matches several companies, and imports the other rows.
     *
     * @api
     */
    public function import(?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null): DataImporterReportTransfer
    {
        return $this->getBusinessFactory()
            ->getCustomerGroupCompanyDataImporter($dataImporterConfigurationTransfer)
            ->import($dataImporterConfigurationTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getImportType(): string
    {
        return CustomerExperienceManagementConfig::IMPORT_TYPE_CUSTOMER_GROUP_COMPANY;
    }
}
