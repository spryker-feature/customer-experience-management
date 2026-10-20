<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\CompanyBuilder;
use Generated\Shared\DataBuilder\CompanyBusinessUnitBuilder;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use Generated\Shared\Transfer\DataImporterReaderConfigurationTransfer;
use Orm\Zed\Company\Persistence\SpyCompany;
use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnit;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;

class CustomerGroupDataImportHelper extends Module
{
    use DataCleanupHelperTrait;

    protected const string COMPANY_STATUS_APPROVED = 'approved';

    protected const string COMPANY_KEY_PREFIX = 'cg-import-company-';

    protected const string CUSTOMER_GROUP_NAME_PREFIX = 'cg-import-group-';

    protected const string IMPORT_FILE_NAME_PREFIX = 'cg-data-import-';

    protected const string IMPORT_FILE_NAME_TEMPLATE = '%s/%s.csv';

    protected const string CSV_DELIMITER = ',';

    protected const string CSV_ENCLOSURE = '"';

    protected const string CSV_ESCAPE = '\\';

    /**
     * The company is saved without the company plugins, which would add a default business unit and roles that block
     * its deletion in the cleanup.
     *
     * @param array<string, mixed> $seed
     */
    public function haveCompanyWithKey(array $seed = []): CompanyTransfer
    {
        $companyTransfer = (new CompanyBuilder($seed + [
            CompanyTransfer::KEY => uniqid(static::COMPANY_KEY_PREFIX, true),
        ]))->build();

        $companyEntity = (new SpyCompany())
            ->setName($companyTransfer->getNameOrFail())
            ->setKey($companyTransfer->getKeyOrFail())
            ->setStatus(static::COMPANY_STATUS_APPROVED)
            ->setIsActive(true);
        $companyEntity->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($companyEntity): void {
            SpyCompanyQuery::create()->filterByIdCompany($companyEntity->getIdCompany())->delete();
        });

        return (new CompanyTransfer())->fromArray($companyEntity->toArray(), true);
    }

    /**
     * @param array<string, mixed> $seed
     */
    public function haveCompanyBusinessUnitWithKey(CompanyTransfer $companyTransfer, array $seed = []): CompanyBusinessUnitTransfer
    {
        $companyBusinessUnitTransfer = (new CompanyBusinessUnitBuilder($seed))->build();

        $companyBusinessUnitEntity = (new SpyCompanyBusinessUnit())
            ->setFkCompany($companyTransfer->getIdCompanyOrFail())
            ->setName($companyBusinessUnitTransfer->getNameOrFail())
            ->setKey($companyBusinessUnitTransfer->getKeyOrFail());
        $companyBusinessUnitEntity->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($companyBusinessUnitEntity): void {
            SpyCompanyBusinessUnitQuery::create()
                ->filterByIdCompanyBusinessUnit($companyBusinessUnitEntity->getIdCompanyBusinessUnit())
                ->delete();
        });

        return (new CompanyBusinessUnitTransfer())->fromArray($companyBusinessUnitEntity->toArray(), true);
    }

    public function haveCustomerGroupNameToImport(): string
    {
        $customerGroupName = uniqid(static::CUSTOMER_GROUP_NAME_PREFIX, false);

        $this->getDataCleanupHelper()->_addCleanup(function () use ($customerGroupName): void {
            SpyCustomerGroupQuery::create()->filterByName($customerGroupName)->delete();
        });

        return $customerGroupName;
    }

    /**
     * @param list<string> $header
     * @param list<list<string>> $rows
     */
    public function haveCsvDataImportConfiguration(
        string $importType,
        array $header,
        array $rows
    ): DataImporterConfigurationTransfer {
        $filePath = sprintf(static::IMPORT_FILE_NAME_TEMPLATE, sys_get_temp_dir(), uniqid(static::IMPORT_FILE_NAME_PREFIX, true));
        $fileHandle = fopen($filePath, 'w');

        foreach ([$header, ...$rows] as $row) {
            fputcsv($fileHandle, $row, static::CSV_DELIMITER, static::CSV_ENCLOSURE, static::CSV_ESCAPE);
        }

        fclose($fileHandle);

        $this->getDataCleanupHelper()->_addCleanup(function () use ($filePath): void {
            unlink($filePath);
        });

        return (new DataImporterConfigurationTransfer())
            ->setImportType($importType)
            ->setReaderConfiguration((new DataImporterReaderConfigurationTransfer())->setFileName($filePath));
    }
}
