<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport\CustomerGroupDataImportPlugin;
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
 * @group CustomerGroupDataImportPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupDataImportPluginTest extends Unit
{
    protected const string IMPORT_TYPE = 'customer-group';

    /**
     * @var list<string>
     */
    protected const array HEADER = ['name', 'description'];

    protected const string DESCRIPTION = 'Imported customer group description';

    protected const string UPDATED_DESCRIPTION = 'Updated customer group description';

    protected const string DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    protected const string CUSTOMER_GROUP_NAME_PREFIX = 'cg-import-case-';

    protected CustomerExperienceManagementCommunicationTester $tester;

    /**
     * @disableTransaction
     */
    public function testImportCreatesTheCustomerGroups(): void
    {
        // Arrange
        $firstCustomerGroupName = $this->tester->haveCustomerGroupNameToImport();
        $secondCustomerGroupName = $this->tester->haveCustomerGroupNameToImport();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$firstCustomerGroupName, static::DESCRIPTION],
            [$secondCustomerGroupName, static::UPDATED_DESCRIPTION],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(2, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(static::DESCRIPTION, $this->tester->findCustomerGroupEntityByName($firstCustomerGroupName)?->getDescription());
        $this->assertSame(static::UPDATED_DESCRIPTION, $this->tester->findCustomerGroupEntityByName($secondCustomerGroupName)?->getDescription());
    }

    /**
     * @disableTransaction
     */
    public function testImportStoresAnEmptyDescriptionAsNoDescription(): void
    {
        // Arrange
        $customerGroupName = $this->tester->haveCustomerGroupNameToImport();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupName, ''],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertTrue($this->tester->hasCustomerGroupNamed($customerGroupName));
        $this->assertNull($this->tester->findCustomerGroupEntityByName($customerGroupName)?->getDescription());
    }

    /**
     * @disableTransaction
     */
    public function testImportUpdatesTheDescriptionAndKeepsTheMembersOfAnExistingCustomerGroup(): void
    {
        // Arrange
        $this->tester->setCustomerGroupPostSavePlugins();
        $customerTransfer = $this->tester->haveCustomer();
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup([CustomerGroupTransfer::DESCRIPTION => static::DESCRIPTION]);
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCustomerAssignment((new CustomerGroupToCustomerAssignmentTransfer())->addIdCustomerToAssign($customerTransfer->getIdCustomerOrFail()))
                ->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToAssign($companyTransfer->getIdCompanyOrFail())),
        );
        $createdAt = $this->tester->findCustomerGroupEntityByName($customerGroupTransfer->getNameOrFail())?->getCreatedAt(static::DATE_TIME_FORMAT);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), static::UPDATED_DESCRIPTION],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $customerGroupEntity = $this->tester->findCustomerGroupEntityByName($customerGroupTransfer->getNameOrFail());
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertNotNull($customerGroupEntity);
        $this->assertSame($customerGroupTransfer->getIdCustomerGroupOrFail(), $customerGroupEntity->getIdCustomerGroup());
        $this->assertSame(static::UPDATED_DESCRIPTION, $customerGroupEntity->getDescription());
        $this->assertNotNull($createdAt);
        $this->assertSame($createdAt, $customerGroupEntity->getCreatedAt(static::DATE_TIME_FORMAT));
        $this->assertSame([$customerTransfer->getIdCustomerOrFail()], $this->tester->getAssignedCustomerIds($customerGroupTransfer));
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportUpdatesTheCustomerGroupWhoseNameDiffersOnlyInLetterCase(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup([
            CustomerGroupTransfer::NAME => uniqid(static::CUSTOMER_GROUP_NAME_PREFIX, false),
            CustomerGroupTransfer::DESCRIPTION => static::DESCRIPTION,
        ]);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [mb_strtoupper($customerGroupTransfer->getNameOrFail()), static::UPDATED_DESCRIPTION],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $customerGroupEntity = $this->tester->findCustomerGroupEntityByName($customerGroupTransfer->getNameOrFail());
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(
            [$customerGroupTransfer->getIdCustomerGroupOrFail()],
            $this->tester->getCustomerGroupIdsByNameIgnoringCase($customerGroupTransfer->getNameOrFail()),
        );
        $this->assertSame($customerGroupTransfer->getNameOrFail(), $customerGroupEntity?->getName());
        $this->assertSame(static::UPDATED_DESCRIPTION, $customerGroupEntity?->getDescription());
    }

    /**
     * @disableTransaction
     */
    public function testImportOfTheSameFileTwiceSucceedsAndKeepsTheCustomerGroup(): void
    {
        // Arrange
        $customerGroupName = $this->tester->haveCustomerGroupNameToImport();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupName, static::DESCRIPTION],
        ]);
        (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);
        $idCustomerGroup = $this->tester->findCustomerGroupEntityByName($customerGroupName)?->getIdCustomerGroup();

        // Act
        $dataImporterReportTransfer = (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertNotNull($idCustomerGroup);
        $this->assertSame($idCustomerGroup, $this->tester->findCustomerGroupEntityByName($customerGroupName)?->getIdCustomerGroup());
    }

    /**
     * @disableTransaction
     */
    public function testImportRejectsARowWithAnEmptyCustomerGroupNameAndImportsTheValidRows(): void
    {
        // Arrange
        $customerGroupName = $this->tester->haveCustomerGroupNameToImport();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            ['', static::DESCRIPTION],
            [$customerGroupName, static::DESCRIPTION],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(1, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(
            [1 => 'The customer group name is empty.'],
            $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer),
        );
        $this->assertTrue($this->tester->hasCustomerGroupNamed($customerGroupName));
    }

    public function testGetImportTypeReturnsTheCustomerGroupImportType(): void
    {
        // Act
        $importType = (new CustomerGroupDataImportPlugin())->getImportType();

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
