<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\CustomerGroupBuilder;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
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
 * @group CompanyBusinessUnitCustomerGroupPostSavePluginTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitCustomerGroupPostSavePluginTest extends Unit
{
    protected CustomerExperienceManagementCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setCustomerGroupPostSavePlugins();
    }

    public function testAddAssignsTheBusinessUnitsToAssignToTheNewCustomerGroup(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = (new CustomerGroupBuilder())->build()
            ->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())->addIdCompanyBusinessUnitToAssign($idCompanyBusinessUnit),
            );

        // Act
        $customerGroupTransfer = $this->tester->getCustomerGroupFacade()->add($customerGroupTransfer);

        // Assert
        $this->assertSame([$idCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
    }

    public function testUpdateAssignsTheBusinessUnitsToAssignAndSkipsTheAlreadyAssignedOnes(): void
    {
        // Arrange
        $idFirstCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idSecondCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idFirstCompanyBusinessUnit], []);

        // Act
        $this->updateCompanyBusinessUnitAssignment(
            $customerGroupTransfer,
            [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit, $idSecondCompanyBusinessUnit],
            [],
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit],
            $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer),
        );
    }

    public function testUpdateDeAssignsOnlyTheBusinessUnitsToDeAssign(): void
    {
        // Arrange
        $idFirstCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idSecondCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit], []);

        // Act
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [], [$idFirstCompanyBusinessUnit]);

        // Assert
        $this->assertSame([$idSecondCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
    }

    public function testUpdateDeAssignsEveryBusinessUnitOfTheCustomerGroupAndNothingElseWhenDeAssignAllIsSet(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveApprovedCompany();
        $idFirstCompanyBusinessUnit = $this->tester->haveCompanyBusinessUnitOfCompany($companyTransfer)->getIdCompanyBusinessUnitOrFail();
        $idSecondCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $otherCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit], []);
        $this->updateCompanyBusinessUnitAssignment($otherCustomerGroupTransfer, [$idFirstCompanyBusinessUnit], []);
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCompanyBusinessUnitAssignment(null)
                ->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->addIdCompanyToAssign($companyTransfer->getIdCompanyOrFail())),
        );

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCompanyAssignment(null)
                ->setCompanyBusinessUnitAssignment((new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())->setIsDeAssignAll(true)),
        );

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
        $this->assertSame([$idFirstCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($otherCustomerGroupTransfer));
        $this->assertSame([$companyTransfer->getIdCompanyOrFail()], $this->tester->getAssignedCompanyIds($customerGroupTransfer));
    }

    public function testUpdateAssignsTheBusinessUnitsToAssignAfterDeAssigningEveryBusinessUnitWhenDeAssignAllIsSet(): void
    {
        // Arrange
        $idFirstCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idSecondCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idThirdCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit], []);

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())
                    ->setIsDeAssignAll(true)
                    ->setIdsCompanyBusinessUnitToAssign([$idSecondCompanyBusinessUnit, $idThirdCompanyBusinessUnit]),
            ),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$idSecondCompanyBusinessUnit, $idThirdCompanyBusinessUnit],
            $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer),
        );
    }

    public function testUpdateKeepsTheMembershipOfABusinessUnitToAssignWhenDeAssignAllIsSet(): void
    {
        // Arrange
        $idFirstCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idSecondCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit], []);
        $membershipIdsByIdCompanyBusinessUnit = $this->tester->getCompanyBusinessUnitMembershipIdsIndexedByIdCompanyBusinessUnit($customerGroupTransfer);

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())
                    ->setIsDeAssignAll(true)
                    ->setIdsCompanyBusinessUnitToAssign([$idSecondCompanyBusinessUnit]),
            ),
        );

        // Assert
        $this->assertSame(
            [$idSecondCompanyBusinessUnit => $membershipIdsByIdCompanyBusinessUnit[$idSecondCompanyBusinessUnit]],
            $this->tester->getCompanyBusinessUnitMembershipIdsIndexedByIdCompanyBusinessUnit($customerGroupTransfer),
        );
    }

    public function testUpdateAppliesTheBusinessUnitAssignmentInSeveralChunks(): void
    {
        // Arrange
        $this->tester->setCustomerGroupPostSavePluginsWithRelationChunkSize(1);
        $idFirstCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idSecondCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idThirdCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $idFourthCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment(
            $customerGroupTransfer,
            [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit, $idThirdCompanyBusinessUnit],
            [],
        );

        // Act
        $this->updateCompanyBusinessUnitAssignment(
            $customerGroupTransfer,
            [$idThirdCompanyBusinessUnit, $idFourthCompanyBusinessUnit, $idFourthCompanyBusinessUnit],
            [$idFirstCompanyBusinessUnit, $idSecondCompanyBusinessUnit],
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$idThirdCompanyBusinessUnit, $idFourthCompanyBusinessUnit],
            $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer),
        );
    }

    public function testUpdateKeepsTheAssignedBusinessUnitsWhenTheBusinessUnitAssignmentIsNotSet(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idCompanyBusinessUnit], []);

        // Act
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer
                ->setCompanyBusinessUnitAssignment(null)
                ->setCompanyAssignment(new CustomerGroupToCompanyAssignmentTransfer()),
        );

        // Assert
        $this->assertSame([$idCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
    }

    public function testBusinessUnitCanBeAssignedToSeveralCustomerGroups(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $firstCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $secondCustomerGroupTransfer = $this->tester->haveCustomerGroup();

        // Act
        $this->updateCompanyBusinessUnitAssignment($firstCustomerGroupTransfer, [$idCompanyBusinessUnit], []);
        $this->updateCompanyBusinessUnitAssignment($secondCustomerGroupTransfer, [$idCompanyBusinessUnit], []);

        // Assert
        $this->assertSame([$idCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($firstCustomerGroupTransfer));
        $this->assertSame([$idCompanyBusinessUnit], $this->tester->getAssignedCompanyBusinessUnitIds($secondCustomerGroupTransfer));
    }

    public function testDeletingTheCustomerGroupRemovesItsBusinessUnitAssignments(): void
    {
        // Arrange
        $idCompanyBusinessUnit = $this->haveCompanyBusinessUnitId();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment($customerGroupTransfer, [$idCompanyBusinessUnit], []);

        // Act
        $this->tester->getCustomerGroupFacade()->delete($customerGroupTransfer);

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
    }

    public function testDeletingTheBusinessUnitRemovesItsCustomerGroupAssignments(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitOfCompany($this->tester->haveApprovedCompany());
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->updateCompanyBusinessUnitAssignment(
            $customerGroupTransfer,
            [$companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            [],
        );

        // Act
        $this->tester->getLocator()->companyBusinessUnit()->facade()->delete($companyBusinessUnitTransfer);

        // Assert
        $this->assertSame([], $this->tester->getAssignedCompanyBusinessUnitIds($customerGroupTransfer));
    }

    protected function haveCompanyBusinessUnitId(): int
    {
        return $this->tester
            ->haveCompanyBusinessUnitOfCompany($this->tester->haveApprovedCompany())
            ->getIdCompanyBusinessUnitOrFail();
    }

    /**
     * @param list<int> $companyBusinessUnitIdsToAssign
     * @param list<int> $companyBusinessUnitIdsToDeAssign
     */
    protected function updateCompanyBusinessUnitAssignment(
        CustomerGroupTransfer $customerGroupTransfer,
        array $companyBusinessUnitIdsToAssign,
        array $companyBusinessUnitIdsToDeAssign
    ): void {
        $this->tester->getCustomerGroupFacade()->update(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())
                    ->setIdsCompanyBusinessUnitToAssign($companyBusinessUnitIdsToAssign)
                    ->setIdsCompanyBusinessUnitToDeAssign($companyBusinessUnitIdsToDeAssign),
            ),
        );
    }
}
