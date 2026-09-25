<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CompanyRolesBackendResource;
use Generated\Shared\Transfer\CompanyRoleCollectionCriteriaTransfer;
use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Generated\Shared\Transfer\PermissionTransfer;
use Spryker\ApiPlatform\ResponseTransform\PaginationLinksTransform;
use Spryker\Zed\CompanyRole\Business\CompanyRoleFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CompanyRolesBackendProvider;
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
 * @group CompanyRolesBackendProviderTest
 * Add your own group annotations below this line
 */
class CompanyRolesBackendProviderTest extends BackendApiTestCase
{
    /**
     * @var string
     */
    protected const ROLE_UUID = '50c647a4-d27f-5d82-a587-1d0b7cc6b58d';

    /**
     * @var string
     */
    protected const COMPANY_UUID = 'a0e4d1c8-6b47-5f19-9c2d-3f8b1e7a5d40';

    /**
     * @var string
     */
    protected const PERMISSION_KEY = 'ApproveQuotePermissionPlugin';

    /**
     * @var int
     */
    protected const ITEMS_PER_PAGE = 10;

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideItemReturnsTheRoleAddressedByUuid(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(),
            ['uuid' => static::ROLE_UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CompanyRolesBackendResource::class, $resource);
        $this->assertSame(static::ROLE_UUID, $resource->uuid);
        $this->assertSame(static::COMPANY_UUID, $resource->companyUuid);
    }

    /**
     * The role's own permissions come back as keys so a client can mark them against the catalogue
     * returned by `GET /company-role-permissions`.
     */
    public function testProvideItemReturnsTheAssignedPermissionsAsUuids(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(),
            ['uuid' => static::ROLE_UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([static::PERMISSION_KEY], $resource->permissionKeys);
    }

    public function testProvideItemThrowsNotFoundForAnUnknownUuid(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'findCompanyRoleByUuid' => (new CompanyRoleResponseTransfer())->setIsSuccessful(false),
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $provider->provide(
                $this->tester->getGetOperation(),
                ['uuid' => static::ROLE_UUID],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProvideCollectionTranslatesTheRequestFiltersIntoConditions(): void
    {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext([
                'filter' => [
                    'company-roles.name' => 'approver',
                    'company-roles.companyName' => 'acme',
                    'company-roles.companyUuid' => static::COMPANY_UUID,
                    'company-roles.isDefault' => 'true',
                ],
            ]),
        );

        // Assert
        $conditions = $capturedCriteria->getCompanyRoleConditions();
        $this->assertSame('approver', $conditions->getName());
        $this->assertSame('acme', $conditions->getCompanyName());
        $this->assertSame([static::COMPANY_UUID], $conditions->getCompanyUuids());
        $this->assertTrue($conditions->getIsDefault());
    }

    /**
     * `q` spans the role and company names together, which is what separates it from the narrower
     * name filter.
     */
    public function testProvideCollectionTranslatesTheFreeTextQueryIntoASearchTerm(): void
    {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(['q' => 'vanguard']),
        );

        // Assert
        $this->assertSame('vanguard', $capturedCriteria->getCompanyRoleConditions()->getSearchTerm());
    }

    public function testProvideCollectionPassesThePageWindowThroughAsLimitAndOffset(): void
    {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(['page' => ['offset' => 20, 'limit' => 5]]),
        );

        // Assert
        $this->assertSame(20, $capturedCriteria->getPagination()->getOffset());
        $this->assertSame(5, $capturedCriteria->getPagination()->getLimit());
    }

    /**
     * @dataProvider provideSupportedSortValues
     */
    public function testProvideCollectionTranslatesASupportedSortIntoOrderingCriteria(
        string $sort,
        string $expectedField,
        bool $expectedIsAscending
    ): void {
        // Arrange
        $capturedCriteria = null;
        $provider = $this->createProviderCapturingCriteria($capturedCriteria);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(['sort' => $sort]),
        );

        // Assert
        $sortTransfers = $capturedCriteria->getSortCollection();
        $this->assertCount(1, $sortTransfers);
        $this->assertSame($expectedField, $sortTransfers[0]->getField());
        $this->assertSame($expectedIsAscending, $sortTransfers[0]->getIsAscending());
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public function provideSupportedSortValues(): array
    {
        return [
            'ascending name' => ['name', 'name', true],
            'descending company name' => ['-companyName', 'companyName', false],
            'ascending default flag' => ['isDefault', 'isDefault', true],
        ];
    }

    public function testProvideCollectionRejectsASortFieldOutsideTheAllowList(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_BAD_REQUEST,
            fn () => $provider->provide(
                $this->createGetCollectionOperation(),
                [],
                $this->createContext(['sort' => 'idCompanyRole']),
            ),
        );
    }

    public function testProvideCollectionPublishesTheTotalForThePaginationLinks(): void
    {
        // Arrange
        $request = new Request(['page' => ['offset' => 0, 'limit' => 10]]);
        $provider = $this->createProvider([
            'getCompanyRoleCollectionByCollectionCriteria' => (new CompanyRoleCollectionTransfer())
                ->addRole($this->createStoredCompanyRole())
                ->setPagination((new PaginationTransfer())->setNbResults(42)),
        ]);

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->tester->getContext(['request' => $request])->toArray(),
        );

        // Assert
        $pagination = $request->attributes->get(PaginationLinksTransform::REQUEST_ATTRIBUTE_PAGINATION);
        $this->assertSame(42, $pagination['numFound']);
    }

    public function testProvideCollectionReturnsAnEmptyArrayWhenNothingMatches(): void
    {
        // Arrange
        $provider = $this->createProvider([
            'getCompanyRoleCollectionByCollectionCriteria' => new CompanyRoleCollectionTransfer(),
        ]);

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(),
        );

        // Assert
        $this->assertSame([], $resources);
    }

    protected function createProviderCapturingCriteria(
        ?CompanyRoleCollectionCriteriaTransfer &$capturedCriteria
    ): CompanyRolesBackendProvider {
        return $this->createProvider([
            'getCompanyRoleCollectionByCollectionCriteria' => function (
                CompanyRoleCollectionCriteriaTransfer $companyRoleCollectionCriteriaTransfer
            ) use (&$capturedCriteria): CompanyRoleCollectionTransfer {
                $capturedCriteria = $companyRoleCollectionCriteriaTransfer;

                return new CompanyRoleCollectionTransfer();
            },
        ]);
    }

    /**
     * @param array<string, mixed> $companyRoleFacadeMethods
     */
    protected function createProvider(array $companyRoleFacadeMethods = []): CompanyRolesBackendProvider
    {
        $this->tester->setService(
            CompanyRoleFacadeInterface::class,
            $this->tester->createClientStub(CompanyRoleFacadeInterface::class, $companyRoleFacadeMethods + [
                'findCompanyRoleByUuid' => (new CompanyRoleResponseTransfer())
                    ->setIsSuccessful(true)
                    ->setCompanyRoleTransfer($this->createStoredCompanyRole()),
                'findCompanyRolePermissions' => (new PermissionCollectionTransfer())->addPermission(
                    (new PermissionTransfer())->setIdPermission(3)->setKey(static::PERMISSION_KEY),
                ),
                'getCompanyRoleCollectionByCollectionCriteria' => new CompanyRoleCollectionTransfer(),
            ]),
        );

        return $this->tester->getProvider(CompanyRolesBackendProvider::class);
    }

    protected function createStoredPermissionCollection(): PermissionCollectionTransfer
    {
        return (new PermissionCollectionTransfer())->addPermission(
            (new PermissionTransfer())->setIdPermission(3)->setKey(static::PERMISSION_KEY),
        );
    }

    protected function createStoredCompanyRole(): CompanyRoleTransfer
    {
        return (new CompanyRoleTransfer())
            ->setIdCompanyRole(7)
            ->setUuid(static::ROLE_UUID)
            ->setName('Approver')
            ->setIsDefault(false)
            ->setFkCompany(41)
            ->setCompany((new CompanyTransfer())->setUuid(static::COMPANY_UUID)->setName('Acme Corporation'))
            ->setPermissionCollection($this->createStoredPermissionCollection());
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            class: CompanyRolesBackendResource::class,
            paginationItemsPerPage: static::ITEMS_PER_PAGE,
        );
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    protected function createContext(array $query = []): array
    {
        return $this->tester->getContext(['request' => new Request($query)])->toArray();
    }
}
