<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupFormExpanderPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupFormExpanderPlugin;
use SprykerFeatureTest\Zed\CustomerExperienceManagement\CustomerExperienceManagementCommunicationTester;
use Symfony\Component\Form\FormInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Plugin
 * @group CustomerGroup
 * @group CustomerGroupFormExpanderPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupFormExpanderPluginTest extends Unit
{
    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Form\CustomerGroupForm::FIELD_NAME
     */
    protected const string FIELD_NAME = 'name';

    protected const int ID_NON_EXISTENT = 2147483647;

    protected const string MESSAGE_INVALID_IDS = 'The selected IDs are not valid.';

    protected const string MESSAGE_UNKNOWN_COMPANY_IDS = 'Unknown company IDs: %d.';

    protected const string MESSAGE_UNKNOWN_COMPANY_BUSINESS_UNIT_IDS = 'Unknown business unit IDs: %d.';

    protected const string MESSAGE_COMPANY_IDS_TO_ASSIGN_AND_DE_ASSIGN = 'Company IDs selected to be both assigned and de-assigned: %d.';

    protected const string MESSAGE_ALREADY_ASSIGNED_COMPANY_IDS = 'Company IDs already assigned to the customer group: %d.';

    protected const string MESSAGE_NOT_ASSIGNED_COMPANY_BUSINESS_UNIT_IDS = 'Business unit IDs not assigned to the customer group: %d.';

    protected const string MESSAGE_TOO_MANY_IDS = 'At most 1000 entries can be selected at a time.';

    protected const int IDS_LIMIT = 1000;

    protected CustomerExperienceManagementCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER, [
            new CompanyCustomerGroupFormExpanderPlugin(),
            new CompanyBusinessUnitCustomerGroupFormExpanderPlugin(),
        ]);
    }

    public function testSubmittedIdsAreMappedToTheCompanyAndBusinessUnitAssignments(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyTransfer = $this->tester->haveApprovedCompany();
        $companyToDeAssignTransfer = $this->tester->haveApprovedCompany();
        $firstCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer);
        $secondCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyToDeAssignTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $firstCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $secondCompanyBusinessUnitTransfer);
        $form = $this->createCustomerGroupForm($customerGroupTransfer);

        // Act
        $form->submit($this->createFormData(
            [
                CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => (string)$companyTransfer->getIdCompanyOrFail(),
                CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_DE_ASSIGN => (string)$companyToDeAssignTransfer->getIdCompanyOrFail(),
            ],
            [
                CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_ASSIGN => '',
                CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN => implode(',', [
                    $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                ]),
            ],
        ), false);

        // Assert
        $this->assertTrue($form->isValid());

        /** @var \Generated\Shared\Transfer\CustomerGroupTransfer $submittedCustomerGroupTransfer */
        $submittedCustomerGroupTransfer = $form->getData();
        $customerGroupToCompanyAssignmentTransfer = $submittedCustomerGroupTransfer->getCompanyAssignmentOrFail();
        $customerGroupToCompanyBusinessUnitAssignmentTransfer = $submittedCustomerGroupTransfer->getCompanyBusinessUnitAssignmentOrFail();
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $customerGroupToCompanyAssignmentTransfer->getIdsCompanyToAssign());
        $this->assertSame([$companyToDeAssignTransfer->getIdCompanyOrFail()], $customerGroupToCompanyAssignmentTransfer->getIdsCompanyToDeAssign());
        $this->assertSame([], $customerGroupToCompanyBusinessUnitAssignmentTransfer->getIdsCompanyBusinessUnitToAssign());
        $this->assertSame(
            [
                $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            ],
            $customerGroupToCompanyBusinessUnitAssignmentTransfer->getIdsCompanyBusinessUnitToDeAssign(),
        );
    }

    public function testSubmittedValueThatIsNotAListOfIdsInvalidatesTheForm(): void
    {
        // Arrange
        $form = $this->createCustomerGroupForm();

        // Act
        $form->submit($this->createFormData([
            CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => '5,abc',
        ], []), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(static::MESSAGE_INVALID_IDS, $this->getFirstErrorMessage($form, CustomerGroupTransfer::COMPANY_ASSIGNMENT));
    }

    public function testCompanyIdThatDoesNotExistInvalidatesTheForm(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $form = $this->createCustomerGroupForm();

        // Act
        $form->submit($this->createFormData([
            CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => implode(',', [$idCompany, static::ID_NON_EXISTENT]),
        ], []), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            sprintf(static::MESSAGE_UNKNOWN_COMPANY_IDS, static::ID_NON_EXISTENT),
            $this->getFirstErrorMessage($form, CustomerGroupTransfer::COMPANY_ASSIGNMENT),
        );
    }

    public function testBusinessUnitIdThatDoesNotExistInvalidatesTheForm(): void
    {
        // Arrange
        $form = $this->createCustomerGroupForm();

        // Act
        $form->submit($this->createFormData([], [
            CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN => (string)static::ID_NON_EXISTENT,
        ]), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            sprintf(static::MESSAGE_UNKNOWN_COMPANY_BUSINESS_UNIT_IDS, static::ID_NON_EXISTENT),
            $this->getFirstErrorMessage($form, CustomerGroupTransfer::COMPANY_BUSINESS_UNIT_ASSIGNMENT),
        );
    }

    public function testEmptySelectionsAreValid(): void
    {
        // Arrange
        $form = $this->createCustomerGroupForm();

        // Act
        $form->submit($this->createFormData(
            [
                CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => '',
                CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_DE_ASSIGN => '',
            ],
            [
                CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_ASSIGN => '',
                CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN => '',
            ],
        ), false);

        // Assert
        $this->assertTrue($form->isValid());
    }

    public function testCompanyIdSelectedToBeBothAssignedAndDeAssignedInvalidatesTheForm(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $form = $this->createCustomerGroupForm($this->tester->haveCustomerGroup());

        // Act
        $form->submit($this->createFormData([
            CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => (string)$idCompany,
            CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_DE_ASSIGN => (string)$idCompany,
        ], []), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            [sprintf(static::MESSAGE_COMPANY_IDS_TO_ASSIGN_AND_DE_ASSIGN, $idCompany)],
            $this->getErrorMessages($form, CustomerGroupTransfer::COMPANY_ASSIGNMENT),
        );
    }

    public function testCompanyAlreadyAssignedToTheCustomerGroupInvalidatesTheForm(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyTransfer = $this->tester->haveApprovedCompany();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
        $form = $this->createCustomerGroupForm($customerGroupTransfer);

        // Act
        $form->submit($this->createFormData([
            CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => (string)$companyTransfer->getIdCompanyOrFail(),
        ], []), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            [sprintf(static::MESSAGE_ALREADY_ASSIGNED_COMPANY_IDS, $companyTransfer->getIdCompanyOrFail())],
            $this->getErrorMessages($form, CustomerGroupTransfer::COMPANY_ASSIGNMENT),
        );
    }

    public function testBusinessUnitNotAssignedToTheCustomerGroupInvalidatesTheForm(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitOfCompany($this->tester->haveApprovedCompany());
        $form = $this->createCustomerGroupForm($this->tester->haveCustomerGroup());

        // Act
        $form->submit($this->createFormData([], [
            CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN => (string)$companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
        ]), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            [sprintf(static::MESSAGE_NOT_ASSIGNED_COMPANY_BUSINESS_UNIT_IDS, $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail())],
            $this->getErrorMessages($form, CustomerGroupTransfer::COMPANY_BUSINESS_UNIT_ASSIGNMENT),
        );
    }

    public function testMoreIdsThanTheLimitInvalidateTheForm(): void
    {
        // Arrange
        $form = $this->createCustomerGroupForm();

        // Act
        $form->submit($this->createFormData([
            CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => implode(',', range(1, static::IDS_LIMIT + 1)),
        ], []), false);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(static::MESSAGE_TOO_MANY_IDS, $this->getFirstErrorMessage($form, CustomerGroupTransfer::COMPANY_ASSIGNMENT));
    }

    public function testIdsFieldsAreRenderedEmptyBeforeASubmit(): void
    {
        // Arrange
        $this->tester->setTableRequest();
        $form = $this->createCustomerGroupForm();

        // Act
        $formView = $form->createView();

        // Assert
        $this->assertSame(
            '',
            $formView[CustomerGroupTransfer::COMPANY_ASSIGNMENT][CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN]->vars['value'],
        );
        $this->assertSame(
            '',
            $formView[CustomerGroupTransfer::COMPANY_BUSINESS_UNIT_ASSIGNMENT][CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN]->vars['value'],
        );
    }

    public function testIdsFieldsKeepTheSubmittedIdsAfterASubmitThatFailsValidation(): void
    {
        // Arrange
        $this->tester->setTableRequest();
        $form = $this->createCustomerGroupForm();
        $form->submit($this->createFormData(
            [CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN => (string)static::ID_NON_EXISTENT],
            [CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN => (string)static::ID_NON_EXISTENT],
        ), false);

        // Act
        $formView = $form->createView();

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            (string)static::ID_NON_EXISTENT,
            $formView[CustomerGroupTransfer::COMPANY_ASSIGNMENT][CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN]->vars['value'],
        );
        $this->assertSame(
            (string)static::ID_NON_EXISTENT,
            $formView[CustomerGroupTransfer::COMPANY_BUSINESS_UNIT_ASSIGNMENT][CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN]->vars['value'],
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    protected function createCustomerGroupForm(?CustomerGroupTransfer $customerGroupTransfer = null): FormInterface
    {
        return (new CustomerGroupCommunicationFactory())->createCustomerGroupForm(
            ($customerGroupTransfer ?? new CustomerGroupTransfer())->setCustomerAssignment(new CustomerGroupToCustomerAssignmentTransfer()),
        );
    }

    /**
     * @param array<string, string> $companyAssignmentData
     * @param array<string, string> $companyBusinessUnitAssignmentData
     *
     * @return array<string, mixed>
     */
    protected function createFormData(array $companyAssignmentData, array $companyBusinessUnitAssignmentData): array
    {
        return [
            static::FIELD_NAME => uniqid('customer-group-', false),
            CustomerGroupTransfer::COMPANY_ASSIGNMENT => $companyAssignmentData,
            CustomerGroupTransfer::COMPANY_BUSINESS_UNIT_ASSIGNMENT => $companyBusinessUnitAssignmentData,
        ];
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    protected function getFirstErrorMessage(FormInterface $form, string $assignmentFieldName): string
    {
        return $form->get($assignmentFieldName)->getErrors(true)->current()->getMessage();
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     *
     * @return list<string>
     */
    protected function getErrorMessages(FormInterface $form, string $assignmentFieldName): array
    {
        $errorMessages = [];

        foreach ($form->get($assignmentFieldName)->getErrors(true) as $formError) {
            $errorMessages[] = $formError->getMessage();
        }

        return $errorMessages;
    }
}
