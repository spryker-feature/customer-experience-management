<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use SprykerFeatureTest\Zed\CustomerExperienceManagement\CustomerExperienceManagementBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Business
 * @group Facade
 * @group GetCustomerGroupCompanyBusinessUnitCollectionTest
 * Add your own group annotations below this line
 */
class GetCustomerGroupCompanyBusinessUnitCollectionTest extends Unit
{
    protected const string SORT_FIELD_NAME = 'name';

    protected const string NAME_ALPHA = 'Alpha Warehouse';

    protected const string NAME_BRAVO = 'Bravo Office';

    protected const string SEARCH_TERM_ALPHA_UPPERCASE = 'ALPHA';

    protected const int MAX_LIMIT = 2;

    protected CustomerExperienceManagementBusinessTester $tester;

    public function testReturnsOnlyTheBusinessUnitMembershipsOfTheGivenCustomerGroupWithTheIdentifiersAndNamesOfTheBusinessUnitAndItsCompany(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $otherCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $firstCompanyTransfer = $this->tester->haveCompany();
        $secondCompanyTransfer = $this->tester->haveCompany();
        $firstCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($firstCompanyTransfer, static::NAME_ALPHA);
        $secondCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($secondCompanyTransfer, static::NAME_BRAVO);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $firstCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $secondCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($otherCustomerGroupTransfer, $firstCompanyBusinessUnitTransfer);

        // Act
        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyBusinessUnitCollection($this->createCriteria($customerGroupTransfer));

        // Assert
        $this->assertSame([
            [
                $customerGroupTransfer->getIdCustomerGroupOrFail(),
                $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $firstCompanyBusinessUnitTransfer->getUuidOrFail(),
                static::NAME_ALPHA,
                $firstCompanyTransfer->getUuidOrFail(),
                $firstCompanyTransfer->getNameOrFail(),
            ],
            [
                $customerGroupTransfer->getIdCustomerGroupOrFail(),
                $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $secondCompanyBusinessUnitTransfer->getUuidOrFail(),
                static::NAME_BRAVO,
                $secondCompanyTransfer->getUuidOrFail(),
                $secondCompanyTransfer->getNameOrFail(),
            ],
        ], array_map(
            fn (CustomerGroupCompanyBusinessUnitTransfer $customerGroupCompanyBusinessUnitTransfer): array => [
                $customerGroupCompanyBusinessUnitTransfer->getIdCustomerGroupOrFail(),
                $customerGroupCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $customerGroupCompanyBusinessUnitTransfer->getCompanyBusinessUnitUuidOrFail(),
                $customerGroupCompanyBusinessUnitTransfer->getCompanyBusinessUnitNameOrFail(),
                $customerGroupCompanyBusinessUnitTransfer->getCompanyUuidOrFail(),
                $customerGroupCompanyBusinessUnitTransfer->getCompanyNameOrFail(),
            ],
            $customerGroupCompanyBusinessUnitCollectionTransfer->getCustomerGroupCompanyBusinessUnits()->getArrayCopy(),
        ));
    }

    public function testFiltersByBusinessUnitUuidsAndByTheCompanyOfTheBusinessUnit(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $firstCompanyTransfer = $this->tester->haveCompany();
        $secondCompanyTransfer = $this->tester->haveCompany();
        $firstCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($firstCompanyTransfer, static::NAME_ALPHA);
        $secondCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($firstCompanyTransfer, static::NAME_BRAVO);
        $thirdCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($secondCompanyTransfer, static::NAME_ALPHA);

        foreach ([$firstCompanyBusinessUnitTransfer, $secondCompanyBusinessUnitTransfer, $thirdCompanyBusinessUnitTransfer] as $companyBusinessUnitTransfer) {
            $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $companyBusinessUnitTransfer);
        }

        $byCompanyCriteriaTransfer = $this->createCriteria($customerGroupTransfer);
        $byCompanyCriteriaTransfer->getCustomerGroupCompanyBusinessUnitConditionsOrFail()
            ->setCompanyUuids([$firstCompanyTransfer->getUuidOrFail()]);
        $byUuidCriteriaTransfer = $this->createCriteria($customerGroupTransfer);
        $byUuidCriteriaTransfer->getCustomerGroupCompanyBusinessUnitConditionsOrFail()
            ->setCompanyBusinessUnitUuids([$thirdCompanyBusinessUnitTransfer->getUuidOrFail()]);

        // Act
        $byCompanyCollectionTransfer = $this->tester->getFacade()->getCustomerGroupCompanyBusinessUnitCollection($byCompanyCriteriaTransfer);
        $byUuidCollectionTransfer = $this->tester->getFacade()->getCustomerGroupCompanyBusinessUnitCollection($byUuidCriteriaTransfer);

        // Assert
        $this->assertSame([
            $firstCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $secondCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
        ], $this->getCompanyBusinessUnitIds($byCompanyCollectionTransfer));
        $this->assertSame(
            [$thirdCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->getCompanyBusinessUnitIds($byUuidCollectionTransfer),
        );
    }

    public function testMatchesTheSearchTermAgainstTheBusinessUnitNameIgnoringCase(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyTransfer = $this->tester->haveCompany();
        $alphaCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_ALPHA);
        $bravoCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_BRAVO);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $alphaCompanyBusinessUnitTransfer);
        $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $bravoCompanyBusinessUnitTransfer);
        $customerGroupCompanyBusinessUnitCriteriaTransfer = $this->createCriteria($customerGroupTransfer);
        $customerGroupCompanyBusinessUnitCriteriaTransfer->getCustomerGroupCompanyBusinessUnitConditionsOrFail()
            ->setSearchTerm(static::SEARCH_TERM_ALPHA_UPPERCASE);

        // Act
        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyBusinessUnitCollection($customerGroupCompanyBusinessUnitCriteriaTransfer);

        // Assert
        $this->assertSame(
            [$alphaCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail()],
            $this->getCompanyBusinessUnitIds($customerGroupCompanyBusinessUnitCollectionTransfer),
        );
    }

    public function testSortsByNameWithTheBusinessUnitIdAsTiebreakerInTheSameDirection(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyTransfer = $this->tester->haveCompany();
        $bravoCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_BRAVO);
        $firstAlphaCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_ALPHA);
        $secondAlphaCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_ALPHA);

        foreach ([$bravoCompanyBusinessUnitTransfer, $firstAlphaCompanyBusinessUnitTransfer, $secondAlphaCompanyBusinessUnitTransfer] as $companyBusinessUnitTransfer) {
            $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $companyBusinessUnitTransfer);
        }

        // Act
        $ascendingCollectionTransfer = $this->tester->getFacade()->getCustomerGroupCompanyBusinessUnitCollection(
            $this->createCriteria($customerGroupTransfer)->addSort((new SortTransfer())->setField(static::SORT_FIELD_NAME)->setIsAscending(true)),
        );
        $descendingCollectionTransfer = $this->tester->getFacade()->getCustomerGroupCompanyBusinessUnitCollection(
            $this->createCriteria($customerGroupTransfer)->addSort((new SortTransfer())->setField(static::SORT_FIELD_NAME)->setIsAscending(false)),
        );

        // Assert
        $this->assertSame([
            $firstAlphaCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $secondAlphaCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $bravoCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
        ], $this->getCompanyBusinessUnitIds($ascendingCollectionTransfer));
        $this->assertSame([
            $bravoCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $secondAlphaCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $firstAlphaCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
        ], $this->getCompanyBusinessUnitIds($descendingCollectionTransfer));
    }

    public function testAppliesTheOffsetAndLimitAsGivenAndCountsEveryMatch(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitIds = [];

        for ($i = 0; $i < 5; $i++) {
            $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_ALPHA);
            $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $companyBusinessUnitTransfer);
            $companyBusinessUnitIds[] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        $customerGroupCompanyBusinessUnitCriteriaTransfer = $this->createCriteria($customerGroupTransfer)
            ->setPagination((new PaginationTransfer())->setOffset(1)->setLimit(2));

        // Act
        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyBusinessUnitCollection($customerGroupCompanyBusinessUnitCriteriaTransfer);

        // Assert
        $this->assertSame(
            [$companyBusinessUnitIds[1], $companyBusinessUnitIds[2]],
            $this->getCompanyBusinessUnitIds($customerGroupCompanyBusinessUnitCollectionTransfer),
        );
        $this->assertSame(5, $customerGroupCompanyBusinessUnitCollectionTransfer->getPaginationOrFail()->getNbResults());
    }

    public function testReturnsAtMostTheMaximumLimitWithoutPaginationAndCountsEveryMatch(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyBusinessUnitIds = $this->haveCompanyBusinessUnitMemberships($customerGroupTransfer, 3);

        // Act
        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->tester
            ->getFacadeWithCustomerGroupMembershipCollectionMaxLimit(static::MAX_LIMIT)
            ->getCustomerGroupCompanyBusinessUnitCollection($this->createCriteria($customerGroupTransfer));

        // Assert
        $this->assertSame(
            [$companyBusinessUnitIds[0], $companyBusinessUnitIds[1]],
            $this->getCompanyBusinessUnitIds($customerGroupCompanyBusinessUnitCollectionTransfer),
        );
        $this->assertSame(static::MAX_LIMIT, $customerGroupCompanyBusinessUnitCollectionTransfer->getPaginationOrFail()->getLimit());
        $this->assertSame(3, $customerGroupCompanyBusinessUnitCollectionTransfer->getPaginationOrFail()->getNbResults());
    }

    public function testReplacesALimitAboveTheMaximumLimit(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyBusinessUnitIds = $this->haveCompanyBusinessUnitMemberships($customerGroupTransfer, 3);
        $customerGroupCompanyBusinessUnitCriteriaTransfer = $this->createCriteria($customerGroupTransfer)
            ->setPagination((new PaginationTransfer())->setLimit(static::MAX_LIMIT + 1));

        // Act
        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->tester
            ->getFacadeWithCustomerGroupMembershipCollectionMaxLimit(static::MAX_LIMIT)
            ->getCustomerGroupCompanyBusinessUnitCollection($customerGroupCompanyBusinessUnitCriteriaTransfer);

        // Assert
        $this->assertSame(
            [$companyBusinessUnitIds[0], $companyBusinessUnitIds[1]],
            $this->getCompanyBusinessUnitIds($customerGroupCompanyBusinessUnitCollectionTransfer),
        );
        $this->assertSame(static::MAX_LIMIT, $customerGroupCompanyBusinessUnitCollectionTransfer->getPaginationOrFail()->getLimit());
    }

    /**
     * @return list<int>
     */
    protected function haveCompanyBusinessUnitMemberships(CustomerGroupTransfer $customerGroupTransfer, int $count): array
    {
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitIds = [];

        for ($i = 0; $i < $count; $i++) {
            $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnitNamed($companyTransfer, static::NAME_ALPHA);
            $this->tester->haveCompanyBusinessUnitMembership($customerGroupTransfer, $companyBusinessUnitTransfer);
            $companyBusinessUnitIds[] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        return $companyBusinessUnitIds;
    }

    protected function createCriteria(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupCompanyBusinessUnitCriteriaTransfer
    {
        return (new CustomerGroupCompanyBusinessUnitCriteriaTransfer())->setCustomerGroupCompanyBusinessUnitConditions(
            (new CustomerGroupCompanyBusinessUnitConditionsTransfer())->addIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );
    }

    /**
     * @return list<int>
     */
    protected function getCompanyBusinessUnitIds(
        CustomerGroupCompanyBusinessUnitCollectionTransfer $customerGroupCompanyBusinessUnitCollectionTransfer
    ): array {
        return array_values(array_map(
            fn (CustomerGroupCompanyBusinessUnitTransfer $customerGroupCompanyBusinessUnitTransfer): int => $customerGroupCompanyBusinessUnitTransfer
                ->getIdCompanyBusinessUnitOrFail(),
            $customerGroupCompanyBusinessUnitCollectionTransfer->getCustomerGroupCompanyBusinessUnits()->getArrayCopy(),
        ));
    }
}
