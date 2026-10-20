<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Table;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyBusinessUnitTableController;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AvailableCompanyBusinessUnitTableController;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCustomerGroupAssignmentTable;
use SprykerFeatureTest\Zed\CustomerExperienceManagement\CustomerExperienceManagementCommunicationTester;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Table
 * @group CompanyBusinessUnitAssignmentTableTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitAssignmentTableTest extends Unit
{
    protected const int COLUMN_INDEX_COMPANY_NAME = 2;

    protected const int COLUMN_INDEX_CUSTOMER_COUNT = 3;

    protected const int COLUMN_INDEX_SELECT_CHECKBOX = 4;

    protected const int COLUMN_INDEX_ACTIONS = 4;

    protected const int COLUMN_INDEX_UNKNOWN = 99;

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\SelectableCustomerGroupAssignmentTableExpander::COL_SELECT_CHECKBOX
     */
    protected const string COLUMN_SELECT_CHECKBOX = 'select-checkbox';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCustomerGroupAssignmentTable::COL_ACTIONS
     */
    protected const string COLUMN_ACTIONS = 'actions';

    /**
     * @uses \Spryker\Zed\CompanyBusinessUnitGui\Communication\Controller\EditCompanyBusinessUnitController::indexAction()
     */
    protected const string COMPANY_BUSINESS_UNIT_EDIT_URL_TEMPLATE = '/company-business-unit-gui/edit-company-business-unit?id-company-business-unit=%d';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCustomerGroupAssignmentTable::URL_PATH_TABLE
     */
    protected const string READ_ONLY_TABLE_URL_TEMPLATE = 'table?id-customer-group=%d';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\SelectableCustomerGroupAssignmentTableExpander::URL_PATH_PREFIX_SELECTABLE
     */
    protected const string SELECTABLE_TABLE_URL_TEMPLATE = 'selectable-table?id-customer-group=%d';

    protected const string CHECKBOX_VALUE_TEMPLATE = 'value="%d"';

    protected const string SORT_DIRECTION_ASC = 'asc';

    protected CustomerExperienceManagementCommunicationTester $tester;

    protected string $namePrefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->namePrefix = uniqid('cg-business-unit-table-', false);
        $this->tester->setCustomerGroupPostSavePlugins();
    }

    public function testAvailableBusinessUnitTableListsOnlyTheBusinessUnitsNotAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $assignedCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'assigned');
        $availableCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([$assignedCompanyBusinessUnitTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createSelectableAvailableCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertSame(
            [$availableCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAssignedBusinessUnitTableListsOnlyTheBusinessUnitsAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $assignedCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'assigned');
        $this->haveCompanyBusinessUnitNamed($companyTransfer, 'available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([$assignedCompanyBusinessUnitTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertSame(
            [$assignedCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAssignedBusinessUnitTableListsNothingWithoutCustomerGroup(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $this->haveCustomerGroupWithCompanyBusinessUnits([$this->haveCompanyBusinessUnitNamed($companyTransfer, 'assigned')]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $tableData = $this->tester->getCommunicationFactory()->createAssignedCompanyBusinessUnitTable(null)->fetchData();

        // Assert
        $this->assertSame([], $tableData['data']);
    }

    public function testAssignedBusinessUnitTableIsReadOnly(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([
            $this->haveCompanyBusinessUnitNamed($companyTransfer, 'assigned'),
        ]);
        $this->tester->setTableSearchRequest($this->namePrefix);
        $assignedCompanyBusinessUnitTable = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroup());

        // Act
        $assignedCompanyBusinessUnitTable->fetchData();

        // Assert
        $this->assertSame(
            sprintf(static::READ_ONLY_TABLE_URL_TEMPLATE, $customerGroupTransfer->getIdCustomerGroupOrFail()),
            $assignedCompanyBusinessUnitTable->getConfiguration()->getUrl(),
        );
        $this->assertSame([], $assignedCompanyBusinessUnitTable->getConfiguration()->getTableAttributes());
        $this->assertArrayNotHasKey(static::COLUMN_SELECT_CHECKBOX, $assignedCompanyBusinessUnitTable->getConfiguration()->getHeader());
    }

    public function testAssignedBusinessUnitTableLinksEachBusinessUnitToItsEditPage(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($this->tester->haveApprovedCompany(), 'assigned');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([$companyBusinessUnitTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertStringContainsString(
            sprintf(static::COMPANY_BUSINESS_UNIT_EDIT_URL_TEMPLATE, $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()),
            $rows[0][static::COLUMN_INDEX_ACTIONS],
        );
    }

    public function testSelectableAssignedBusinessUnitTableHasNoActionsColumn(): void
    {
        // Arrange
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([
            $this->haveCompanyBusinessUnitNamed($this->tester->haveApprovedCompany(), 'assigned'),
        ]);
        $this->tester->setTableSearchRequest($this->namePrefix);
        $selectableAssignedCompanyBusinessUnitTable = $this->tester->getCommunicationFactory()
            ->createSelectableAssignedCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroup());

        // Act
        $selectableAssignedCompanyBusinessUnitTable->fetchData();

        // Assert
        $this->assertArrayNotHasKey(static::COLUMN_ACTIONS, $selectableAssignedCompanyBusinessUnitTable->getConfiguration()->getHeader());
    }

    public function testSelectableAssignedBusinessUnitTableAddsTheSelectionCheckbox(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($this->tester->haveApprovedCompany(), 'assigned');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([$companyBusinessUnitTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);
        $selectableAssignedCompanyBusinessUnitTable = $this->tester->getCommunicationFactory()
            ->createSelectableAssignedCompanyBusinessUnitTable($customerGroupTransfer->getIdCustomerGroup());

        // Act
        $rows = $selectableAssignedCompanyBusinessUnitTable->fetchData()['data'];

        // Assert
        $this->assertSame(
            sprintf(static::SELECTABLE_TABLE_URL_TEMPLATE, $customerGroupTransfer->getIdCustomerGroupOrFail()),
            $selectableAssignedCompanyBusinessUnitTable->getConfiguration()->getUrl(),
        );
        $this->assertStringContainsString(
            sprintf(static::CHECKBOX_VALUE_TEMPLATE, $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()),
            $rows[0][static::COLUMN_INDEX_SELECT_CHECKBOX],
        );
    }

    public function testSelectableBusinessUnitTablesShowTheSelectionCheckboxInTheLastColumn(): void
    {
        // Arrange
        $this->tester->setTableSearchRequest($this->namePrefix);
        $selectableAvailableBusinessUnitTable = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyBusinessUnitTable(null);
        $selectableAssignedBusinessUnitTable = $this->tester->getCommunicationFactory()->createSelectableAssignedCompanyBusinessUnitTable(null);

        // Act
        $selectableAvailableBusinessUnitTable->fetchData();
        $selectableAssignedBusinessUnitTable->fetchData();

        // Assert
        $this->assertSame(static::COLUMN_SELECT_CHECKBOX, array_key_last($selectableAvailableBusinessUnitTable->getConfiguration()->getHeader()));
        $this->assertSame(static::COLUMN_SELECT_CHECKBOX, array_key_last($selectableAssignedBusinessUnitTable->getConfiguration()->getHeader()));
    }

    public function testCustomerCountIncludesOnlyTheDirectCompanyUsersOfTheBusinessUnit(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $parentCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'parent');
        $childCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'child', [
            CompanyBusinessUnitTransfer::FK_PARENT_COMPANY_BUSINESS_UNIT => $parentCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
        ]);
        $this->tester->haveCompanyUserInCompanyBusinessUnit($parentCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyUserInCompanyBusinessUnit($childCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyUserInCompanyBusinessUnit($childCompanyBusinessUnitTransfer, false);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyBusinessUnitTable(null)->fetchData()['data'];

        // Assert
        $customerCountsByIdCompanyBusinessUnit = $this->getCustomerCountsByIdCompanyBusinessUnit($rows);
        $this->assertSame(1, $customerCountsByIdCompanyBusinessUnit[$parentCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()]);
        $this->assertSame(2, $customerCountsByIdCompanyBusinessUnit[$childCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()]);
    }

    public function testAvailableBusinessUnitTableFindsTheBusinessUnitsByTheNameOfTheirCompany(): void
    {
        // Arrange
        $companyName = sprintf('%s-company', $this->namePrefix);
        $companyTransfer = $this->tester->haveApprovedCompany([CompanyTransfer::NAME => $companyName]);
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer);
        $this->tester->setTableSearchRequest($companyName);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyBusinessUnitTable(null)->fetchData()['data'];

        // Assert
        $this->assertContains($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(), $this->tester->getTableRowIds($rows));
        $this->assertSame([$companyName], array_values(array_unique(array_column($rows, static::COLUMN_INDEX_COMPANY_NAME))));
    }

    public function testAvailableBusinessUnitTableSortsByCompanyName(): void
    {
        // Arrange
        $secondCompanyTransfer = $this->tester->haveApprovedCompany([CompanyTransfer::NAME => sprintf('%s-b', $this->namePrefix)]);
        $firstCompanyTransfer = $this->tester->haveApprovedCompany([CompanyTransfer::NAME => sprintf('%s-a', $this->namePrefix)]);
        $secondCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($secondCompanyTransfer, 'unit');
        $firstCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($firstCompanyTransfer, 'unit');
        $this->tester->setSortedTableSearchRequest(
            sprintf('%s-unit', $this->namePrefix),
            static::COLUMN_INDEX_COMPANY_NAME,
            static::SORT_DIRECTION_ASC,
        );

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyBusinessUnitTable(null)->fetchData()['data'];

        // Assert
        $this->assertSame(
            [
                $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            ],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAvailableBusinessUnitTableOrdersByIdWhenTheSortColumnIsUnknown(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $firstCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'bravo');
        $secondCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'alpha');
        $this->tester->setSortedTableSearchRequest($this->namePrefix, static::COLUMN_INDEX_UNKNOWN, static::SORT_DIRECTION_ASC);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyBusinessUnitTable(null)->fetchData()['data'];

        // Assert
        $this->assertSame(
            [
                $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            ],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAssignedBusinessUnitTableControllerListsTheBusinessUnitsOfTheRequestedCustomerGroup(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $assignedCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'assigned');
        $this->haveCompanyBusinessUnitNamed($companyTransfer, 'available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([$assignedCompanyBusinessUnitTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $jsonResponse = (new AssignedCompanyBusinessUnitTableController())->tableAction(
            $this->createTableRequest($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );

        // Assert
        $this->assertSame(
            [$assignedCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getTableRowIds($this->getRows($jsonResponse)),
        );
    }

    public function testAvailableBusinessUnitTableControllerListsTheSelectableBusinessUnitsNotAssignedToTheRequestedCustomerGroup(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $assignedCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'assigned');
        $availableCompanyBusinessUnitTransfer = $this->haveCompanyBusinessUnitNamed($companyTransfer, 'available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanyBusinessUnits([$assignedCompanyBusinessUnitTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $jsonResponse = (new AvailableCompanyBusinessUnitTableController())->selectableTableAction(
            $this->createTableRequest($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );

        // Assert
        $rows = $this->getRows($jsonResponse);
        $this->assertSame(
            [$availableCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->tester->getTableRowIds($rows),
        );
        $this->assertStringContainsString(
            sprintf(static::CHECKBOX_VALUE_TEMPLATE, $availableCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()),
            $rows[0][static::COLUMN_INDEX_SELECT_CHECKBOX],
        );
    }

    protected function createTableRequest(int $idCustomerGroup): Request
    {
        return new Request([AbstractCustomerGroupAssignmentTable::PARAM_ID_CUSTOMER_GROUP => (string)$idCustomerGroup]);
    }

    /**
     * @return list<list<mixed>>
     */
    protected function getRows(JsonResponse $jsonResponse): array
    {
        return json_decode((string)$jsonResponse->getContent(), true)['data'];
    }

    /**
     * @param array<string, mixed> $seed
     */
    protected function haveCompanyBusinessUnitNamed(
        CompanyTransfer $companyTransfer,
        string $nameSuffix,
        array $seed = []
    ): CompanyBusinessUnitTransfer {
        return $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer, $seed + [
            CompanyBusinessUnitTransfer::NAME => sprintf('%s-%s', $this->namePrefix, $nameSuffix),
        ]);
    }

    /**
     * @param list<\Generated\Shared\Transfer\CompanyBusinessUnitTransfer> $companyBusinessUnitTransfers
     */
    protected function haveCustomerGroupWithCompanyBusinessUnits(array $companyBusinessUnitTransfers): CustomerGroupTransfer
    {
        $customerGroupToCompanyBusinessUnitAssignmentTransfer = new CustomerGroupToCompanyBusinessUnitAssignmentTransfer();

        foreach ($companyBusinessUnitTransfers as $companyBusinessUnitTransfer) {
            $customerGroupToCompanyBusinessUnitAssignmentTransfer->addIdCompanyBusinessUnitToAssign(
                $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            );
        }

        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment($customerGroupToCompanyBusinessUnitAssignmentTransfer),
        );

        return $customerGroupTransfer;
    }

    /**
     * @param list<list<mixed>> $rows
     *
     * @return array<int, int>
     */
    protected function getCustomerCountsByIdCompanyBusinessUnit(array $rows): array
    {
        return array_combine(
            $this->tester->getTableRowIds($rows),
            array_map(fn (array $row): int => (int)$row[static::COLUMN_INDEX_CUSTOMER_COUNT], $rows),
        );
    }
}
