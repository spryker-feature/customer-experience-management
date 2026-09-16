<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\ApiPlatform\ResponseTransform\PaginationLinksTransform;
use Spryker\Zed\CompanyUser\CompanyUserConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CompanyUsersBackendProvider;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUserReader;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyUsersBackendProviderTest
 * Add your own group annotations below this line
 */
class CompanyUsersBackendProviderTest extends BackendApiTestCase
{
    protected const string UUID = '85fc827d-0d09-5d9b-a30f-a9beada10372';

    protected const string COMPANY_UUID = '0818f408-cc84-575d-ad54-92118a0e4273';

    protected const string COMPANY_BUSINESS_UNIT_UUID = 'b8a06475-73f5-575a-b1e9-1954de7a49ef';

    protected const string COMPANY_ROLE_UUID = '2f0a9d3e-9e69-53eb-8518-284a0db04376';

    protected const string CUSTOMER_REFERENCE = 'DE--1';

    protected const int ID_COMPANY = 11;

    protected const int ID_COMPANY_BUSINESS_UNIT = 22;

    protected const int ITEMS_PER_PAGE = 10;

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideItemReturnsTheCompanyUserAddressedByTheUuid(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CompanyUsersBackendResource::class),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CompanyUsersBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertSame(static::CUSTOMER_REFERENCE, $resource->customerReference);
        $this->assertSame(static::COMPANY_UUID, $resource->companyUuid);
        $this->assertSame(static::COMPANY_BUSINESS_UNIT_UUID, $resource->companyBusinessUnitUuid);
        $this->assertSame([static::COMPANY_ROLE_UUID], $resource->companyRoleUuids);
    }

    public function testProvideCollectionAppliesTheCustomerReferenceFilter(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext($this->createRequestWithFilter(
                'company-users.customerReference',
                static::CUSTOMER_REFERENCE,
            )),
        );

        // Assert
        $this->assertSame(
            [static::CUSTOMER_REFERENCE],
            $capturedCriteriaFilterTransfer->getCustomerReferences(),
        );
    }

    public function testProvideCollectionResolvesTheCompanyUuidFilterToAnId(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext($this->createRequestWithFilter(
                'company-users.companyUuid',
                static::COMPANY_UUID,
            )),
        );

        // Assert
        $this->assertSame(static::ID_COMPANY, $capturedCriteriaFilterTransfer->getIdCompany());
    }

    public function testProvideCollectionResolvesTheBusinessUnitUuidFilterToAnId(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext($this->createRequestWithFilter(
                'company-users.companyBusinessUnitUuid',
                static::COMPANY_BUSINESS_UNIT_UUID,
            )),
        );

        // Assert
        $this->assertSame(
            static::ID_COMPANY_BUSINESS_UNIT,
            $capturedCriteriaFilterTransfer->getIdCompanyBusinessUnit(),
        );
    }

    public function testProvideCollectionAppliesTheIsActiveFilterIncludingFalse(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext($this->createRequestWithFilter(
                'company-users.isActive',
                'false',
            )),
        );

        // Assert
        $this->assertFalse(
            $capturedCriteriaFilterTransfer->getIsActive(),
            'A "false" filter value must narrow to disabled company users, not be discarded as empty.',
        );
    }

    public function testProvideCollectionMapsTheFreeTextSearchToTheCustomerNameCriteria(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(new Request(['q' => 'Hopkin'])),
        );

        // Assert
        $this->assertSame('Hopkin', $capturedCriteriaFilterTransfer->getCustomerName());
    }

    public function testProvideCollectionForwardsTheSortFieldFromTheAllowList(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(new Request(['sort' => '-' . CompanyUserConfig::SORT_FIELD_CUSTOMER_LAST_NAME])),
        );

        // Assert
        $sortTransfers = iterator_to_array($capturedCriteriaFilterTransfer->getSortCollection());

        $this->assertCount(1, $sortTransfers);
        $this->assertSame(
            CompanyUserConfig::SORT_FIELD_CUSTOMER_LAST_NAME,
            $sortTransfers[0]->getField(),
            'The public sort field is forwarded as-is; the repository resolves it through the sortable field map.',
        );
        $this->assertFalse($sortTransfers[0]->getIsAscending());
    }

    public function testProvideCollectionForwardsEverySortFieldInTheRequestedOrder(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);
        $sort = sprintf(
            '%s,-%s',
            CompanyUserConfig::SORT_FIELD_CUSTOMER_FIRST_NAME,
            CompanyUserConfig::SORT_FIELD_CUSTOMER_LAST_NAME,
        );

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(new Request(['sort' => $sort])),
        );

        // Assert
        $sortTransfers = iterator_to_array($capturedCriteriaFilterTransfer->getSortCollection());

        $this->assertSame(
            [CompanyUserConfig::SORT_FIELD_CUSTOMER_FIRST_NAME, CompanyUserConfig::SORT_FIELD_CUSTOMER_LAST_NAME],
            array_map(static fn ($sortTransfer): ?string => $sortTransfer->getField(), $sortTransfers),
            'A comma-separated sort keeps every field, in the order it was requested.',
        );
        $this->assertTrue($sortTransfers[0]->getIsAscending());
        $this->assertFalse($sortTransfers[1]->getIsAscending());
    }

    public function testProvideCollectionPublishesTheCollectionPaginationOnTheRequest(): void
    {
        // Arrange
        $capturedCriteriaFilterTransfer = null;
        $provider = $this->createProvider($capturedCriteriaFilterTransfer);
        $request = new Request();

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext($request),
        );

        // Assert
        $this->assertCount(1, $resources);
        $this->assertSame(
            ['numFound' => 1, 'currentPage' => 1, 'maxPage' => 1, 'currentItemsPerPage' => 10],
            $request->attributes->get(PaginationLinksTransform::REQUEST_ATTRIBUTE_PAGINATION),
            'Collection metadata is published on the request, for the document meta - not onto a resource.',
        );
    }

    protected function createProvider(mixed &$capturedCriteriaFilterTransfer = null): CompanyUsersBackendProvider
    {
        $companyUserTransfer = $this->createCompanyUserTransfer();

        $this->tester->setService(
            CompanyUserReader::class,
            $this->tester->createClientStub(CompanyUserReader::class, [
                'getCompanyUserByUuid' => $companyUserTransfer,
                'getCompanyUserCollection' => function (
                    CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
                ) use (
                    &$capturedCriteriaFilterTransfer,
                    $companyUserTransfer,
                ): CompanyUserCollectionTransfer {
                    $capturedCriteriaFilterTransfer = $companyUserCriteriaFilterTransfer;

                    return (new CompanyUserCollectionTransfer())
                        ->addCompanyUser($companyUserTransfer)
                        ->setPagination((new PaginationTransfer())->setNbResults(1));
                },
                'findIdCompanyByUuid' => static::ID_COMPANY,
                'findIdCompanyBusinessUnitByUuid' => static::ID_COMPANY_BUSINESS_UNIT,
            ]),
        );

        return $this->tester->getProvider(CompanyUsersBackendProvider::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function createContext(Request $request): array
    {
        return $this->tester->getContext(['request' => $request])->toArray();
    }

    protected function createRequestWithFilter(string $key, string $value): Request
    {
        return new Request(['filter' => [$key => $value]]);
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            class: CompanyUsersBackendResource::class,
            paginationItemsPerPage: static::ITEMS_PER_PAGE,
        );
    }

    protected function createCompanyUserTransfer(): CompanyUserTransfer
    {
        return (new CompanyUserTransfer())
            ->setUuid(static::UUID)
            ->setIsActive(true)
            ->setIsDefault(false)
            ->setCustomer((new CustomerTransfer())->setCustomerReference(static::CUSTOMER_REFERENCE))
            ->setCompany((new CompanyTransfer())->setUuid(static::COMPANY_UUID))
            ->setCompanyBusinessUnit(
                (new CompanyBusinessUnitTransfer())->setUuid(static::COMPANY_BUSINESS_UNIT_UUID),
            )
            ->setCompanyRoleCollection(
                (new CompanyRoleCollectionTransfer())
                    ->addRole((new CompanyRoleTransfer())->setUuid(static::COMPANY_ROLE_UUID)),
            );
    }
}
