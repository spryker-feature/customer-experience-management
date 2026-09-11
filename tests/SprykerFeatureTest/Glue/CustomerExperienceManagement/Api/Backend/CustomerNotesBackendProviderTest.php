<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CustomersNotesBackendResource;
use Generated\Shared\Transfer\CustomerNoteCollectionTransfer;
use Generated\Shared\Transfer\CustomerNoteConditionsTransfer;
use Generated\Shared\Transfer\CustomerNoteCriteriaTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;
use Spryker\ApiPlatform\ResponseTransform\PaginationLinksTransform;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\CustomerNote\Business\CustomerNoteFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CustomerNotesBackendProvider;
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
 * @group CustomerNotesBackendProviderTest
 * Add your own group annotations below this line
 */
class CustomerNotesBackendProviderTest extends BackendApiTestCase
{
    protected const string UNKNOWN_CUSTOMER_REFERENCE = 'unknown-customer-reference';

    protected const string UUID = 'b1f7c3d2-8a41-5c6e-9d70-2e5b8f0a4c31';

    protected const string OTHER_UUID = 'e4a6bd10-2c55-5f31-9a08-77c31de0bb42';

    protected const int ID_OTHER_CUSTOMER = 99;

    protected const string MESSAGE = 'Called the customer about invoice 4711.';

    protected const string USERNAME = 'Admin Spryker';

    protected const int ITEMS_PER_PAGE = 25;

    protected const string URI_TEMPLATE_COLLECTION = '/customers/{customerReference}/notes';

    protected const string SORTABLE_FIELD = 'createdAt';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideCollectionReturnsTheNotesOfTheCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $provider = $this->createProvider($this->stubFacades($customerTransfer, [
            $this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail()),
            $this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail(), static::OTHER_UUID),
        ]));

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertCount(2, $resources);
        $this->assertSame(static::UUID, $resources[0]->uuid);
        $this->assertSame($customerTransfer->getCustomerReferenceOrFail(), $resources[0]->customerReference);
        $this->assertSame(static::MESSAGE, $resources[0]->message);
        $this->assertSame(static::USERNAME, $resources[0]->username);
    }

    public function testProvideCollectionReturnsEmptyArrayWhenTheCustomerHasNoNotes(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $provider = $this->createProvider($this->stubFacades($customerTransfer, []));

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([], $resources);
    }

    public function testProvideCollectionScopesTheCriteriaToTheOwningCustomerOnly(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadesCapturingCriteria(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail())],
            $capturedCriteria,
        ));

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomerNoteCriteriaTransfer::class, $capturedCriteria);
        $this->assertSame(
            [$customerTransfer->getIdCustomerOrFail()],
            $capturedCriteria->getCustomerNoteConditionsOrFail()->getCustomerIds(),
            'The owning customer must be part of the query conditions, not filtered afterwards.',
        );
    }

    public function testProvideCollectionPublishesTopLevelPaginationFromTheResultTotal(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $customerNoteCollectionTransfer = (new CustomerNoteCollectionTransfer())
            ->addNotes($this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail()))
            ->addNotes($this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail(), static::OTHER_UUID))
            ->setPagination(
                (new PaginationTransfer())->setNbResults(7),
            );
        $request = new Request(['page' => ['limit' => '2', 'offset' => '2']]);

        $provider = $this->createProvider([
            'findCustomerByReference' => $this->createCustomerResponse($customerTransfer),
            'getCustomerNoteCollection' => $customerNoteCollectionTransfer,
        ]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
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

        $provider = $this->createProvider($this->stubFacadesCapturingCriteria(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail())],
            $capturedCriteria,
        ));

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
    public function paginationQueryParametersDataProvider(): array
    {
        return [
            'no pagination falls back to the operation default' => [[], 1, static::ITEMS_PER_PAGE],
            'a scalar page number is not part of the page window and is ignored' => [['page' => '3'], 1, static::ITEMS_PER_PAGE],
            'a non-numeric page number does not fail the request' => [['page' => 'abc'], 1, static::ITEMS_PER_PAGE],
            'the JSON:API item window' => [['page' => ['limit' => '5', 'offset' => '10']], 3, 5],
            'a limit without an offset starts at the first page' => [['page' => ['limit' => '5']], 1, 5],
        ];
    }

    public function testProvideCollectionAcceptsAWhitelistedSortField(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadesCapturingCriteria(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail())],
            $capturedCriteria,
        ));

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithQuery(['sort' => '-' . static::SORTABLE_FIELD]),
        );

        // Assert
        $sortTransfers = $capturedCriteria->getSortCollection();

        $this->assertCount(1, $sortTransfers);
        $this->assertSame(static::SORTABLE_FIELD, $sortTransfers->offsetGet(0)->getField());
        $this->assertFalse($sortTransfers->offsetGet(0)->getIsAscending());
    }

    /**
     * @dataProvider rejectedSortFieldDataProvider
     */
    public function testProvideCollectionRejectsASortFieldOutsideTheWhitelist(string $sortField): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $provider = $this->createProvider($this->stubFacades(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail())],
        ));

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_BAD_REQUEST,
            fn () => $provider->provide(
                $this->createGetCollectionOperation(),
                [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
                $this->createContextWithQuery(['sort' => $sortField]),
            ),
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function rejectedSortFieldDataProvider(): array
    {
        return [
            'a readable field that is not sortable' => ['message'],
            'an unknown field' => ['nope'],
            'a column expression' => ['spy_customer_note.id_customer_note'],
            'an injection attempt' => ['message; DROP TABLE spy_customer_note; --'],
        ];
    }

    public function testProvideCollectionThrowsNotFoundForAnUnknownCustomerWithoutReadingNotes(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
            'getCustomerNoteCollection' => function (): CustomerNoteCollectionTransfer {
                $this->fail('The note collection must not be read for an unknown customer reference.');
            },
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

    public function testProvideItemReturnsTheNoteOfTheCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $provider = $this->createProvider($this->stubFacades(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail())],
        ));

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomersNotesBackendResource::class),
            [
                CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail(),
                SpyCustomerNoteEntityTransfer::UUID => static::UUID,
            ],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomersNotesBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
    }

    public function testProvideItemThrowsNotFoundWhenTheNoteBelongsToAnotherCustomer(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $provider = $this->createProvider($this->stubFacades(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer(static::ID_OTHER_CUSTOMER, static::UUID)],
        ));

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $provider->provide(
                $this->tester->getGetOperation(CustomersNotesBackendResource::class),
                [
                    CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail(),
                    SpyCustomerNoteEntityTransfer::UUID => static::UUID,
                ],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProvideCollectionForARelationshipIgnoresTheParentRequestQuery(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedCriteria = null;

        $provider = $this->createProvider($this->stubFacadesCapturingCriteria(
            $customerTransfer,
            [$this->createCustomerNoteEntityTransfer($customerTransfer->getIdCustomerOrFail())],
            $capturedCriteria,
        ));

        // Act
        $resources = $provider->provide(
            new GetCollection(class: CustomersNotesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithQuery(['sort' => 'email', 'page' => '3']),
        );

        // Assert
        $this->assertCount(1, $resources, 'A sort field the notes resource does not support must not drop the relationship.');
        $this->assertCount(
            0,
            $capturedCriteria->getSortCollection(),
            "The parent's sort must not be applied to the included notes.",
        );
        $this->assertSame(
            1,
            $capturedCriteria->getPaginationOrFail()->getPage(),
            "The parent's page must not be applied to the included notes.",
        );
    }

    /**
     * @param array<string, mixed> $facadeMethods
     */
    protected function createProvider(array $facadeMethods): CustomerNotesBackendProvider
    {
        $this->tester->setService(
            CustomerFacadeInterface::class,
            $this->tester->createClientStub(CustomerFacadeInterface::class, [
                'findCustomerByReference' => $facadeMethods['findCustomerByReference'],
            ]),
        );
        $this->tester->setService(
            CustomerNoteFacadeInterface::class,
            $this->tester->createClientStub(CustomerNoteFacadeInterface::class, [
                'getCustomerNoteCollection' => $facadeMethods['getCustomerNoteCollection'],
            ]),
        );

        return $this->tester->getProvider(CustomerNotesBackendProvider::class);
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            uriTemplate: static::URI_TEMPLATE_COLLECTION,
            class: CustomersNotesBackendResource::class,
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

    /**
     * @param array<\Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer> $noteBook
     *
     * @return array<string, mixed>
     */
    protected function stubFacades(CustomerTransfer $customerTransfer, array $noteBook): array
    {
        return [
            'findCustomerByReference' => $this->createCustomerResponse($customerTransfer),
            'getCustomerNoteCollection' => fn (CustomerNoteCriteriaTransfer $customerNoteCriteriaTransfer): CustomerNoteCollectionTransfer => $this->queryNoteBook($noteBook, $customerNoteCriteriaTransfer),
        ];
    }

    /**
     * @param array<\Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer> $noteBook
     *
     * @return array<string, mixed>
     */
    protected function stubFacadesCapturingCriteria(
        CustomerTransfer $customerTransfer,
        array $noteBook,
        ?CustomerNoteCriteriaTransfer &$capturedCriteria
    ): array {
        return [
            'findCustomerByReference' => $this->createCustomerResponse($customerTransfer),
            'getCustomerNoteCollection' => function (CustomerNoteCriteriaTransfer $customerNoteCriteriaTransfer) use ($noteBook, &$capturedCriteria): CustomerNoteCollectionTransfer {
                $capturedCriteria = $customerNoteCriteriaTransfer;

                return $this->queryNoteBook($noteBook, $customerNoteCriteriaTransfer);
            },
        ];
    }

    /**
     * Mirrors the repository's condition handling so the stub honours the scoping the provider builds.
     *
     * @param array<\Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer> $noteBook
     */
    protected function queryNoteBook(
        array $noteBook,
        CustomerNoteCriteriaTransfer $customerNoteCriteriaTransfer
    ): CustomerNoteCollectionTransfer {
        $customerNoteCollectionTransfer = new CustomerNoteCollectionTransfer();
        $conditionsTransfer = $customerNoteCriteriaTransfer->getCustomerNoteConditions();

        foreach ($noteBook as $customerNoteEntityTransfer) {
            if ($this->matchesConditions($customerNoteEntityTransfer, $conditionsTransfer)) {
                $customerNoteCollectionTransfer->addNotes($customerNoteEntityTransfer);
            }
        }

        return $customerNoteCollectionTransfer->setPagination($customerNoteCriteriaTransfer->getPagination());
    }

    protected function matchesConditions(
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer,
        ?CustomerNoteConditionsTransfer $customerNoteConditionsTransfer
    ): bool {
        if ($customerNoteConditionsTransfer === null) {
            return true;
        }

        $customerIds = $customerNoteConditionsTransfer->getCustomerIds();

        if ($customerIds !== [] && !in_array($customerNoteEntityTransfer->getFkCustomer(), $customerIds, true)) {
            return false;
        }

        $uuids = $customerNoteConditionsTransfer->getUuids();

        return $uuids === [] || in_array($customerNoteEntityTransfer->getUuid(), $uuids, true);
    }

    protected function createCustomerResponse(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return (new CustomerResponseTransfer())
            ->setHasCustomer(true)
            ->setCustomerTransfer($customerTransfer);
    }

    protected function createCustomerNoteEntityTransfer(
        int $fkCustomer,
        string $uuid = self::UUID
    ): SpyCustomerNoteEntityTransfer {
        return (new SpyCustomerNoteEntityTransfer())
            ->setUuid($uuid)
            ->setFkCustomer($fkCustomer)
            ->setMessage(static::MESSAGE)
            ->setUsername(static::USERNAME);
    }
}
