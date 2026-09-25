<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CustomerGroupsBackendProvider;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CustomerGroupsBackendProviderTest
 * Add your own group annotations below this line
 */
class CustomerGroupsBackendProviderTest extends BackendApiTestCase
{
    protected const string UUID = '4b1e6a02-9f37-5c48-b6d1-2e8a70c9f4a5';

    protected const string UNKNOWN_UUID = '00000000-0000-5000-8000-000000000000';

    protected const string GROUP_NAME = 'Wholesale';

    protected const string GROUP_DESCRIPTION = 'Tier one';

    protected const int ITEMS_PER_PAGE = 10;

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideItemReturnsTheGroupAddressedByTheUuid(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'getCustomerGroupCollection' => $this->createCollection($this->createCustomerGroupTransfer()),
        ]);

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomerGroupsBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomerGroupsBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertSame(static::GROUP_NAME, $resource->name);
    }

    public function testProvideItemRespondsNotFoundWhenNoGroupMatchesTheUuid(): void
    {
        // Arrange
        $provider = $this->createProvider(['getCustomerGroupCollection' => new CustomerGroupCollectionTransfer()]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $provider->provide(
                $this->tester->getGetOperation(CustomerGroupsBackendResource::class),
                [CustomerGroupTransfer::UUID => static::UNKNOWN_UUID],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProvideCollectionReturnsAnEmptyArrayWhenNothingMatches(): void
    {
        // Arrange
        $provider = $this->createProvider(['getCustomerGroupCollection' => new CustomerGroupCollectionTransfer()]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([], $resources);
    }

    public function testProvideCollectionPassesTheFreeTextSearchTermToTheFacade(): void
    {
        // Arrange
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery(['q' => 'needle']),
        );

        // Assert
        $this->assertSame(
            'needle',
            $capturedCriteria?->getCustomerGroupConditionsOrFail()->getSearchTerm(),
        );
    }

    public function testProvideCollectionPassesTheNameFilterToTheFacade(): void
    {
        // Arrange
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery(['filter' => ['customer-groups.name' => static::GROUP_NAME]]),
        );

        // Assert
        $this->assertSame(
            [static::GROUP_NAME],
            $capturedCriteria?->getCustomerGroupConditionsOrFail()->getNames(),
        );
    }

    /**
     * @dataProvider provideWhitelistedSortFields
     */
    public function testProvideCollectionAcceptsEveryWhitelistedSortField(
        string $sort,
        string $expectedField,
        bool $expectedIsAscending
    ): void {
        // Arrange
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery(['sort' => $sort]),
        );

        // Assert
        $sortTransfers = $capturedCriteria?->getSortCollection();
        $this->assertCount(1, $sortTransfers);
        $this->assertSame($expectedField, $sortTransfers->offsetGet(0)->getField());
        $this->assertSame($expectedIsAscending, $sortTransfers->offsetGet(0)->getIsAscending());
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public function provideWhitelistedSortFields(): array
    {
        return [
            'name ascending' => ['name', 'name', true],
            'name descending' => ['-name', 'name', false],
            'createdAt ascending' => ['createdAt', 'createdAt', true],
            'createdAt descending' => ['-createdAt', 'createdAt', false],
        ];
    }

    public function testProvideCollectionKeepsTheOrderOfSeveralSortFields(): void
    {
        // Arrange
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery(['sort' => 'createdAt,-name']),
        );

        // Assert
        $sortTransfers = $capturedCriteria?->getSortCollection();
        $this->assertCount(2, $sortTransfers);
        $this->assertSame('createdAt', $sortTransfers->offsetGet(0)->getField());
        $this->assertSame('name', $sortTransfers->offsetGet(1)->getField());
        $this->assertFalse($sortTransfers->offsetGet(1)->getIsAscending());
    }

    public function testProvideCollectionRejectsASortFieldOutsideTheWhitelist(): void
    {
        // Arrange
        $provider = $this->createProvider(['getCustomerGroupCollection' => new CustomerGroupCollectionTransfer()]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_BAD_REQUEST,
            fn () => $provider->provide(
                $this->createGetCollectionOperation(),
                [],
                $this->createContextWithQuery(['sort' => 'description']),
            ),
        );
    }

    public function testProvideCollectionPublishesThePaginationTotalFromTheFacade(): void
    {
        // Arrange
        $customerGroupCollectionTransfer = $this->createCollection($this->createCustomerGroupTransfer())
            ->setPagination((new PaginationTransfer())->setNbResults(42));

        $provider = $this->createProvider([
            'getCustomerGroupCollection' => function (
                CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
            ) use ($customerGroupCollectionTransfer): CustomerGroupCollectionTransfer {
                $customerGroupCriteriaTransfer->getPaginationOrFail()->setNbResults(42);

                return $customerGroupCollectionTransfer;
            },
        ]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertCount(1, $resources);
    }

    protected function createProviderCapturingCriteria(
        ?CustomerGroupCriteriaTransfer &$capturedCriteria = null
    ): CustomerGroupsBackendProvider {
        return $this->createProvider([
            'getCustomerGroupCollection' => function (
                CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
            ) use (&$capturedCriteria): CustomerGroupCollectionTransfer {
                $capturedCriteria = $customerGroupCriteriaTransfer;

                return new CustomerGroupCollectionTransfer();
            },
        ]);
    }

    /**
     * @param array<string, mixed> $customerGroupFacadeMethods
     */
    protected function createProvider(array $customerGroupFacadeMethods): CustomerGroupsBackendProvider
    {
        $this->tester->setService(
            CustomerGroupFacadeInterface::class,
            $this->tester->createClientStub(CustomerGroupFacadeInterface::class, $customerGroupFacadeMethods),
        );

        return $this->tester->getProvider(CustomerGroupsBackendProvider::class);
    }

    protected function createCustomerGroupTransfer(): CustomerGroupTransfer
    {
        return (new CustomerGroupTransfer())
            ->setIdCustomerGroup(1)
            ->setUuid(static::UUID)
            ->setName(static::GROUP_NAME)
            ->setDescription(static::GROUP_DESCRIPTION);
    }

    protected function createCollection(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupCollectionTransfer
    {
        return (new CustomerGroupCollectionTransfer())->addGroup($customerGroupTransfer);
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            class: CustomerGroupsBackendResource::class,
            paginationItemsPerPage: static::ITEMS_PER_PAGE,
        );
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    protected function createContextWithQuery(array $query): array
    {
        return $this->tester->getContext(['request' => new Request($query)])->toArray();
    }
}
