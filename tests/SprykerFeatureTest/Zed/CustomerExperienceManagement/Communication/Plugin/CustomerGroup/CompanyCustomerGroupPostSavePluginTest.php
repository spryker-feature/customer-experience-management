<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Exception;
use Generated\Shared\DataBuilder\CustomerGroupBuilder;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use SprykerFeatureTest\Zed\CustomerExperienceManagement\CustomerExperienceManagementCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Plugin
 * @group CustomerGroup
 * @group CompanyCustomerGroupPostSavePluginTest
 * Add your own group annotations below this line
 */
class CompanyCustomerGroupPostSavePluginTest extends Unit
{
    protected const int ID_COMPANY_NOT_EXISTING = 2147483647;

    protected CustomerExperienceManagementCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setCustomerGroupPostSavePlugins();
    }

    public function testAddAssignsTheCompaniesToAssignToTheNewCustomerGroup(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = (new CustomerGroupBuilder())->build()
            ->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToAssign($idCompany));

        // Act
        $customerGroupTransfer = $this->tester->getCustomerGroupFacade()->add($customerGroupTransfer);

        // Assert
        $this->assertSame([$idCompany], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testUpdateAssignsTheCompaniesToAssignAndSkipsTheAlreadyAssignedOnes(): void
    {
        // Arrange
        $idFirstCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idSecondCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idFirstCompany], []);

        // Act
        $this->updateCompanyAssignment($customerGroupTransfer, [$idFirstCompany, $idSecondCompany, $idSecondCompany], []);

        // Assert
        $this->assertEqualsCanonicalizing(
            [$idFirstCompany, $idSecondCompany],
            $this->tester->getAssignedCompanyIds($customerGroupTransfer),
        );
    }

    public function testUpdateDeAssignsOnlyTheCompaniesToDeAssign(): void
    {
        // Arrange
        $idFirstCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idSecondCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idFirstCompany, $idSecondCompany], []);

        // Act
        $this->updateCompanyAssignment($customerGroupTransfer, [], [$idFirstCompany]);

        // Assert
        $this->assertSame([$idSecondCompany], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testUpdateDeAssignsEveryCompanyOfTheCustomerGroupAndNothingElseWhenDeAssignAllIsSet(): void
    {
        // Arrange
        $firstCompanyTransfer = $this->tester->haveApprovedCompany();
        $idSecondCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idCompanyBusinessUnit = $this->tester->haveCompanyBusinessUnitOfCompany($firstCompanyTransfer)->getIdCompanyBusinessUnitOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $otherCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$firstCompanyTransfer->getIdCompanyOrFail(), $idSecondCompany], []);
        $this->updateCompanyAssignment($otherCustomerGroupTransfer, [$firstCompanyTransfer->getIdCompanyOrFail()], []);
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCompanyAssignment(null)
                ->setCompanyBusinessUnitAssignment(
                    (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())->addIdCompanyBusinessUnitToAssign($idCompanyBusinessUnit),
                ),
        );

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCompanyBusinessUnitAssignment(null)
                ->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->setIsDeAssignAll(true)),
        );

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
        $this->assertSame([$firstCompanyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($otherCustomerGroupTransfer));
        $this->assertSame([$idCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
    }

    public function testUpdateAssignsTheCompaniesToAssignAfterDeAssigningEveryCompanyWhenDeAssignAllIsSet(): void
    {
        // Arrange
        $idFirstCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idSecondCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idThirdCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idFirstCompany, $idSecondCompany], []);

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())
                    ->setIsDeAssignAll(true)
                    ->setIdsCompanyToAssign([$idSecondCompany, $idThirdCompany]),
            ),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$idSecondCompany, $idThirdCompany],
            $this->tester->getAssignedCompanyIds($customerGroupTransfer),
        );
    }

    public function testUpdateKeepsTheMembershipOfACompanyToAssignWhenDeAssignAllIsSet(): void
    {
        // Arrange
        $idFirstCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idSecondCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idFirstCompany, $idSecondCompany], []);
        $membershipIdsByIdCompany = $this->tester->getCompanyMembershipIdsIndexedByIdCompany($customerGroupTransfer);

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())
                    ->setIsDeAssignAll(true)
                    ->setIdsCompanyToAssign([$idSecondCompany]),
            ),
        );

        // Assert
        $this->assertSame(
            [$idSecondCompany => $membershipIdsByIdCompany[$idSecondCompany]],
            $this->tester->getCompanyMembershipIdsIndexedByIdCompany($customerGroupTransfer),
        );
    }

    public function testUpdateAppliesTheCompanyAssignmentInSeveralChunks(): void
    {
        // Arrange
        $this->tester->setCustomerGroupPostSavePluginsWithRelationChunkSize(1);
        $idFirstCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idSecondCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idThirdCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $idFourthCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idFirstCompany, $idSecondCompany, $idThirdCompany], []);

        // Act
        $this->updateCompanyAssignment(
            $customerGroupTransfer,
            [$idThirdCompany, $idFourthCompany, $idFourthCompany],
            [$idFirstCompany, $idSecondCompany],
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$idThirdCompany, $idFourthCompany],
            $this->tester->getAssignedCompanyIds($customerGroupTransfer),
        );
    }

    public function testUpdateKeepsTheAssignedCompaniesWhenTheCompanyAssignmentIsNotSet(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idCompany], []);

        // Act
        $this->tester->getCustomerGroupFacade()->update($customerGroupTransfer->setCompanyAssignment(null));

        // Assert
        $this->assertSame([$idCompany], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testUpdateLeavesTheCustomerAndBusinessUnitAssignmentsUntouched(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $idCompanyBusinessUnit = $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer)->getIdCompanyBusinessUnitOrFail();
        $idCustomer = $this->tester->haveCustomer()->getIdCustomerOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();

        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCustomerAssignment((new CustomerGroupToCustomerAssignmentTransfer())->addIdCustomerToAssign($idCustomer))
                ->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToAssign($companyTransfer->getIdCompanyOrFail()))
                ->setCompanyBusinessUnitAssignment(
                    (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())->addIdCompanyBusinessUnitToAssign($idCompanyBusinessUnit),
                ),
        );

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCustomerAssignment(new CustomerGroupToCustomerAssignmentTransfer())
                ->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToDeAssign($companyTransfer->getIdCompanyOrFail()))
                ->setCompanyBusinessUnitAssignment(null),
        );

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
        $this->assertSame([$idCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
        $this->assertTrue(
            SpyCustomerGroupToCustomerQuery::create()
                ->filterByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
                ->filterByFkCustomer($idCustomer)
                ->exists(),
        );
    }

    public function testCompanyCanBeAssignedToSeveralCustomerGroups(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();

        // Act
        $this->updateCompanyAssignment($firstCustomerGroupTransfer, [$idCompany], []);
        $this->updateCompanyAssignment($secondCustomerGroupTransfer, [$idCompany], []);

        // Assert
        $this->assertSame([$idCompany], $this->tester->getAssignedCompanyIds($firstCustomerGroupTransfer));
        $this->assertSame([$idCompany], $this->tester->getAssignedCompanyIds($secondCustomerGroupTransfer));
    }

    public function testUpdateAssignsTheCompanyAfterAFailedUpdateInTheSameProcess(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->expectThrowable(Exception::class, function () use ($customerGroupTransfer): void {
            $this->updateCompanyAssignment($customerGroupTransfer, [static::ID_COMPANY_NOT_EXISTING], []);
        });

        // Act
        $this->updateCompanyAssignment($customerGroupTransfer, [$idCompany], []);

        // Assert
        $this->assertSame([$idCompany], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testDeletingTheCustomerGroupRemovesItsCompanyAssignments(): void
    {
        // Arrange
        $idCompany = $this->tester->haveApprovedCompany()->getIdCompanyOrFail();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$idCompany], []);

        // Act
        $this->tester->getCustomerGroupFacade()->delete($customerGroupTransfer);

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testDeletingTheCompanyRemovesItsCustomerGroupAssignments(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompanyWithoutDependentRecords();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyAssignment($customerGroupTransfer, [$companyTransfer->getIdCompanyOrFail()], []);

        // Act
        $this->tester->getLocator()->company()->facade()->delete($companyTransfer);

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    /**
     * @param list<int> $companyIdsToAssign
     * @param list<int> $companyIdsToDeAssign
     */
    protected function updateCompanyAssignment(
        CustomerGroupTransfer $customerGroupTransfer,
        array $companyIdsToAssign,
        array $companyIdsToDeAssign
    ): void {
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())
                    ->setIdsCompanyToAssign($companyIdsToAssign)
                    ->setIdsCompanyToDeAssign($companyIdsToDeAssign),
            ),
        );
    }
}
