<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CustomersBackendResource;
use Generated\Shared\Transfer\CustomerCollectionCriteriaTransfer;
use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\ResponseTransform\PaginationLinksTransform;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CustomersBackendProvider;
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
 * @group CustomersBackendProviderTest
 * Add your own group annotations below this line
 */
class CustomersBackendProviderTest extends BackendApiTestCase
{
    protected const string UNKNOWN_CUSTOMER_REFERENCE = 'unknown-customer-reference';

    protected const string FILTER_CUSTOMER_REFERENCE = 'filtered-customer-reference';

    protected const string FILTER_EMAIL = 'filtered.customer@example.com';

    protected const string FILTER_SEARCH_TERM = 'filtered-search-term';

    protected const int ITEMS_PER_PAGE = 25;

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideItemReturnsResourceWhenCustomerExists(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $customerResponseTransfer = (new CustomerResponseTransfer())
            ->setHasCustomer(true)
            ->setCustomerTransfer($customerTransfer);

        $provider = $this->createProvider(['findCustomerByReference' => $customerResponseTransfer]);

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomersBackendResource::class, $resource);
        $this->assertSame($customerTransfer->getCustomerReferenceOrFail(), $resource->customerReference);
        $this->assertSame($customerTransfer->getEmailOrFail(), $resource->email);
    }

    public function testProvideItemThrowsNotFoundWhenCustomerIsMissing(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
        ]);

        // Act
        try {
            $provider->provide(
                $this->tester->getGetOperation(CustomersBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE],
                $this->tester->getContext()->toArray(),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_NOT_FOUND, $glueApiException->getStatusCode());
            $this->assertStringContainsString(static::UNKNOWN_CUSTOMER_REFERENCE, $glueApiException->getMessage());
        }
    }

    /**
     * @dataProvider filterQueryParametersDataProvider
     *
     * @param array<string, mixed> $query
     */
    public function testProvideCollectionTranslatesFiltersIntoConditions(array $query, callable $assertConditions): void
    {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery($query),
        );

        // Assert
        $this->assertInstanceOf(CustomerCollectionCriteriaTransfer::class, $capturedCriteria);
        $assertConditions($capturedCriteria);
    }

    /**
     * @return array<string, array{array<string, mixed>, callable}>
     */
    protected function filterQueryParametersDataProvider(): array
    {
        return [
            'customer reference filter' => [
                ['filter' => ['customers.customerReference' => static::FILTER_CUSTOMER_REFERENCE]],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $this->assertSame(
                        [static::FILTER_CUSTOMER_REFERENCE],
                        $criteria->getCustomerConditionsOrFail()->getCustomerReferences(),
                    );
                },
            ],
            'email filter' => [
                ['filter' => ['customers.email' => static::FILTER_EMAIL]],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $this->assertSame([static::FILTER_EMAIL], $criteria->getCustomerConditionsOrFail()->getEmails());
                },
            ],
            'free-text q fans out across all three columns' => [
                ['q' => static::FILTER_SEARCH_TERM],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $searchTerms = $criteria->getCustomerConditionsOrFail()->getSearchTermsOrFail();
                    $this->assertSame(static::FILTER_SEARCH_TERM, $searchTerms->getEmail());
                    $this->assertSame(static::FILTER_SEARCH_TERM, $searchTerms->getFirstName());
                    $this->assertSame(static::FILTER_SEARCH_TERM, $searchTerms->getLastName());
                },
            ],
            'free-text q wins over the per-field name filters' => [
                [
                    'q' => static::FILTER_SEARCH_TERM,
                    'filter' => ['customers.lastName' => 'ignored-last-name'],
                ],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $searchTerms = $criteria->getCustomerConditionsOrFail()->getSearchTermsOrFail();
                    $this->assertSame(static::FILTER_SEARCH_TERM, $searchTerms->getLastName());
                },
            ],
            'a filter named search is not treated as free-text search' => [
                ['filter' => ['customers.search' => static::FILTER_SEARCH_TERM]],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $this->assertNull($criteria->getCustomerConditionsOrFail()->getSearchTerms());
                },
            ],
            'last name filter does not set the email term' => [
                ['filter' => ['customers.lastName' => static::FILTER_SEARCH_TERM]],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $searchTerms = $criteria->getCustomerConditionsOrFail()->getSearchTermsOrFail();
                    $this->assertSame(static::FILTER_SEARCH_TERM, $searchTerms->getLastName());
                    $this->assertNull($searchTerms->getEmail());
                },
            ],
            'anonymized customers are excluded' => [
                [],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $this->assertNull($criteria->getCustomerConditionsOrFail()->getHasAnonymizedAt());
                },
            ],
            'anonymized customers stay excluded even when an includeAnonymized filter is sent' => [
                ['filter' => ['customers.includeAnonymized' => '1']],
                function (CustomerCollectionCriteriaTransfer $criteria): void {
                    $this->assertNull($criteria->getCustomerConditionsOrFail()->getHasAnonymizedAt());
                },
            ],
        ];
    }

    /**
     * @dataProvider paginationQueryParametersDataProvider
     *
     * @param array<string, mixed> $query
     */
    public function testProvideCollectionConvertsJsonApiPaginationToPageAndMaxPerPage(
        array $query,
        int $expectedPage,
        int $expectedMaxPerPage
    ): void {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery($query),
        );

        // Assert
        $paginationTransfer = $capturedCriteria->getPaginationOrFail();
        $this->assertSame($expectedPage, $paginationTransfer->getPage());
        $this->assertSame($expectedMaxPerPage, $paginationTransfer->getMaxPerPage());
    }

    /**
     * @return array<string, array{array<string, mixed>, int, int}>
     */
    protected function paginationQueryParametersDataProvider(): array
    {
        return [
            'defaults to the operation items per page' => [[], 1, static::ITEMS_PER_PAGE],
            'a scalar page number is not part of the page window and is ignored' => [
                ['page' => '3'],
                1,
                static::ITEMS_PER_PAGE,
            ],
            'a non-numeric page number does not fail the request' => [
                ['page' => 'abc'],
                1,
                static::ITEMS_PER_PAGE,
            ],
            'limit only' => [['page' => ['limit' => '5']], 1, 5],
            'second page' => [['page' => ['limit' => '5', 'offset' => '5']], 2, 5],
            'third page' => [['page' => ['limit' => '5', 'offset' => '10']], 3, 5],
            'partial offset resolves to its containing page' => [['page' => ['limit' => '5', 'offset' => '7']], 2, 5],
            'non-positive limit falls back to the operation page size' => [
                ['page' => ['limit' => '0']],
                1,
                static::ITEMS_PER_PAGE,
            ],
            'negative offset is clamped to the first page' => [['page' => ['limit' => '5', 'offset' => '-10']], 1, 5],
        ];
    }

    public function testProvideCollectionAcceptsWhitelistedSortFieldWithDescendingPrefix(): void
    {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContextWithQuery(['sort' => '-email,firstName']),
        );

        // Assert
        $sortCollection = $capturedCriteria->getSortCollection();
        $this->assertCount(2, $sortCollection);
        $this->assertSame('email', $sortCollection->offsetGet(0)->getField());
        $this->assertFalse($sortCollection->offsetGet(0)->getIsAscending());
        $this->assertSame('firstName', $sortCollection->offsetGet(1)->getField());
        $this->assertTrue($sortCollection->offsetGet(1)->getIsAscending());
    }

    public function testProvideCollectionRejectsSortFieldOutsideTheWhitelist(): void
    {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        try {
            $provider->provide(
                $this->createGetCollectionOperation(),
                [],
                $this->createContextWithQuery(['sort' => 'id_customer; DROP TABLE spy_customer; --']),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_BAD_REQUEST, $glueApiException->getStatusCode());
            $this->assertStringContainsString('is not supported', $glueApiException->getMessage());
        }
    }

    public function testProvideCollectionPublishesTopLevelPaginationFromTheResultTotal(): void
    {
        // Arrange
        $customerCollectionTransfer = (new CustomerCollectionTransfer())
            ->addCustomer($this->tester->haveCustomerTransfer([CustomerTransfer::ID_CUSTOMER => 1]))
            ->addCustomer($this->tester->haveCustomerTransfer([CustomerTransfer::ID_CUSTOMER => 2]))
            ->setPagination((new PaginationTransfer())->setNbResults(7));
        $request = new Request(['page' => ['limit' => '2', 'offset' => '2']]);

        $provider = $this->createProvider(['getCustomerCollectionByCollectionCriteria' => $customerCollectionTransfer]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->tester->getContext(['request' => $request])->toArray(),
        );

        // Assert
        $this->assertCount(2, $resources);
        $this->assertSame(
            ['numFound' => 7, 'currentPage' => 2, 'maxPage' => 4, 'currentItemsPerPage' => 2],
            $request->attributes->get(PaginationLinksTransform::REQUEST_ATTRIBUTE_PAGINATION),
            'Pagination is published for the top-level meta, not as a resource attribute.',
        );
    }

    public function testProvideCollectionReturnsEmptyArrayWithoutPaginationWhenNothingMatches(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'getCustomerCollectionByCollectionCriteria' => new CustomerCollectionTransfer(),
        ]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([], $resources);
    }

    protected function createProviderCapturingCriteria(?CustomerCollectionCriteriaTransfer &$capturedCriteria): CustomersBackendProvider
    {
        return $this->createProvider([
            'getCustomerCollectionByCollectionCriteria' => function (
                CustomerCollectionCriteriaTransfer $customerCollectionCriteriaTransfer
            ) use (&$capturedCriteria): CustomerCollectionTransfer {
                $capturedCriteria = $customerCollectionCriteriaTransfer;

                return new CustomerCollectionTransfer();
            },
        ]);
    }

    /**
     * @param array<string, mixed> $customerFacadeMethods
     */
    protected function createProvider(array $customerFacadeMethods): CustomersBackendProvider
    {
        $this->tester->setService(
            CustomerFacadeInterface::class,
            $this->tester->createClientStub(CustomerFacadeInterface::class, $customerFacadeMethods),
        );

        return $this->tester->getProvider(CustomersBackendProvider::class);
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            class: CustomersBackendResource::class,
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
