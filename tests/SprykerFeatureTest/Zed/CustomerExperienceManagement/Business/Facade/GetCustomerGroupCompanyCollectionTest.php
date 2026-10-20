<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupCompanyCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyTransfer;
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
 * @group GetCustomerGroupCompanyCollectionTest
 * Add your own group annotations below this line
 */
class GetCustomerGroupCompanyCollectionTest extends Unit
{
    protected const string SORT_FIELD_NAME = 'name';

    protected const string NAME_ALPHA = 'Alpha Industries';

    protected const string NAME_BRAVO = 'Bravo Trading';

    protected const string SEARCH_TERM_ALPHA_UPPERCASE = 'ALPHA';

    protected const int MAX_LIMIT = 2;

    protected CustomerExperienceManagementBusinessTester $tester;

    public function testReturnsOnlyTheCompanyMembershipsOfTheGivenCustomerGroupWithTheIdentifiersAndNameOfTheCompany(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $otherCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $firstCompanyTransfer = $this->tester->haveCompany();
        $secondCompanyTransfer = $this->tester->haveCompany();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $firstCompanyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $secondCompanyTransfer);
        $this->tester->haveCompanyMembership($otherCustomerGroupTransfer, $firstCompanyTransfer);

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyCollection($this->createCriteria($customerGroupTransfer));

        // Assert
        $this->assertSame([
            [
                $customerGroupTransfer->getIdCustomerGroupOrFail(),
                $firstCompanyTransfer->getIdCompanyOrFail(),
                $firstCompanyTransfer->getUuidOrFail(),
                $firstCompanyTransfer->getNameOrFail(),
            ],
            [
                $customerGroupTransfer->getIdCustomerGroupOrFail(),
                $secondCompanyTransfer->getIdCompanyOrFail(),
                $secondCompanyTransfer->getUuidOrFail(),
                $secondCompanyTransfer->getNameOrFail(),
            ],
        ], array_map(
            fn (CustomerGroupCompanyTransfer $customerGroupCompanyTransfer): array => [
                $customerGroupCompanyTransfer->getIdCustomerGroupOrFail(),
                $customerGroupCompanyTransfer->getIdCompanyOrFail(),
                $customerGroupCompanyTransfer->getCompanyUuidOrFail(),
                $customerGroupCompanyTransfer->getCompanyNameOrFail(),
            ],
            $customerGroupCompanyCollectionTransfer->getCustomerGroupCompanies()->getArrayCopy(),
        ));
    }

    public function testFiltersByCompanyUuids(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $firstCompanyTransfer = $this->tester->haveCompany();
        $secondCompanyTransfer = $this->tester->haveCompany();
        $this->tester->haveCompanyMembership($customerGroupTransfer, $firstCompanyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $secondCompanyTransfer);
        $customerGroupCompanyCriteriaTransfer = $this->createCriteria($customerGroupTransfer);
        $customerGroupCompanyCriteriaTransfer->getCustomerGroupCompanyConditionsOrFail()
            ->setCompanyUuids([$secondCompanyTransfer->getUuidOrFail()]);

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer);

        // Assert
        $this->assertSame([$secondCompanyTransfer->getIdCompanyOrFail()], $this->getCompanyIds($customerGroupCompanyCollectionTransfer));
    }

    public function testMatchesTheSearchTermAgainstTheCompanyNameIgnoringCase(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $alphaCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_ALPHA);
        $bravoCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_BRAVO);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $alphaCompanyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $bravoCompanyTransfer);
        $customerGroupCompanyCriteriaTransfer = $this->createCriteria($customerGroupTransfer);
        $customerGroupCompanyCriteriaTransfer->getCustomerGroupCompanyConditionsOrFail()
            ->setSearchTerm(static::SEARCH_TERM_ALPHA_UPPERCASE);

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer);

        // Assert
        $this->assertSame([$alphaCompanyTransfer->getIdCompanyOrFail()], $this->getCompanyIds($customerGroupCompanyCollectionTransfer));
    }

    public function testSortsByNameWithTheCompanyIdAsTiebreakerInTheSameDirection(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $bravoCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_BRAVO);
        $firstAlphaCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_ALPHA);
        $secondAlphaCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_ALPHA);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $bravoCompanyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $firstAlphaCompanyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $secondAlphaCompanyTransfer);

        // Act
        $ascendingCollectionTransfer = $this->tester->getFacade()->getCustomerGroupCompanyCollection(
            $this->createCriteria($customerGroupTransfer)->addSort((new SortTransfer())->setField(static::SORT_FIELD_NAME)->setIsAscending(true)),
        );
        $descendingCollectionTransfer = $this->tester->getFacade()->getCustomerGroupCompanyCollection(
            $this->createCriteria($customerGroupTransfer)->addSort((new SortTransfer())->setField(static::SORT_FIELD_NAME)->setIsAscending(false)),
        );

        // Assert
        $this->assertSame([
            $firstAlphaCompanyTransfer->getIdCompanyOrFail(),
            $secondAlphaCompanyTransfer->getIdCompanyOrFail(),
            $bravoCompanyTransfer->getIdCompanyOrFail(),
        ], $this->getCompanyIds($ascendingCollectionTransfer));
        $this->assertSame([
            $bravoCompanyTransfer->getIdCompanyOrFail(),
            $secondAlphaCompanyTransfer->getIdCompanyOrFail(),
            $firstAlphaCompanyTransfer->getIdCompanyOrFail(),
        ], $this->getCompanyIds($descendingCollectionTransfer));
    }

    public function testOrdersByCompanyIdWithoutSort(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $bravoCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_BRAVO);
        $alphaCompanyTransfer = $this->tester->haveCompanyNamed(static::NAME_ALPHA);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $alphaCompanyTransfer);
        $this->tester->haveCompanyMembership($customerGroupTransfer, $bravoCompanyTransfer);

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyCollection($this->createCriteria($customerGroupTransfer));

        // Assert
        $this->assertSame(
            [$bravoCompanyTransfer->getIdCompanyOrFail(), $alphaCompanyTransfer->getIdCompanyOrFail()],
            $this->getCompanyIds($customerGroupCompanyCollectionTransfer),
        );
    }

    public function testAppliesTheOffsetAndLimitAsGivenAndCountsEveryMatch(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyIds = [];

        for ($i = 0; $i < 5; $i++) {
            $companyTransfer = $this->tester->haveCompany();
            $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
            $companyIds[] = $companyTransfer->getIdCompanyOrFail();
        }

        $customerGroupCompanyCriteriaTransfer = $this->createCriteria($customerGroupTransfer)
            ->setPagination((new PaginationTransfer())->setOffset(1)->setLimit(2));

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester->getFacade()
            ->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer);

        // Assert
        $this->assertSame([$companyIds[1], $companyIds[2]], $this->getCompanyIds($customerGroupCompanyCollectionTransfer));
        $this->assertSame(5, $customerGroupCompanyCollectionTransfer->getPaginationOrFail()->getNbResults());
    }

    public function testReturnsAtMostTheMaximumLimitWithoutPaginationAndCountsEveryMatch(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyIds = $this->haveCompanyMemberships($customerGroupTransfer, 3);

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester
            ->getFacadeWithCustomerGroupMembershipCollectionMaxLimit(static::MAX_LIMIT)
            ->getCustomerGroupCompanyCollection($this->createCriteria($customerGroupTransfer));

        // Assert
        $this->assertSame([$companyIds[0], $companyIds[1]], $this->getCompanyIds($customerGroupCompanyCollectionTransfer));
        $this->assertSame(static::MAX_LIMIT, $customerGroupCompanyCollectionTransfer->getPaginationOrFail()->getLimit());
        $this->assertSame(3, $customerGroupCompanyCollectionTransfer->getPaginationOrFail()->getNbResults());
    }

    public function testReplacesALimitAboveTheMaximumLimit(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $companyIds = $this->haveCompanyMemberships($customerGroupTransfer, 3);
        $customerGroupCompanyCriteriaTransfer = $this->createCriteria($customerGroupTransfer)
            ->setPagination((new PaginationTransfer())->setLimit(static::MAX_LIMIT + 1));

        // Act
        $customerGroupCompanyCollectionTransfer = $this->tester
            ->getFacadeWithCustomerGroupMembershipCollectionMaxLimit(static::MAX_LIMIT)
            ->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer);

        // Assert
        $this->assertSame([$companyIds[0], $companyIds[1]], $this->getCompanyIds($customerGroupCompanyCollectionTransfer));
        $this->assertSame(static::MAX_LIMIT, $customerGroupCompanyCollectionTransfer->getPaginationOrFail()->getLimit());
    }

    /**
     * @return list<int>
     */
    protected function haveCompanyMemberships(CustomerGroupTransfer $customerGroupTransfer, int $count): array
    {
        $companyIds = [];

        for ($i = 0; $i < $count; $i++) {
            $companyTransfer = $this->tester->haveCompany();
            $this->tester->haveCompanyMembership($customerGroupTransfer, $companyTransfer);
            $companyIds[] = $companyTransfer->getIdCompanyOrFail();
        }

        return $companyIds;
    }

    protected function createCriteria(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupCompanyCriteriaTransfer
    {
        return (new CustomerGroupCompanyCriteriaTransfer())->setCustomerGroupCompanyConditions(
            (new CustomerGroupCompanyConditionsTransfer())->addIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );
    }

    /**
     * @return list<int>
     */
    protected function getCompanyIds(CustomerGroupCompanyCollectionTransfer $customerGroupCompanyCollectionTransfer): array
    {
        return array_values(array_map(
            fn (CustomerGroupCompanyTransfer $customerGroupCompanyTransfer): int => $customerGroupCompanyTransfer->getIdCompanyOrFail(),
            $customerGroupCompanyCollectionTransfer->getCustomerGroupCompanies()->getArrayCopy(),
        ));
    }
}
