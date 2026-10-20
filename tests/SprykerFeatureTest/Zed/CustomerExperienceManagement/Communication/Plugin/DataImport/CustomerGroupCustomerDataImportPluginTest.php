<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport\CustomerGroupCustomerDataImportPlugin;
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
 * @group CustomerGroupCustomerDataImportPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupCustomerDataImportPluginTest extends Unit
{
    protected const string IMPORT_TYPE = 'customer-group-customer';

    /**
     * @var list<string>
     */
    protected const array HEADER = ['customer_group_name', 'customer_reference'];

    protected const string UNKNOWN_VALUE_PREFIX = 'cg-import-unknown-';

    protected CustomerExperienceManagementCommunicationTester $tester;

    /**
     * @disableTransaction
     */
    public function testImportAssignsTheCustomersToTheCustomerGroups(): void
    {
        // Arrange
        $firstCustomerTransfer = $this->tester->haveCustomer();
        $secondCustomerTransfer = $this->tester->haveCustomer();
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$firstCustomerGroupTransfer->getNameOrFail(), $firstCustomerTransfer->getCustomerReferenceOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $secondCustomerTransfer->getCustomerReferenceOrFail()],
            [$secondCustomerGroupTransfer->getNameOrFail(), $firstCustomerTransfer->getCustomerReferenceOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCustomerDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(3, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(
            [$firstCustomerTransfer->getIdCustomerOrFail(), $secondCustomerTransfer->getIdCustomerOrFail()],
            $this->tester->getAssignedCustomerIds($firstCustomerGroupTransfer),
        );
        $this->assertSame([$firstCustomerTransfer->getIdCustomerOrFail()], $this->tester->getAssignedCustomerIds($secondCustomerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportOfTheSameFileTwiceSucceedsAndCreatesNoDuplicates(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $customerTransfer->getCustomerReferenceOrFail()],
        ]);
        (new CustomerGroupCustomerDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCustomerDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([$customerTransfer->getIdCustomerOrFail()], $this->tester->getAssignedCustomerIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportKeepsACustomerAlreadyAssignedInTheBackOffice(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCustomerAssignment(
                (new CustomerGroupToCustomerAssignmentTransfer())->addIdCustomerToAssign($customerTransfer->getIdCustomerOrFail()),
            ),
        );
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $customerTransfer->getCustomerReferenceOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCustomerDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([$customerTransfer->getIdCustomerOrFail()], $this->tester->getAssignedCustomerIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportRejectsTheRowsWithAnUnknownCustomerGroupOrCustomerAndImportsTheValidRows(): void
    {
        // Arrange
        $firstCustomerTransfer = $this->tester->haveCustomer();
        $secondCustomerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $unknownCustomerGroupName = uniqid(static::UNKNOWN_VALUE_PREFIX, false);
        $unknownCustomerReference = uniqid(static::UNKNOWN_VALUE_PREFIX, true);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $firstCustomerTransfer->getCustomerReferenceOrFail()],
            [$unknownCustomerGroupName, $firstCustomerTransfer->getCustomerReferenceOrFail()],
            [$customerGroupTransfer->getNameOrFail(), $unknownCustomerReference],
            [$customerGroupTransfer->getNameOrFail(), $secondCustomerTransfer->getCustomerReferenceOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCustomerDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(2, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame([
            2 => sprintf('Customer group "%s" was not found.', $unknownCustomerGroupName),
            3 => sprintf('Customer with reference "%s" was not found.', $unknownCustomerReference),
        ], $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer));
        $this->assertSame(
            [$firstCustomerTransfer->getIdCustomerOrFail(), $secondCustomerTransfer->getIdCustomerOrFail()],
            $this->tester->getAssignedCustomerIds($customerGroupTransfer),
        );
        $this->assertFalse($this->tester->hasCustomerGroupNamed($unknownCustomerGroupName));
        $this->assertFalse($this->tester->hasCustomerWithReference($unknownCustomerReference));
    }

    /**
     * @disableTransaction
     */
    public function testImportRejectsTheRowsWithAnEmptyCustomerGroupNameOrCustomerReference(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            ['', $customerTransfer->getCustomerReferenceOrFail()],
            [$customerGroupTransfer->getNameOrFail(), ''],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCustomerDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([
            1 => 'The customer group name is empty.',
            2 => 'The customer reference is empty.',
        ], $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer));
        $this->assertSame([], $this->tester->getAssignedCustomerIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportedCustomerGroupIsAmongTheCustomerGroupsOfTheCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        (new CustomerGroupCustomerDataImportPlugin())->import($this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $customerTransfer->getCustomerReferenceOrFail()],
        ]));

        // Act
        $customerGroupCollectionTransfer = $this->tester->getCustomerGroupFacade()
            ->getCustomerGroupCollectionByIdCustomer($customerTransfer->getIdCustomerOrFail());

        // Assert
        $this->assertSame(
            [$customerGroupTransfer->getIdCustomerGroupOrFail()],
            array_map(
                fn (CustomerGroupTransfer $assignedCustomerGroupTransfer): int => $assignedCustomerGroupTransfer->getIdCustomerGroupOrFail(),
                $customerGroupCollectionTransfer->getGroups()->getArrayCopy(),
            ),
        );
    }

    /**
     * @disableTransaction
     */
    public function testImportedCustomerIsRemovedByABackOfficeSave(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        (new CustomerGroupCustomerDataImportPlugin())->import($this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $customerTransfer->getCustomerReferenceOrFail()],
        ]));

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCustomerAssignment(
                (new CustomerGroupToCustomerAssignmentTransfer())->addIdCustomerToDeAssign($customerTransfer->getIdCustomerOrFail()),
            ),
        );

        // Assert
        $this->assertSame([], $this->tester->getAssignedCustomerIds($customerGroupTransfer));
    }

    public function testGetImportTypeReturnsTheCustomerGroupCustomerImportType(): void
    {
        // Act
        $importType = (new CustomerGroupCustomerDataImportPlugin())->getImportType();

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
