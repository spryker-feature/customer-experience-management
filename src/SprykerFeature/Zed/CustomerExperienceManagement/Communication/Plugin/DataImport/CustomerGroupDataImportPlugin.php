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
class CustomerGroupDataImportPlugin extends AbstractPlugin implements DataImportPluginInterface
{
    /**
     * {@inheritDoc}
     * - Reads `name` and `description` from each row of `customer_group.csv`.
     * - Creates the customer group when no customer group has that name, compared case-insensitively.
     * - When several customer groups have that name ignoring case, uses the one with exactly that name, or rejects the row when none has it.
     * - Otherwise replaces the description of the existing customer group and keeps its name, customers, companies and business units.
     * - Stores an empty description as no description.
     *
     * @api
     */
    public function import(?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null): DataImporterReportTransfer
    {
        return $this->getBusinessFactory()
            ->getCustomerGroupDataImporter($dataImporterConfigurationTransfer)
            ->import($dataImporterConfigurationTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getImportType(): string
    {
        return CustomerExperienceManagementConfig::IMPORT_TYPE_CUSTOMER_GROUP;
    }
}
