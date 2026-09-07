<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CustomersAddressesBackendResource;
use Generated\Api\Backend\Pagination;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\Transfer\AddressCollectionTransfer;
use Generated\Shared\Transfer\AddressConditionsTransfer;
use Generated\Shared\Transfer\AddressCriteriaTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use ReflectionClass;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CustomerAddressesBackendProvider;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
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
 * @group CustomerAddressesBackendProviderTest
 * Add your own group annotations below this line
 */
class CustomerAddressesBackendProviderTest extends BackendApiTestCase
{
    protected const string UNKNOWN_CUSTOMER_REFERENCE = 'unknown-customer-reference';

    protected const string UUID = '5caa05f5-41f5-5e6c-a254-07d7887fb4e9';

    protected const int ID_CUSTOMER_ADDRESS = 41;

    protected const int ID_OTHER_CUSTOMER_ADDRESS = 42;

    protected const int ID_OTHER_CUSTOMER = 99;

    protected const string COUNTRY_NAME = 'Germany';

    protected const string CITY = 'Berlin';

    protected const int ITEMS_PER_PAGE = 25;

    protected const int READER_FALLBACK_LIMIT = 10;

    protected const string RESOURCE_PROPERTY_ID_CUSTOMER_ADDRESS = 'idCustomerAddress';

    protected const string ISO_2_CODE = 'DE';

    protected const string REGION_CODE = 'DE-BE';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideItemReturnsResourceForAnAddressOfTheCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $addressTransfer = $this->createAddressTransfer($customerTransfer);

        $provider = $this->createProvider($this->stubFacade($customerTransfer, [$addressTransfer]));

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomersAddressesBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertSame($customerTransfer->getCustomerReferenceOrFail(), $resource->customerReference);
        $this->assertSame(static::CITY, $resource->city);
    }

    public function testProvideItemReportsTheStoredRegionReferenceAsItsRegionCode(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $addressTransfer = $this->createAddressTransfer($customerTransfer, [
            AddressTransfer::ISO2_CODE => static::ISO_2_CODE,
            AddressTransfer::REGION => static::REGION_CODE,
        ]);

        $provider = $this->createProvider($this->stubFacade($customerTransfer, [$addressTransfer]));

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame(static::REGION_CODE, $resource->region);
    }

    public function testProvideItemLeavesTheRegionEmptyForAnAddressWithoutOne(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $addressTransfer = $this->createAddressTransfer($customerTransfer, [
            AddressTransfer::ISO2_CODE => static::ISO_2_CODE,
            AddressTransfer::REGION => null,
        ]);

        $provider = $this->createProvider($this->stubFacade($customerTransfer, [$addressTransfer]));

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($resource->region);
    }

    public function testProvideItemFlattensTheCountryNameOntoTheResource(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $provider = $this->createProvider($this->stubFacade($customerTransfer, [$this->createAddressTransfer($customerTransfer)]));

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame(static::COUNTRY_NAME, $resource->country);
    }

    public function testProvideItemScopesTheLookupToTheUuidAndTheOwningCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadeCapturingCriteria(
            $customerTransfer,
            [$this->createAddressTransfer($customerTransfer)],
            $capturedCriteria,
        ));

        // Act
        $provider->provide(
            $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(AddressCriteriaTransfer::class, $capturedCriteria);
        $addressConditionsTransfer = $capturedCriteria->getAddressConditionsOrFail();
        $this->assertSame([static::UUID], $addressConditionsTransfer->getUuids());
        $this->assertSame(
            [$customerTransfer->getIdCustomerOrFail()],
            $addressConditionsTransfer->getCustomerIds(),
            'The owning customer must be part of the criteria, otherwise the uuid alone would be addressable.',
        );
    }

    public function testProvideItemThrowsNotFoundWhenTheAddressBelongsToAnotherCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $otherCustomerTransfer = $this->tester->haveCustomerTransfer([
            CustomerTransfer::ID_CUSTOMER => static::ID_OTHER_CUSTOMER,
        ]);

        $provider = $this->createProvider($this->stubFacade(
            $customerTransfer,
            [$this->createAddressTransfer($otherCustomerTransfer)],
        ));

        // Act
        try {
            $provider->provide(
                $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
                $this->createItemUriVariables($customerTransfer),
                $this->tester->getContext()->toArray(),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_NOT_FOUND, $glueApiException->getStatusCode());
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_ADDRESS_NOT_FOUND,
                $glueApiException->getErrorCode(),
            );
            $this->assertStringContainsString(static::UUID, $glueApiException->getMessage());
        }
    }

    public function testProvideItemThrowsNotFoundForUnknownCustomerReferenceWithoutLookingUpAnAddress(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
            'getAddressCollection' => function (): AddressCollectionTransfer {
                $this->fail('The address lookup must not run for an unknown customer reference.');
            },
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $provider->provide(
                $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
                [
                    CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE,
                    AddressTransfer::UUID => static::UUID,
                ],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testTheResourceDoesNotExposeTheAddressDatabaseId(): void
    {
        // Arrange
        $resourceReflection = new ReflectionClass(CustomersAddressesBackendResource::class);

        // Assert
        $this->assertFalse(
            $resourceReflection->hasProperty(static::RESOURCE_PROPERTY_ID_CUSTOMER_ADDRESS),
            'The addresses resource must not expose the address database id.',
        );
    }

    /**
     * @dataProvider defaultAddressFlagsDataProvider
     */
    public function testProvideItemComputesTheDefaultAddressFlags(
        ?int $idDefaultBillingAddress,
        ?int $idDefaultShippingAddress,
        bool $expectedIsDefaultBilling,
        bool $expectedIsDefaultShipping
    ): void {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer()
            ->setDefaultBillingAddress($idDefaultBillingAddress)
            ->setDefaultShippingAddress($idDefaultShippingAddress);

        $provider = $this->createProvider($this->stubFacade($customerTransfer, [$this->createAddressTransfer($customerTransfer)]));

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame($expectedIsDefaultBilling, $resource->isDefaultBilling);
        $this->assertSame($expectedIsDefaultShipping, $resource->isDefaultShipping);
    }

    /**
     * @return array<string, array{int|null, int|null, bool, bool}>
     */
    protected function defaultAddressFlagsDataProvider(): array
    {
        return [
            'both defaults point at this address' => [
                static::ID_CUSTOMER_ADDRESS,
                static::ID_CUSTOMER_ADDRESS,
                true,
                true,
            ],
            'only the billing default points at this address' => [
                static::ID_CUSTOMER_ADDRESS,
                static::ID_OTHER_CUSTOMER_ADDRESS,
                true,
                false,
            ],
            'only the shipping default points at this address' => [
                static::ID_OTHER_CUSTOMER_ADDRESS,
                static::ID_CUSTOMER_ADDRESS,
                false,
                true,
            ],
            'the defaults point at another address' => [
                static::ID_OTHER_CUSTOMER_ADDRESS,
                static::ID_OTHER_CUSTOMER_ADDRESS,
                false,
                false,
            ],
            'the customer has no defaults at all' => [null, null, false, false],
        ];
    }

    public function testProvideCollectionScopesTheCriteriaToTheOwningCustomerOnly(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadeCapturingCriteria($customerTransfer, [], $capturedCriteria));

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $addressConditionsTransfer = $capturedCriteria->getAddressConditionsOrFail();
        $this->assertSame([$customerTransfer->getIdCustomerOrFail()], $addressConditionsTransfer->getCustomerIds());
        $this->assertSame([], $addressConditionsTransfer->getUuids());
    }

    public function testProvideCollectionSetsPaginationOnFirstResourceOnly(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $addressCollectionTransfer = (new AddressCollectionTransfer())
            ->addAddress($this->createAddressTransfer($customerTransfer))
            ->addAddress($this->createAddressTransfer($customerTransfer, [
                AddressTransfer::ID_CUSTOMER_ADDRESS => static::ID_OTHER_CUSTOMER_ADDRESS,
            ]))
            ->setPagination(
                (new PaginationTransfer())->setPage(1)->setMaxPerPage(2)->setNbResults(7)->setLastPage(4),
            );

        $provider = $this->createProvider([
            'findCustomerByReference' => $this->createCustomerResponse($customerTransfer),
            'getAddressCollection' => $addressCollectionTransfer,
        ]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertCount(2, $resources);
        $this->assertInstanceOf(Pagination::class, $resources[0]->pagination);
        $this->assertSame(7, $resources[0]->pagination->getNumFound());
        $this->assertSame(1, $resources[0]->pagination->getCurrentPage());
        $this->assertSame(4, $resources[0]->pagination->getMaxPage());
        $this->assertSame(2, $resources[0]->pagination->getCurrentItemsPerPage());
        $this->assertNull($resources[1]->pagination, 'Only the first resource may carry the pagination metadata.');
    }

    public function testProvideCollectionReturnsEmptyArrayWhenTheCustomerHasNoAddresses(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $provider = $this->createProvider($this->stubFacade($customerTransfer, []));

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([], $resources);
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
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadeCapturingCriteria($customerTransfer, [], $capturedCriteria));

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
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
            'the page number from the generated OpenAPI document' => [
                ['page' => '3'],
                3,
                static::ITEMS_PER_PAGE,
            ],
            'a non-numeric page number does not fail the request' => [
                ['page' => 'abc'],
                1,
                static::ITEMS_PER_PAGE,
            ],
            'limit only' => [['page' => ['limit' => '5']], 1, 5],
            'second page' => [['page' => ['limit' => '5', 'offset' => '5']], 2, 5],
            'partial offset resolves to its containing page' => [['page' => ['limit' => '5', 'offset' => '7']], 2, 5],
            'non-positive limit falls back to the reader default' => [
                ['page' => ['limit' => '0']],
                1,
                static::READER_FALLBACK_LIMIT,
            ],
            'negative offset is clamped to the first page' => [['page' => ['limit' => '5', 'offset' => '-10']], 1, 5],
        ];
    }

    public function testProvideCollectionAcceptsWhitelistedSortFieldsWithDescendingPrefix(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadeCapturingCriteria($customerTransfer, [], $capturedCriteria));

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithQuery(['sort' => '-lastName,zipCode']),
        );

        // Assert
        $sortCollection = $capturedCriteria->getSortCollection();
        $this->assertCount(2, $sortCollection);
        $this->assertSame(AddressTransfer::LAST_NAME, $sortCollection->offsetGet(0)->getField());
        $this->assertFalse($sortCollection->offsetGet(0)->getIsAscending());
        $this->assertSame(AddressTransfer::ZIP_CODE, $sortCollection->offsetGet(1)->getField());
        $this->assertTrue($sortCollection->offsetGet(1)->getIsAscending());
    }

    /**
     * @dataProvider rejectedSortFieldDataProvider
     */
    public function testProvideCollectionRejectsSortFieldOutsideTheWhitelist(string $sortField): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $provider = $this->createProvider($this->stubFacade($customerTransfer, []));

        // Act
        try {
            $provider->provide(
                $this->createGetCollectionOperation(),
                [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
                $this->createContextWithQuery(['sort' => $sortField]),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_BAD_REQUEST, $glueApiException->getStatusCode());
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
                $glueApiException->getErrorCode(),
            );
            $this->assertStringContainsString('is not supported', $glueApiException->getMessage());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    protected function rejectedSortFieldDataProvider(): array
    {
        return [
            'sql injection attempt' => ['id_customer_address; DROP TABLE spy_customer_address; --'],
            'a readable but unsortable property' => [AddressTransfer::UUID],
            'the address database id' => [AddressTransfer::ID_CUSTOMER_ADDRESS],
            'city, which the Back Office address table does not sort by either' => [AddressTransfer::CITY],
            'the creation timestamp' => [AddressTransfer::CREATED_AT],
            'the update timestamp' => [AddressTransfer::UPDATED_AT],
        ];
    }

    public function testProvideCollectionThrowsNotFoundForUnknownCustomerReference(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $provider->provide(
                $this->createGetCollectionOperation(),
                [CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    /**
     * @param array<string, mixed> $override
     */
    protected function createAddressTransfer(CustomerTransfer $ownerTransfer, array $override = []): AddressTransfer
    {
        /** @var \Generated\Shared\Transfer\AddressTransfer $addressTransfer */
        $addressTransfer = (new AddressBuilder($override + [
            AddressTransfer::UUID => static::UUID,
            AddressTransfer::ID_CUSTOMER_ADDRESS => static::ID_CUSTOMER_ADDRESS,
            AddressTransfer::CITY => static::CITY,
            AddressTransfer::FK_CUSTOMER => $ownerTransfer->getIdCustomerOrFail(),
        ]))->build();

        return $addressTransfer->setCountry((new CountryTransfer())->setName(static::COUNTRY_NAME));
    }

    protected function createCustomerResponse(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return (new CustomerResponseTransfer())
            ->setHasCustomer(true)
            ->setCustomerTransfer($customerTransfer);
    }

    /**
     * @return array<string, string>
     */
    protected function createItemUriVariables(CustomerTransfer $customerTransfer): array
    {
        return [
            CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail(),
            AddressTransfer::UUID => static::UUID,
        ];
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\AddressTransfer> $addressBook
     *
     * @return array<string, mixed>
     */
    protected function stubFacade(CustomerTransfer $customerTransfer, array $addressBook): array
    {
        return [
            'findCustomerByReference' => $this->createCustomerResponse($customerTransfer),
            'getAddressCollection' => fn (AddressCriteriaTransfer $addressCriteriaTransfer): AddressCollectionTransfer => $this->queryAddressBook($addressBook, $addressCriteriaTransfer),
        ];
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\AddressTransfer> $addressBook
     */
    protected function queryAddressBook(
        array $addressBook,
        AddressCriteriaTransfer $addressCriteriaTransfer
    ): AddressCollectionTransfer {
        $addressConditionsTransfer = $addressCriteriaTransfer->getAddressConditions();
        $addressCollectionTransfer = new AddressCollectionTransfer();

        foreach ($addressBook as $addressTransfer) {
            if ($this->matchesConditions($addressTransfer, $addressConditionsTransfer)) {
                $addressCollectionTransfer->addAddress($addressTransfer);
            }
        }

        return $addressCollectionTransfer;
    }

    protected function matchesConditions(
        AddressTransfer $addressTransfer,
        ?AddressConditionsTransfer $addressConditionsTransfer
    ): bool {
        if ($addressConditionsTransfer === null) {
            return true;
        }

        $uuids = $addressConditionsTransfer->getUuids();

        if ($uuids !== [] && !in_array($addressTransfer->getUuid(), $uuids, true)) {
            return false;
        }

        $customerIds = $addressConditionsTransfer->getCustomerIds();

        return $customerIds === [] || in_array($addressTransfer->getFkCustomer(), $customerIds, true);
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\AddressTransfer> $addressTransfers
     *
     * @return array<string, mixed>
     */
    protected function stubFacadeCapturingCriteria(
        CustomerTransfer $customerTransfer,
        array $addressTransfers,
        ?AddressCriteriaTransfer &$capturedCriteria
    ): array {
        $stub = $this->stubFacade($customerTransfer, $addressTransfers);

        $stub['getAddressCollection'] = function (
            AddressCriteriaTransfer $addressCriteriaTransfer
        ) use (
            &$capturedCriteria,
            $addressTransfers,
): AddressCollectionTransfer {
            $capturedCriteria = $addressCriteriaTransfer;

            return $this->queryAddressBook($addressTransfers, $addressCriteriaTransfer);
        };

        return $stub;
    }

    /**
     * @param array<string, mixed> $customerFacadeMethods
     */
    protected function createProvider(array $customerFacadeMethods): CustomerAddressesBackendProvider
    {
        $this->tester->setService(
            CustomerFacadeInterface::class,
            $this->tester->createClientStub(CustomerFacadeInterface::class, $customerFacadeMethods),
        );

        return $this->tester->getProvider(CustomerAddressesBackendProvider::class);
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            class: CustomersAddressesBackendResource::class,
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
