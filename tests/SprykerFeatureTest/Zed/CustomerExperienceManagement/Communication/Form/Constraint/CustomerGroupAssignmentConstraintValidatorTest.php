<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Form\Constraint;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\Constraint\CompanyAssignmentConstraint;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface;
use Symfony\Component\Validator\Validation;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Form
 * @group Constraint
 * @group CustomerGroupAssignmentConstraintValidatorTest
 * Add your own group annotations below this line
 */
class CustomerGroupAssignmentConstraintValidatorTest extends Unit
{
    protected const int ID_CUSTOMER_GROUP = 7;

    protected const string MESSAGE_UNKNOWN_IDS = 'Unknown company IDs: %s.';

    protected const string MESSAGE_IDS_TO_ASSIGN_AND_DE_ASSIGN = 'Company IDs selected to be both assigned and de-assigned: %s.';

    protected const string MESSAGE_ALREADY_ASSIGNED_IDS = 'Company IDs already assigned to the customer group: %s.';

    protected const string MESSAGE_NOT_ASSIGNED_IDS = 'Company IDs not assigned to the customer group: %s.';

    public function testValidateLooksNothingUpForEmptySelections(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock();
        $repositoryMock->expects($this->never())->method('getExistingCompanyIds');
        $repositoryMock->expects($this->never())->method('getAssignedCompanyIds');

        // Act
        $violationMessages = $this->validate($repositoryMock, static::ID_CUSTOMER_GROUP, [], []);

        // Assert
        $this->assertSame([], $violationMessages);
    }

    public function testValidateLooksUpTheIdsOfBothSelectionsTogetherInOneQueryPerCheck(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock();
        $repositoryMock->expects($this->once())->method('getExistingCompanyIds')->with([1, 2, 3])->willReturn([1, 2, 3]);
        $repositoryMock->expects($this->once())->method('getAssignedCompanyIds')->with(static::ID_CUSTOMER_GROUP, [1, 2, 3])->willReturn([3]);

        // Act
        $violationMessages = $this->validate($repositoryMock, static::ID_CUSTOMER_GROUP, [1, 2], [3]);

        // Assert
        $this->assertSame([], $violationMessages);
    }

    public function testValidateRejectsIdsSelectedToBeBothAssignedAndDeAssigned(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock();
        $repositoryMock->method('getExistingCompanyIds')->willReturn([1, 2]);
        $repositoryMock->expects($this->once())->method('getAssignedCompanyIds')->with(static::ID_CUSTOMER_GROUP, [1])->willReturn([]);

        // Act
        $violationMessages = $this->validate($repositoryMock, static::ID_CUSTOMER_GROUP, [1, 2], [2]);

        // Assert
        $this->assertSame([sprintf(static::MESSAGE_IDS_TO_ASSIGN_AND_DE_ASSIGN, '2')], $violationMessages);
    }

    public function testValidateRejectsUnknownIdsWithoutCheckingTheirMembership(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock();
        $repositoryMock->method('getExistingCompanyIds')->willReturn([1]);
        $repositoryMock->expects($this->once())->method('getAssignedCompanyIds')->with(static::ID_CUSTOMER_GROUP, [1])->willReturn([]);

        // Act
        $violationMessages = $this->validate($repositoryMock, static::ID_CUSTOMER_GROUP, [1, 8], [9]);

        // Assert
        $this->assertSame([sprintf(static::MESSAGE_UNKNOWN_IDS, '8, 9')], $violationMessages);
    }

    public function testValidateRejectsIdsAlreadyAssignedAndIdsNotAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock();
        $repositoryMock->method('getExistingCompanyIds')->willReturn([1, 2, 3, 4]);
        $repositoryMock->method('getAssignedCompanyIds')->willReturn([2, 3]);

        // Act
        $violationMessages = $this->validate($repositoryMock, static::ID_CUSTOMER_GROUP, [1, 2], [3, 4]);

        // Assert
        $this->assertSame([
            sprintf(static::MESSAGE_ALREADY_ASSIGNED_IDS, '2'),
            sprintf(static::MESSAGE_NOT_ASSIGNED_IDS, '4'),
        ], $violationMessages);
    }

    public function testValidateRejectsEveryIdToDeAssignForACustomerGroupThatIsNotSavedYet(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock();
        $repositoryMock->method('getExistingCompanyIds')->willReturn([1, 2]);
        $repositoryMock->expects($this->never())->method('getAssignedCompanyIds');

        // Act
        $violationMessages = $this->validate($repositoryMock, null, [1], [2]);

        // Assert
        $this->assertSame([sprintf(static::MESSAGE_NOT_ASSIGNED_IDS, '2')], $violationMessages);
    }

    protected function createRepositoryMock(): CustomerExperienceManagementRepositoryInterface&MockObject
    {
        return $this->createMock(CustomerExperienceManagementRepositoryInterface::class);
    }

    /**
     * @param list<int> $idsToAssign
     * @param list<int> $idsToDeAssign
     *
     * @return list<string>
     */
    protected function validate(
        CustomerExperienceManagementRepositoryInterface $customerExperienceManagementRepository,
        ?int $idCustomerGroup,
        array $idsToAssign,
        array $idsToDeAssign
    ): array {
        $constraintViolationList = Validation::createValidator()->validate(
            (new CustomerGroupToCompanyAssignmentTransfer())
                ->setIdsCompanyToAssign($idsToAssign)
                ->setIdsCompanyToDeAssign($idsToDeAssign),
            new CompanyAssignmentConstraint($customerExperienceManagementRepository, $idCustomerGroup),
        );

        $violationMessages = [];

        foreach ($constraintViolationList as $constraintViolation) {
            $violationMessages[] = (string)$constraintViolation->getMessage();
        }

        return $violationMessages;
    }
}
