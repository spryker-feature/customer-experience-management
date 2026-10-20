<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport\CustomerGroupCompanyBusinessUnitDataImportPlugin;
use SprykerFeatureTest\Zed\CustomerExperienceManagement\CustomerExperienceManagementCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Plugin
 * @group DataImport
 * @group CustomerGroupCompanyBusinessUnitDataImportPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupCompanyBusinessUnitDataImportPluginTest extends Unit
{
    protected const string IMPORT_TYPE = 'customer-group-company-business-unit';

    /**
     * @var list<string>
     */
    protected const array HEADER = ['customer_group_name', 'business_unit_key'];

    protected const string UNKNOWN_VALUE_PREFIX = 'cg-import-unknown-';

    protected CustomerExperienceManagementCommunicationTester $tester;

    /**
     * @disableTransaction
     */
    public function testImportAssignsBusinessUnitsOfDifferentCompaniesToTheCustomerGroups(): void
    {
        // Arrange
        $firstCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($this->tester->haveCompanyWithKey());
        $secondCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($this->tester->haveCompanyWithKey());
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$firstCustomerGroupTransfer->getNameOrFail(), $firstCompanyBusinessUnitTransfer->getKeyOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $secondCompanyBusinessUnitTransfer->getKeyOrFail()],
            [$secondCustomerGroupTransfer->getNameOrFail(), $firstCompanyBusinessUnitTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(3, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(
            [
                $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            ],
            $this->tester->getAssignedCompanyBusinessUnitIds($firstCustomerGroupTransfer),
        );
        $this->assertSame(
            [$firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getAssignedCompanyBusinessUnitIds($secondCustomerGroupTransfer),
        );
    }

    /**
     * @disableTransaction
     */
    public function testImportOfASecondFileAddsTheBusinessUnitToAnotherCustomerGroupAndKeepsTheFirst(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($this->tester->haveCompanyWithKey());
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($this->haveImportConfiguration([
            [$firstCustomerGroupTransfer->getNameOrFail(), $companyBusinessUnitTransfer->getKeyOrFail()],
        ]));

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($this->haveImportConfiguration([
            [$secondCustomerGroupTransfer->getNameOrFail(), $companyBusinessUnitTransfer->getKeyOrFail()],
        ]));

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(
            [$companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getAssignedCompanyBusinessUnitIds($firstCustomerGroupTransfer),
        );
        $this->assertSame(
            [$companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getAssignedCompanyBusinessUnitIds($secondCustomerGroupTransfer),
        );
    }

    /**
     * @disableTransaction
     */
    public function testImportOfTheSameFileTwiceSucceedsAndCreatesNoDuplicates(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($this->tester->haveCompanyWithKey());
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyBusinessUnitTransfer->getKeyOrFail()],
        ]);
        (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(
            [$companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer),
        );
    }

    /**
     * @disableTransaction
     */
    public function testImportOfABusinessUnitDoesNotAssignItsCompany(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($this->tester->haveCompanyWithKey());
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyBusinessUnitTransfer->getKeyOrFail()],
        ]);

        // Act
        (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportRejectsTheRowsWithAnUnknownCustomerGroupOrBusinessUnitAndImportsTheValidRows(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $firstCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($companyTransfer);
        $secondCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitWithKey($companyTransfer);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $unknownCustomerGroupName = uniqid(static::UNKNOWN_VALUE_PREFIX, false);
        $unknownCompanyBusinessUnitKey = uniqid(static::UNKNOWN_VALUE_PREFIX, true);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $firstCompanyBusinessUnitTransfer->getKeyOrFail()],
            [$unknownCustomerGroupName, $firstCompanyBusinessUnitTransfer->getKeyOrFail()],
            [$customerGroupTransfer->getNameOrFail(), $unknownCompanyBusinessUnitKey],
            [$customerGroupTransfer->getNameOrFail(), ''],
            [$customerGroupTransfer->getNameOrFail(), $secondCompanyBusinessUnitTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(2, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame([
            2 => sprintf('Customer group "%s" was not found.', $unknownCustomerGroupName),
            3 => sprintf('Business unit with key "%s" was not found.', $unknownCompanyBusinessUnitKey),
            4 => 'The business unit key is empty.',
        ], $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer));
        $this->assertSame(
            [
                $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            ],
            $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer),
        );
        $this->assertFalse($this->tester->hasCustomerGroupNamed($unknownCustomerGroupName));
        $this->assertFalse($this->tester->hasCompanyBusinessUnitWithKey($unknownCompanyBusinessUnitKey));
    }

    public function testGetImportTypeReturnsTheCustomerGroupCompanyBusinessUnitImportType(): void
    {
        // Act
        $importType = (new CustomerGroupCompanyBusinessUnitDataImportPlugin())->getImportType();

        // Assert
        $this->assertSame(static::IMPORT_TYPE, $importType);
    }

    /**
     * @param list<list<string>> $rows
     */
    protected function haveImportConfiguration(array $rows): DataImporterConfigurationTransfer
    {
        return $this->tester->haveCsvDataImportConfiguration(static::IMPORT_TYPE, static::HEADER, $rows);
    }
}
