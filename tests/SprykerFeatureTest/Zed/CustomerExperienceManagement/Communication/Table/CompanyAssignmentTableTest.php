<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Table;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\Kernel\Exception\Controller\InvalidIdException;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyTableController;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AvailableCompanyTableController;
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
 * @group CompanyAssignmentTableTest
 * Add your own group annotations below this line
 */
class CompanyAssignmentTableTest extends Unit
{
    protected const int COLUMN_INDEX_NAME = 1;

    protected const int COLUMN_INDEX_CUSTOMER_COUNT = 2;

    protected const int COLUMN_INDEX_SELECT_CHECKBOX = 3;

    protected const int COLUMN_INDEX_ACTIONS = 3;

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
     * @uses \Spryker\Zed\CompanyGui\Communication\Controller\EditCompanyController::indexAction()
     */
    protected const string COMPANY_EDIT_URL_TEMPLATE = '/company-gui/edit-company/index?id-company=%d';

    protected const string ID_NOT_NUMERIC = 'not-an-id';

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

        $this->namePrefix = uniqid('cg-company-table-', false);
        $this->tester->setCustomerGroupPostSavePlugins();
    }

    public function testAvailableCompanyTableListsOnlyTheCompaniesNotAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $assignedCompanyTransfer = $this->haveCompanyNamed('assigned');
        $availableCompanyTransfer = $this->haveCompanyNamed('available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$assignedCompanyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createSelectableAvailableCompanyTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertSame([$availableCompanyTransfer->getIdCompanyOrFail()], $this->tester->getTableRowIds($rows));
    }

    public function testAvailableCompanyTableListsAllCompaniesWithoutCustomerGroup(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->haveCompanyNamed('first');
        $secondCompanyTransfer = $this->haveCompanyNamed('second');
        $this->haveCustomerGroupWithCompanies([$firstCompanyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyTable(null)->fetchData()['data'];

        // Assert
        $this->assertEqualsCanonicalizing(
            [$firstCompanyTransfer->getIdCompanyOrFail(), $secondCompanyTransfer->getIdCompanyOrFail()],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAssignedCompanyTableListsOnlyTheCompaniesAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $assignedCompanyTransfer = $this->haveCompanyNamed('assigned');
        $this->haveCompanyNamed('available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$assignedCompanyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertSame([$assignedCompanyTransfer->getIdCompanyOrFail()], $this->tester->getTableRowIds($rows));
    }

    public function testAssignedCompanyTableListsNothingWithoutCustomerGroup(): void
    {
        // Arrange
        $this->haveCustomerGroupWithCompanies([$this->haveCompanyNamed('assigned')]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $tableData = $this->tester->getCommunicationFactory()->createAssignedCompanyTable(null)->fetchData();

        // Assert
        $this->assertSame([], $tableData['data']);
    }

    public function testAssignedCompanyTableIsReadOnly(): void
    {
        // Arrange
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$this->haveCompanyNamed('assigned')]);
        $this->tester->setTableSearchRequest($this->namePrefix);
        $assignedCompanyTable = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyTable($customerGroupTransfer->getIdCustomerGroup());

        // Act
        $assignedCompanyTable->fetchData();

        // Assert
        $this->assertSame(
            sprintf(static::READ_ONLY_TABLE_URL_TEMPLATE, $customerGroupTransfer->getIdCustomerGroupOrFail()),
            $assignedCompanyTable->getConfiguration()->getUrl(),
        );
        $this->assertSame([], $assignedCompanyTable->getConfiguration()->getTableAttributes());
        $this->assertArrayNotHasKey(static::COLUMN_SELECT_CHECKBOX, $assignedCompanyTable->getConfiguration()->getHeader());
    }

    public function testAssignedCompanyTableLinksEachCompanyToItsEditPage(): void
    {
        // Arrange
        $companyTransfer = $this->haveCompanyNamed('assigned');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$companyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()
            ->createAssignedCompanyTable($customerGroupTransfer->getIdCustomerGroup())
            ->fetchData()['data'];

        // Assert
        $this->assertStringContainsString(
            sprintf(static::COMPANY_EDIT_URL_TEMPLATE, $companyTransfer->getIdCompanyOrFail()),
            $rows[0][static::COLUMN_INDEX_ACTIONS],
        );
    }

    public function testSelectableAssignedCompanyTableHasNoActionsColumn(): void
    {
        // Arrange
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$this->haveCompanyNamed('assigned')]);
        $this->tester->setTableSearchRequest($this->namePrefix);
        $selectableAssignedCompanyTable = $this->tester->getCommunicationFactory()
            ->createSelectableAssignedCompanyTable($customerGroupTransfer->getIdCustomerGroup());

        // Act
        $selectableAssignedCompanyTable->fetchData();

        // Assert
        $this->assertArrayNotHasKey(static::COLUMN_ACTIONS, $selectableAssignedCompanyTable->getConfiguration()->getHeader());
    }

    public function testSelectableAssignedCompanyTableAddsTheSelectionCheckbox(): void
    {
        // Arrange
        $companyTransfer = $this->haveCompanyNamed('assigned');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$companyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);
        $selectableAssignedCompanyTable = $this->tester->getCommunicationFactory()
            ->createSelectableAssignedCompanyTable($customerGroupTransfer->getIdCustomerGroup());

        // Act
        $rows = $selectableAssignedCompanyTable->fetchData()['data'];

        // Assert
        $this->assertSame(
            sprintf(static::SELECTABLE_TABLE_URL_TEMPLATE, $customerGroupTransfer->getIdCustomerGroupOrFail()),
            $selectableAssignedCompanyTable->getConfiguration()->getUrl(),
        );
        $this->assertStringContainsString(
            sprintf(static::CHECKBOX_VALUE_TEMPLATE, $companyTransfer->getIdCompanyOrFail()),
            $rows[0][static::COLUMN_INDEX_SELECT_CHECKBOX],
        );
    }

    public function testSelectableCompanyTablesShowTheSelectionCheckboxInTheLastColumn(): void
    {
        // Arrange
        $this->tester->setTableSearchRequest($this->namePrefix);
        $selectableAvailableCompanyTable = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyTable(null);
        $selectableAssignedCompanyTable = $this->tester->getCommunicationFactory()->createSelectableAssignedCompanyTable(null);

        // Act
        $selectableAvailableCompanyTable->fetchData();
        $selectableAssignedCompanyTable->fetchData();

        // Assert
        $this->assertSame(static::COLUMN_SELECT_CHECKBOX, array_key_last($selectableAvailableCompanyTable->getConfiguration()->getHeader()));
        $this->assertSame(static::COLUMN_SELECT_CHECKBOX, array_key_last($selectableAssignedCompanyTable->getConfiguration()->getHeader()));
    }

    public function testCustomerCountIncludesEveryCompanyUserOfTheCompany(): void
    {
        // Arrange
        $companyTransfer = $this->haveCompanyNamed('counted');
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer);
        $this->tester->haveCompanyUserInCompanyBusinessUnit($companyBusinessUnitTransfer);
        $this->tester->haveCompanyUserInCompanyBusinessUnit($companyBusinessUnitTransfer);
        $this->tester->haveCompanyUserInCompanyBusinessUnit($companyBusinessUnitTransfer, false);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyTable(null)->fetchData()['data'];

        // Assert
        $this->assertSame(3, (int)$rows[0][static::COLUMN_INDEX_CUSTOMER_COUNT]);
    }

    /**
     * Sorting by the customer count would count the company users of every company before the page is cut,
     * so the column is not sortable and a request to sort by it orders by ID, in the requested direction.
     */
    public function testAvailableCompanyTableOrdersByIdWhenAskedToSortByCustomerCount(): void
    {
        // Arrange
        $companyWithUserTransfer = $this->haveCompanyNamed('with-user');
        $companyWithoutUsersTransfer = $this->haveCompanyNamed('without-users');
        $this->tester->haveCompanyUserInCompanyBusinessUnit(
            $this->tester->haveCompanyBusinessUnitOfCompany($companyWithUserTransfer),
        );
        $this->tester->setSortedTableSearchRequest($this->namePrefix, static::COLUMN_INDEX_CUSTOMER_COUNT, static::SORT_DIRECTION_ASC);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyTable(null)->fetchData()['data'];

        // Assert
        $this->assertSame(
            [$companyWithUserTransfer->getIdCompanyOrFail(), $companyWithoutUsersTransfer->getIdCompanyOrFail()],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAvailableCompanyTableEscapesTheCompanyName(): void
    {
        // Arrange
        $this->haveCompanyNamed('<b>bold</b>');
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyTable(null)->fetchData()['data'];

        // Assert
        $this->assertStringNotContainsString('<b>', $rows[0][static::COLUMN_INDEX_NAME]);
        $this->assertStringContainsString('&lt;b&gt;bold&lt;/b&gt;', $rows[0][static::COLUMN_INDEX_NAME]);
    }

    public function testAvailableCompanyTableOrdersByIdWhenTheSortColumnIsUnknown(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->haveCompanyNamed('bravo');
        $secondCompanyTransfer = $this->haveCompanyNamed('alpha');
        $this->tester->setSortedTableSearchRequest($this->namePrefix, static::COLUMN_INDEX_UNKNOWN, static::SORT_DIRECTION_ASC);

        // Act
        $rows = $this->tester->getCommunicationFactory()->createSelectableAvailableCompanyTable(null)->fetchData()['data'];

        // Assert
        $this->assertSame(
            [$firstCompanyTransfer->getIdCompanyOrFail(), $secondCompanyTransfer->getIdCompanyOrFail()],
            $this->tester->getTableRowIds($rows),
        );
    }

    public function testAssignedCompanyTableControllerListsTheCompaniesOfTheRequestedCustomerGroup(): void
    {
        // Arrange
        $assignedCompanyTransfer = $this->haveCompanyNamed('assigned');
        $this->haveCompanyNamed('available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$assignedCompanyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $jsonResponse = (new AssignedCompanyTableController())->tableAction(
            $this->createTableRequest((string)$customerGroupTransfer->getIdCustomerGroupOrFail()),
        );

        // Assert
        $this->assertSame([$assignedCompanyTransfer->getIdCompanyOrFail()], $this->tester->getTableRowIds($this->getRows($jsonResponse)));
    }

    public function testAvailableCompanyTableControllerListsTheSelectableCompaniesNotAssignedToTheRequestedCustomerGroup(): void
    {
        // Arrange
        $assignedCompanyTransfer = $this->haveCompanyNamed('assigned');
        $availableCompanyTransfer = $this->haveCompanyNamed('available');
        $customerGroupTransfer = $this->haveCustomerGroupWithCompanies([$assignedCompanyTransfer]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $jsonResponse = (new AvailableCompanyTableController())->selectableTableAction(
            $this->createTableRequest((string)$customerGroupTransfer->getIdCustomerGroupOrFail()),
        );

        // Assert
        $rows = $this->getRows($jsonResponse);
        $this->assertSame([$availableCompanyTransfer->getIdCompanyOrFail()], $this->tester->getTableRowIds($rows));
        $this->assertStringContainsString(
            sprintf(static::CHECKBOX_VALUE_TEMPLATE, $availableCompanyTransfer->getIdCompanyOrFail()),
            $rows[0][static::COLUMN_INDEX_SELECT_CHECKBOX],
        );
    }

    public function testAssignedCompanyTableControllerListsNothingWithoutCustomerGroup(): void
    {
        // Arrange
        $this->haveCustomerGroupWithCompanies([$this->haveCompanyNamed('assigned')]);
        $this->tester->setTableSearchRequest($this->namePrefix);

        // Act
        $jsonResponse = (new AssignedCompanyTableController())->tableAction($this->createTableRequest(''));

        // Assert
        $this->assertSame([], $this->getRows($jsonResponse));
    }

    public function testAssignedCompanyTableControllerRejectsACustomerGroupIdThatIsNotNumeric(): void
    {
        // Expect
        $this->expectException(InvalidIdException::class);

        // Act
        (new AssignedCompanyTableController())->tableAction($this->createTableRequest(static::ID_NOT_NUMERIC));
    }

    protected function createTableRequest(string $idCustomerGroup): Request
    {
        return new Request([AbstractCustomerGroupAssignmentTable::PARAM_ID_CUSTOMER_GROUP => $idCustomerGroup]);
    }

    /**
     * @return list<list<mixed>>
     */
    protected function getRows(JsonResponse $jsonResponse): array
    {
        return json_decode((string)$jsonResponse->getContent(), true)['data'];
    }

    protected function haveCompanyNamed(string $nameSuffix): CompanyTransfer
    {
        return $this->tester->haveApprovedCompany([
            CompanyTransfer::NAME => sprintf('%s-%s', $this->namePrefix, $nameSuffix),
        ]);
    }

    /**
     * @param list<\Generated\Shared\Transfer\CompanyTransfer> $companyTransfers
     */
    protected function haveCustomerGroupWithCompanies(array $companyTransfers): CustomerGroupTransfer
    {
        $customerGroupToCompanyAssignmentTransfer = new CustomerGroupToCompanyAssignmentTransfer();

        foreach ($companyTransfers as $companyTransfer) {
            $customerGroupToCompanyAssignmentTransfer->addIdCompanyToAssign($companyTransfer->getIdCompanyOrFail());
        }

        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment($customerGroupToCompanyAssignmentTransfer),
        );

        return $customerGroupTransfer;
    }
}
