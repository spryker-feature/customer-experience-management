<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\CompanyUser\Persistence\SpyCompanyUserQuery;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyUserCustomerGroupProviderPlugin;
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
 * @group CompanyUserCustomerGroupProviderPluginTest
 * Add your own group annotations below this line
 */
class CompanyUserCustomerGroupProviderPluginTest extends Unit
{
    protected CustomerExperienceManagementCommunicationTester $tester;

    public function testProvidesTheGroupsOfTheCompanyOfTheCurrentCompanyUser(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
        $customerTransfer = $this->haveCustomerShoppingAs($this->haveCompanyUser($companyTransfer, $this->haveBusinessUnit($companyTransfer)));

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame([$customerGroupTransfer->getIdCustomerGroupOrFail()], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    public function testProvidesTheCompanyAndBusinessUnitGroupsWithTheSameDataAsADirectlyAssignedGroup(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $companyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $companyBusinessUnitTransfer);
        $companyUserTransfer = $this->haveCompanyUser($companyTransfer, $companyBusinessUnitTransfer);
        $customerGroupFacade = $this->tester->getLocator()->customerGroup()->facade();
        $customerGroupFacade->update($customerGroupTransfer->setCustomerAssignment(
            (new CustomerGroupToCustomerAssignmentTransfer())
                ->setIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
                ->setIdsCustomerToAssign([$companyUserTransfer->getFkCustomerOrFail()]),
        ));
        $directCustomerGroupTransfer = $customerGroupFacade
            ->getCustomerGroupCollectionByIdCustomer($companyUserTransfer->getFkCustomerOrFail())
            ->getGroups()
            ->offsetGet(0);

        // Act
        $customerGroupTransfers = $this->provideCustomerGroups($this->haveCustomerShoppingAs($companyUserTransfer))->getGroups();

        // Assert
        $this->assertCount(2, $customerGroupTransfers);
        $this->assertNotNull($directCustomerGroupTransfer->getUuid());
        $this->assertNotNull($directCustomerGroupTransfer->getCreatedAt());
        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $this->assertSame($directCustomerGroupTransfer->toArray(), $customerGroupTransfer->toArray());
        }
    }

    public function testProvidesTheGroupsOfTheOwnBusinessUnitButNotThoseOfASiblingBusinessUnit(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $ownCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $siblingCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $ownCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $siblingCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyBusinessUnitMembership($ownCustomerGroupTransfer, $ownCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($siblingCustomerGroupTransfer, $siblingCompanyBusinessUnitTransfer);
        $customerTransfer = $this->haveCustomerShoppingAs($this->haveCompanyUser($companyTransfer, $ownCompanyBusinessUnitTransfer));

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame([$ownCustomerGroupTransfer->getIdCustomerGroupOrFail()], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    public function testProvidesTheGroupsOfEveryAncestorBusinessUnitButNotThoseOfAChildBusinessUnit(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $rootCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $parentCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer, $rootCompanyBusinessUnitTransfer);
        $ownCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer, $parentCompanyBusinessUnitTransfer);
        $childCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer, $ownCompanyBusinessUnitTransfer);
        $rootCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $parentCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $childCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyBusinessUnitMembership($rootCustomerGroupTransfer, $rootCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($parentCustomerGroupTransfer, $parentCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($childCustomerGroupTransfer, $childCompanyBusinessUnitTransfer);
        $customerTransfer = $this->haveCustomerShoppingAs($this->haveCompanyUser($companyTransfer, $ownCompanyBusinessUnitTransfer));

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame(
            [$parentCustomerGroupTransfer->getIdCustomerGroupOrFail(), $rootCustomerGroupTransfer->getIdCustomerGroupOrFail()],
            $this->getCustomerGroupIds($customerGroupCollectionTransfer),
        );
    }

    public function testFollowsTheBusinessUnitHierarchyAsItIsInTheNextRequestAfterReParenting(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $oldParentCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $newParentCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $ownCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer, $oldParentCompanyBusinessUnitTransfer);
        $oldParentCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $newParentCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyBusinessUnitMembership($oldParentCustomerGroupTransfer, $oldParentCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($newParentCustomerGroupTransfer, $newParentCompanyBusinessUnitTransfer);
        $customerTransfer = $this->haveCustomerShoppingAs($this->haveCompanyUser($companyTransfer, $ownCompanyBusinessUnitTransfer));
        $customerGroupIdsBeforeReParenting = $this->getCustomerGroupIds($this->provideCustomerGroups($customerTransfer));

        SpyCompanyBusinessUnitQuery::create()
            ->findPk($ownCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail())
            ->setFkParentCompanyBusinessUnit($newParentCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail())
            ->save();

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame([$oldParentCustomerGroupTransfer->getIdCustomerGroupOrFail()], $customerGroupIdsBeforeReParenting);
        $this->assertSame([$newParentCustomerGroupTransfer->getIdCustomerGroupOrFail()], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    public function testUsesOnlyTheCompanyUserTheCustomerCurrentlyShopsAs(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $assignedCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $otherCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $assignedCompanyBusinessUnitTransfer);
        $assignedCompanyUserTransfer = $this->haveCompanyUser($companyTransfer, $assignedCompanyBusinessUnitTransfer);
        $otherCompanyUserTransfer = $this->haveCompanyUser(
            $companyTransfer,
            $otherCompanyBusinessUnitTransfer,
            $assignedCompanyUserTransfer->getCustomerOrFail(),
        );

        // Act
        $assignedCustomerGroupCollectionTransfer = $this->provideCustomerGroups($this->haveCustomerShoppingAs($assignedCompanyUserTransfer));
        $otherCustomerGroupCollectionTransfer = $this->provideCustomerGroups($this->haveCustomerShoppingAs($otherCompanyUserTransfer));

        // Assert
        $this->assertSame([$customerGroupTransfer->getIdCustomerGroupOrFail()], $this->getCustomerGroupIds($assignedCustomerGroupCollectionTransfer));
        $this->assertSame([], $this->getCustomerGroupIds($otherCustomerGroupCollectionTransfer));
    }

    public function testReadsTheCompanyAndBusinessUnitOfTheCompanyUserWhenTheTransferHasOnlyItsId(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $companyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $companyCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyBusinessUnitCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyMembership($companyCustomerGroupTransfer, $companyTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($companyBusinessUnitCustomerGroupTransfer, $companyBusinessUnitTransfer);
        $customerTransfer = $this->haveCustomerShoppingAsCompanyUserId($this->haveCompanyUser($companyTransfer, $companyBusinessUnitTransfer));

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame(
            [$companyCustomerGroupTransfer->getIdCustomerGroupOrFail(), $companyBusinessUnitCustomerGroupTransfer->getIdCustomerGroupOrFail()],
            $this->getCustomerGroupIds($customerGroupCollectionTransfer),
        );
    }

    public function testUsesTheCompanyAndBusinessUnitOfTheTransferWithoutReadingTheCompanyUser(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $storedCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $sessionCompanyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $storedCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $sessionCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyBusinessUnitMembership($storedCustomerGroupTransfer, $storedCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($sessionCustomerGroupTransfer, $sessionCompanyBusinessUnitTransfer);
        $customerTransfer = $this->haveCustomerShoppingAs($this->haveCompanyUser($companyTransfer, $storedCompanyBusinessUnitTransfer));
        $customerTransfer->getCompanyUserTransferOrFail()
            ->setFkCompanyBusinessUnit($sessionCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame([$sessionCustomerGroupTransfer->getIdCustomerGroupOrFail()], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    public function testIgnoresACompanyUserOfAnotherCustomer(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
        $foreignCompanyUserTransfer = $this->haveCompanyUser($companyTransfer, $this->haveBusinessUnit($companyTransfer));
        $customerTransfer = $this->tester->haveCustomer()->setCompanyUserTransfer(
            (new CompanyUserTransfer())->setIdCompanyUser($foreignCompanyUserTransfer->getIdCompanyUserOrFail()),
        );

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame([], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    public function testIgnoresAnInactiveCompanyUser(): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
        $companyUserTransfer = $this->haveCompanyUser($companyTransfer, $this->haveBusinessUnit($companyTransfer));
        SpyCompanyUserQuery::create()->findPk($companyUserTransfer->getIdCompanyUserOrFail())->setIsActive(false)->save();

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($this->haveCustomerShoppingAsCompanyUserId($companyUserTransfer));

        // Assert
        $this->assertSame([], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    /**
     * @dataProvider inactiveOrUnapprovedCompanyDataProvider
     */
    public function testIgnoresTheMembershipsOfAnInactiveOrUnapprovedCompany(bool $isActive, string $status): void
    {
        // Arrange
        $companyTransfer = $this->haveActiveCompany();
        $companyBusinessUnitTransfer = $this->haveBusinessUnit($companyTransfer);
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $companyBusinessUnitTransfer);
        $customerTransfer = $this->haveCustomerShoppingAsCompanyUserId($this->haveCompanyUser($companyTransfer, $companyBusinessUnitTransfer));
        SpyCompanyQuery::create()->findPk($companyTransfer->getIdCompanyOrFail())->setIsActive($isActive)->setStatus($status)->save();

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($customerTransfer);

        // Assert
        $this->assertSame([], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public function inactiveOrUnapprovedCompanyDataProvider(): array
    {
        return [
            'inactive company' => [false, SpyCompanyTableMap::COL_STATUS_APPROVED],
            'pending company' => [true, SpyCompanyTableMap::COL_STATUS_PENDING],
            'denied company' => [true, SpyCompanyTableMap::COL_STATUS_DENIED],
        ];
    }

    public function testReturnsNoGroupsForACustomerWithoutCompanyUser(): void
    {
        // Arrange
        $this->tester->haveCustomerGroup();

        // Act
        $customerGroupCollectionTransfer = $this->provideCustomerGroups($this->tester->haveCustomer());

        // Assert
        $this->assertSame([], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
    }

    protected function provideCustomerGroups(CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer
    {
        return (new CompanyUserCustomerGroupProviderPlugin())->provideCustomerGroups($customerTransfer);
    }

    protected function haveActiveCompany(): CompanyTransfer
    {
        return $this->tester->haveCompany([
            CompanyTransfer::IS_ACTIVE => true,
            CompanyTransfer::STATUS => SpyCompanyTableMap::COL_STATUS_APPROVED,
        ]);
    }

    protected function haveBusinessUnit(
        CompanyTransfer $companyTransfer,
        ?CompanyBusinessUnitTransfer $parentCompanyBusinessUnitTransfer = null
    ): CompanyBusinessUnitTransfer {
        return $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompanyOrFail(),
            CompanyBusinessUnitTransfer::FK_PARENT_COMPANY_BUSINESS_UNIT => $parentCompanyBusinessUnitTransfer?->getIdCompanyBusinessUnitOrFail(),
        ]);
    }

    protected function haveCompanyUser(
        CompanyTransfer $companyTransfer,
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer,
        ?CustomerTransfer $customerTransfer = null
    ): CompanyUserTransfer {
        $customerTransfer ??= $this->tester->haveCustomer();

        return $this->tester->haveCompanyUser([
            CompanyUserTransfer::CUSTOMER => $customerTransfer,
            CompanyUserTransfer::FK_CUSTOMER => $customerTransfer->getIdCustomerOrFail(),
            CompanyUserTransfer::FK_COMPANY => $companyTransfer->getIdCompanyOrFail(),
            CompanyUserTransfer::FK_COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            CompanyUserTransfer::IS_ACTIVE => true,
        ]);
    }

    protected function haveCustomerShoppingAs(CompanyUserTransfer $companyUserTransfer): CustomerTransfer
    {
        return (new CustomerTransfer())
            ->setIdCustomer($companyUserTransfer->getFkCustomerOrFail())
            ->setCompanyUserTransfer(
                (new CompanyUserTransfer())
                    ->setIdCompanyUser($companyUserTransfer->getIdCompanyUserOrFail())
                    ->setFkCompany($companyUserTransfer->getFkCompanyOrFail())
                    ->setFkCompanyBusinessUnit($companyUserTransfer->getFkCompanyBusinessUnitOrFail()),
            );
    }

    protected function haveCustomerShoppingAsCompanyUserId(CompanyUserTransfer $companyUserTransfer): CustomerTransfer
    {
        return (new CustomerTransfer())
            ->setIdCustomer($companyUserTransfer->getFkCustomerOrFail())
            ->setCompanyUserTransfer((new CompanyUserTransfer())->setIdCompanyUser($companyUserTransfer->getIdCompanyUserOrFail()));
    }

    /**
     * @return list<int>
     */
    protected function getCustomerGroupIds(CustomerGroupCollectionTransfer $customerGroupCollectionTransfer): array
    {
        return array_values(array_map(
            fn (CustomerGroupTransfer $customerGroupTransfer): int => $customerGroupTransfer->getIdCustomerGroupOrFail(),
            $customerGroupCollectionTransfer->getGroups()->getArrayCopy(),
        ));
    }
}
