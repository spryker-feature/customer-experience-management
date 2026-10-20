<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use Propel\Runtime\Exception\PropelException;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\DataImport\CustomerGroupCompanyDataImportPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManager;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementPersistenceFactory;
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
 * @group CustomerGroupCompanyDataImportPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupCompanyDataImportPluginTest extends Unit
{
    protected const string IMPORT_TYPE = 'customer-group-company';

    /**
     * @var list<string>
     */
    protected const array HEADER = ['customer_group_name', 'company_key'];

    protected const string UNKNOWN_VALUE_PREFIX = 'cg-import-unknown-';

    protected const string COMPANY_NAME_PREFIX = 'cg-import-company-';

    protected const string CUSTOMER_GROUP_NAME_PREFIX = 'cg-import-case-';

    protected const int WRITE_CHUNK_SIZE = 2;

    protected const string REFUSAL_MESSAGE = 'The database refused the memberships.';

    protected CustomerExperienceManagementCommunicationTester $tester;

    /**
     * @disableTransaction
     */
    public function testImportAssignsTheCompaniesToTheCustomerGroups(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->tester->haveCompanyWithKey();
        $secondCompanyTransfer = $this->tester->haveCompanyWithKey();
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$firstCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
            [$secondCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(3, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(
            [$firstCompanyTransfer->getIdCompanyOrFail(), $secondCompanyTransfer->getIdCompanyOrFail()],
            $this->tester->getAssignedCompanyIds($firstCustomerGroupTransfer),
        );
        $this->assertSame([$firstCompanyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($secondCustomerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportAssignsTheCompanyToTheCustomerGroupWhoseNameDiffersOnlyInLetterCase(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup([
            CustomerGroupTransfer::NAME => uniqid(static::CUSTOMER_GROUP_NAME_PREFIX, false),
        ]);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [mb_strtoupper($customerGroupTransfer->getNameOrFail()), $companyTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportOfTheSameFileTwiceSucceedsAndCreatesNoDuplicates(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyTransfer->getKeyOrFail()],
        ]);
        (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportWritesEveryMembershipOfAFileLongerThanOneWriteChunk(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->tester->haveCompanyWithKey();
        $secondCompanyTransfer = $this->tester->haveCompanyWithKey();
        $thirdCompanyTransfer = $this->tester->haveCompanyWithKey();
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$firstCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
            [$secondCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $thirdCompanyTransfer->getKeyOrFail()],
        ]);
        $customerGroupCompanyDataImportPlugin = (new CustomerGroupCompanyDataImportPlugin())
            ->setBusinessFactory($this->tester->createBusinessFactoryWithRelationChunkSize(static::WRITE_CHUNK_SIZE));

        // Act
        $dataImporterReportTransfer = $customerGroupCompanyDataImportPlugin->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(5, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(
            [
                $firstCompanyTransfer->getIdCompanyOrFail(),
                $secondCompanyTransfer->getIdCompanyOrFail(),
                $thirdCompanyTransfer->getIdCompanyOrFail(),
            ],
            $this->tester->getAssignedCompanyIds($firstCustomerGroupTransfer),
        );
        $this->assertSame([$firstCompanyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($secondCustomerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportTakesTheRowsOfARefusedWriteChunkBackFromTheImportedDataSetCount(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->tester->haveCompanyWithKey();
        $secondCompanyTransfer = $this->tester->haveCompanyWithKey();
        $thirdCompanyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $refusedCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$customerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
            [$refusedCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$refusedCustomerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
            [$customerGroupTransfer->getNameOrFail(), $thirdCompanyTransfer->getKeyOrFail()],
        ]);
        $customerExperienceManagementBusinessFactory = $this->tester->createBusinessFactoryWithRelationChunkSize(static::WRITE_CHUNK_SIZE);
        $customerExperienceManagementBusinessFactory->setEntityManager(
            $this->createEntityManagerRefusingCustomerGroup($refusedCustomerGroupTransfer->getIdCustomerGroupOrFail()),
        );
        $customerGroupCompanyDataImportPlugin = (new CustomerGroupCompanyDataImportPlugin())
            ->setBusinessFactory($customerExperienceManagementBusinessFactory);

        // Act
        $dataImporterReportTransfer = $customerGroupCompanyDataImportPlugin->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(3, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame(
            [4 => sprintf('Saving the 2 memberships of the last 2 accepted rows failed, none of them is saved: %s', static::REFUSAL_MESSAGE)],
            $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer),
        );
        $this->assertSame(
            [
                $firstCompanyTransfer->getIdCompanyOrFail(),
                $secondCompanyTransfer->getIdCompanyOrFail(),
                $thirdCompanyTransfer->getIdCompanyOrFail(),
            ],
            $this->tester->getAssignedCompanyIds($customerGroupTransfer),
        );
        $this->assertSame([], $this->tester->getAssignedCompanyIds($refusedCustomerGroupTransfer));
    }

    /**
     * Each group already holds the company the other group gets, so a lookup that matched the groups and the
     * companies separately, rather than as pairs, would skip memberships that do not exist yet.
     *
     * @disableTransaction
     */
    public function testImportSkipsOnlyTheExistingMembershipsOfTheCustomerGroupsOfAWriteChunk(): void
    {
        // Arrange
        $this->tester->setCustomerGroupPostSavePlugins();
        $firstCompanyTransfer = $this->tester->haveCompanyWithKey();
        $secondCompanyTransfer = $this->tester->haveCompanyWithKey();
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->assignCompany($firstCustomerGroupTransfer, $firstCompanyTransfer);
        $this->assignCompany($secondCustomerGroupTransfer, $secondCompanyTransfer);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$firstCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$firstCustomerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
            [$secondCustomerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$secondCustomerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(4, $dataImporterReportTransfer->getImportedDataSetCount());
        $expectedCompanyIds = [$firstCompanyTransfer->getIdCompanyOrFail(), $secondCompanyTransfer->getIdCompanyOrFail()];
        $this->assertSame($expectedCompanyIds, $this->tester->getAssignedCompanyIds($firstCustomerGroupTransfer));
        $this->assertSame($expectedCompanyIds, $this->tester->getAssignedCompanyIds($secondCustomerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportKeepsACompanyAlreadyAssignedInTheBackOffice(): void
    {
        // Arrange
        $this->tester->setCustomerGroupPostSavePlugins();
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToAssign($companyTransfer->getIdCompanyOrFail()),
            ),
        );
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportRejectsTheRowsWithAnUnknownCustomerGroupOrCompanyAndImportsTheValidRows(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->tester->haveCompanyWithKey();
        $secondCompanyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $unknownCustomerGroupName = uniqid(static::UNKNOWN_VALUE_PREFIX, false);
        $unknownCompanyKey = uniqid(static::UNKNOWN_VALUE_PREFIX, true);
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $firstCompanyTransfer->getKeyOrFail()],
            [$unknownCustomerGroupName, $firstCompanyTransfer->getKeyOrFail()],
            [$customerGroupTransfer->getNameOrFail(), $unknownCompanyKey],
            [$customerGroupTransfer->getNameOrFail(), $secondCompanyTransfer->getKeyOrFail()],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(2, $dataImporterReportTransfer->getImportedDataSetCount());
        $this->assertSame([
            2 => sprintf('Customer group "%s" was not found.', $unknownCustomerGroupName),
            3 => sprintf('Company with key "%s" was not found.', $unknownCompanyKey),
        ], $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer));
        $this->assertSame(
            [$firstCompanyTransfer->getIdCompanyOrFail(), $secondCompanyTransfer->getIdCompanyOrFail()],
            $this->tester->getAssignedCompanyIds($customerGroupTransfer),
        );
        $this->assertFalse($this->tester->hasCustomerGroupNamed($unknownCustomerGroupName));
        $this->assertFalse($this->tester->hasCompanyWithKey($unknownCompanyKey));
    }

    /**
     * @disableTransaction
     */
    public function testImportRejectsACompanyKeyThatMatchesSeveralCompanies(): void
    {
        // Arrange
        $companyKey = uniqid(static::UNKNOWN_VALUE_PREFIX, true);
        $this->tester->haveCompanyWithKey([CompanyTransfer::KEY => $companyKey]);
        $this->tester->haveCompanyWithKey([CompanyTransfer::KEY => $companyKey]);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyKey],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame(
            [1 => sprintf('Company key "%s" matches more than one company.', $companyKey)],
            $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer),
        );
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * An empty company key is rejected before the lookup, so it never matches a company whose key is an empty string.
     *
     * @disableTransaction
     */
    public function testImportRejectsTheRowsWithAnEmptyCustomerGroupNameOrCompanyKey(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $this->tester->haveCompanyWithKey([CompanyTransfer::KEY => '']);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $dataImporterConfigurationTransfer = $this->haveImportConfiguration([
            ['', $companyTransfer->getKeyOrFail()],
            [$customerGroupTransfer->getNameOrFail(), ''],
        ]);

        // Act
        $dataImporterReportTransfer = (new CustomerGroupCompanyDataImportPlugin())->import($dataImporterConfigurationTransfer);

        // Assert
        $this->assertFalse($dataImporterReportTransfer->getIsSuccess());
        $this->assertSame([
            1 => 'The customer group name is empty.',
            2 => 'The company key is empty.',
        ], $this->tester->getDataImporterReportMessagesByDataSetNumber($dataImporterReportTransfer));
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @disableTransaction
     */
    public function testImportedCompanyIsListedInTheBackOfficeTableOfAssignedCompanies(): void
    {
        // Arrange
        $companyName = uniqid(static::COMPANY_NAME_PREFIX, false);
        $companyTransfer = $this->tester->haveCompanyWithKey([CompanyTransfer::NAME => $companyName]);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        (new CustomerGroupCompanyDataImportPlugin())->import($this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyTransfer->getKeyOrFail()],
        ]));
        $this->tester->setTableSearchRequest($companyName);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $this->tester->getTableRowIds($rows));
    }

    /**
     * @disableTransaction
     */
    public function testImportedCompanyIsRemovedByABackOfficeSave(): void
    {
        // Arrange
        $this->tester->setCustomerGroupPostSavePlugins();
        $companyTransfer = $this->tester->haveCompanyWithKey();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        (new CustomerGroupCompanyDataImportPlugin())->import($this->haveImportConfiguration([
            [$customerGroupTransfer->getNameOrFail(), $companyTransfer->getKeyOrFail()],
        ]));

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToDeAssign($companyTransfer->getIdCompanyOrFail()),
            ),
        );

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testGetImportTypeReturnsTheCustomerGroupCompanyImportType(): void
    {
        // Act
        $importType = (new CustomerGroupCompanyDataImportPlugin())->getImportType();

        // Assert
        $this->assertSame(static::IMPORT_TYPE, $importType);
    }

    protected function createEntityManagerRefusingCustomerGroup(int $idRefusedCustomerGroup): CustomerExperienceManagementEntityManager
    {
        $customerExperienceManagementEntityManager = new class ($idRefusedCustomerGroup, static::REFUSAL_MESSAGE) extends CustomerExperienceManagementEntityManager {
            public function __construct(protected int $idRefusedCustomerGroup, protected string $refusalMessage)
            {
            }

            /**
             * @param array<int, array<int>> $companyIdsByIdCustomerGroup
             *
             * @throws \Propel\Runtime\Exception\PropelException
             */
            public function createCustomerGroupToCompanyRelations(array $companyIdsByIdCustomerGroup): void
            {
                if (isset($companyIdsByIdCustomerGroup[$this->idRefusedCustomerGroup])) {
                    throw new PropelException($this->refusalMessage);
                }

                parent::createCustomerGroupToCompanyRelations($companyIdsByIdCustomerGroup);
            }
        };
        $customerExperienceManagementEntityManager->setFactory(new CustomerExperienceManagementPersistenceFactory());

        return $customerExperienceManagementEntityManager;
    }

    protected function assignCompany(CustomerGroupTransfer $customerGroupTransfer, CompanyTransfer $companyTransfer): void
    {
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToAssign($companyTransfer->getIdCompanyOrFail()),
            ),
        );
    }

    /**
     * @param list<list<string>> $rows
     */
    protected function haveImportConfiguration(array $rows): DataImporterConfigurationTransfer
    {
        return $this->tester->haveCsvDataImportConfiguration(static::IMPORT_TYPE, static::HEADER, $rows);
    }
}
