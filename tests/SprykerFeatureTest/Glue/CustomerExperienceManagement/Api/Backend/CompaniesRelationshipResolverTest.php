<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CompaniesBackendResource;
use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyCollectionTransfer;
use Generated\Shared\Transfer\CompanyCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship\CompaniesRelationshipResolver;
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
 * @group CompaniesRelationshipResolverTest
 * Add your own group annotations below this line
 */
class CompaniesRelationshipResolverTest extends BackendApiTestCase
{
    protected const string FIRST_COMPANY_USER_UUID = '0f7c8a1e-3b2d-5c4f-9a6e-1d2b3c4d5e61';

    protected const string SECOND_COMPANY_USER_UUID = '0f7c8a1e-3b2d-5c4f-9a6e-1d2b3c4d5e62';

    protected const string THIRD_COMPANY_USER_UUID = '0f7c8a1e-3b2d-5c4f-9a6e-1d2b3c4d5e63';

    protected const string FIRST_COMPANY_UUID = '7a1b2c3d-4e5f-5a6b-8c7d-9e0f1a2b3c41';

    protected const string SECOND_COMPANY_UUID = '7a1b2c3d-4e5f-5a6b-8c7d-9e0f1a2b3c42';

    protected const string UNKNOWN_COMPANY_UUID = '7a1b2c3d-4e5f-5a6b-8c7d-9e0f1a2b3c49';

    protected const string COMPANY_STATUS_APPROVED = 'approved';

    protected CustomerExperienceManagementApiTester $tester;

    public function testResolvePerItemLinksEveryParentToItsOwnCompanyWithOneFacadeCall(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, [static::FIRST_COMPANY_UUID, static::SECOND_COMPANY_UUID]);

        // Act
        $companyResourcesByParentId = $resolver->resolvePerItem([
            $this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, static::FIRST_COMPANY_UUID),
            $this->createCompanyUserResource(static::SECOND_COMPANY_USER_UUID, static::SECOND_COMPANY_UUID),
            $this->createCompanyUserResource(static::THIRD_COMPANY_USER_UUID, static::FIRST_COMPANY_UUID),
        ], $this->tester->getContext()->toArray());

        // Assert
        $this->assertCount(1, $capturedCriteriaFilters);
        $this->assertSame([static::FIRST_COMPANY_UUID, static::SECOND_COMPANY_UUID], $capturedCriteriaFilters[0]->getUuids());
        $this->assertSame([
            static::FIRST_COMPANY_USER_UUID => [static::FIRST_COMPANY_UUID],
            static::SECOND_COMPANY_USER_UUID => [static::SECOND_COMPANY_UUID],
            static::THIRD_COMPANY_USER_UUID => [static::FIRST_COMPANY_UUID],
        ], $this->getCompanyUuidsByParentId($companyResourcesByParentId));
    }

    public function testResolvePerItemGivesAParentWithoutACompanyOrWithAnUnknownCompanyAnEmptyList(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, [static::FIRST_COMPANY_UUID]);

        // Act
        $companyResourcesByParentId = $resolver->resolvePerItem([
            $this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, null),
            $this->createCompanyUserResource(static::SECOND_COMPANY_USER_UUID, static::UNKNOWN_COMPANY_UUID),
        ], $this->tester->getContext()->toArray());

        // Assert
        $this->assertSame([
            static::FIRST_COMPANY_USER_UUID => [],
            static::SECOND_COMPANY_USER_UUID => [],
        ], $this->getCompanyUuidsByParentId($companyResourcesByParentId));
    }

    public function testResolvePerItemDoesNotCallTheFacadeWhenNoParentNamesACompany(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, []);

        // Act
        $companyResourcesByParentId = $resolver->resolvePerItem(
            [$this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, null)],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([], $capturedCriteriaFilters);
        $this->assertSame([static::FIRST_COMPANY_USER_UUID => []], $this->getCompanyUuidsByParentId($companyResourcesByParentId));
    }

    public function testResolveReturnsEveryLinkedCompanyOnce(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, [static::FIRST_COMPANY_UUID, static::SECOND_COMPANY_UUID]);

        // Act
        $companyResources = $resolver->resolve([
            $this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, static::FIRST_COMPANY_UUID),
            $this->createCompanyUserResource(static::SECOND_COMPANY_USER_UUID, static::SECOND_COMPANY_UUID),
            $this->createCompanyUserResource(static::THIRD_COMPANY_USER_UUID, static::FIRST_COMPANY_UUID),
        ], $this->tester->getContext()->toArray());

        // Assert
        $this->assertSame(
            [static::FIRST_COMPANY_UUID, static::SECOND_COMPANY_UUID],
            array_map(fn (CompaniesBackendResource $companyResource): string => (string)$companyResource->uuid, $companyResources),
        );
    }

    public function testResolvePerItemIgnoresTheParentRequestQuery(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, [static::FIRST_COMPANY_UUID]);
        $request = new Request([
            'page' => ['offset' => '20', 'limit' => '5'],
            'sort' => 'name',
            'q' => 'acme',
            'filter' => ['companies.name' => 'acme'],
        ]);

        // Act
        $resolver->resolvePerItem(
            [$this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, static::FIRST_COMPANY_UUID)],
            $this->tester->getContext(['request' => $request])->toArray(),
        );

        // Assert
        $this->assertNull($capturedCriteriaFilters[0]->getPagination());
        $this->assertCount(0, $capturedCriteriaFilters[0]->getSortCollection());
        $this->assertNull($capturedCriteriaFilters[0]->getName());
    }

    /**
     * @param array<\Generated\Shared\Transfer\CompanyCriteriaFilterTransfer> $capturedCriteriaFilters
     * @param list<string> $knownCompanyUuids
     */
    protected function createResolver(array &$capturedCriteriaFilters, array $knownCompanyUuids): CompaniesRelationshipResolver
    {
        $this->tester->setService(
            CompanyFacadeInterface::class,
            $this->tester->createClientStub(CompanyFacadeInterface::class, [
                'getCompanyCollection' => function (CompanyCriteriaFilterTransfer $companyCriteriaFilterTransfer) use (&$capturedCriteriaFilters, $knownCompanyUuids): CompanyCollectionTransfer {
                    $capturedCriteriaFilters[] = $companyCriteriaFilterTransfer;

                    return $this->createCompanyCollection(array_intersect($knownCompanyUuids, $companyCriteriaFilterTransfer->getUuids()));
                },
            ]),
        );

        return $this->tester->getRelationshipResolver(CompaniesRelationshipResolver::class);
    }

    /**
     * @param array<string> $companyUuids
     */
    protected function createCompanyCollection(array $companyUuids): CompanyCollectionTransfer
    {
        $companyCollectionTransfer = new CompanyCollectionTransfer();

        foreach (array_values($companyUuids) as $index => $companyUuid) {
            $companyCollectionTransfer->addCompany(
                (new CompanyTransfer())
                    ->setIdCompany($index + 1)
                    ->setUuid($companyUuid)
                    ->setName($companyUuid)
                    ->setStatus(static::COMPANY_STATUS_APPROVED)
                    ->setIsActive(true),
            );
        }

        return $companyCollectionTransfer;
    }

    protected function createCompanyUserResource(string $uuid, ?string $companyUuid): CompanyUsersBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyUsersBackendResource $companyUserResource */
        $companyUserResource = $this->tester->getResource(CompanyUsersBackendResource::class, [
            'uuid' => $uuid,
            'companyUuid' => $companyUuid,
        ]);

        return $companyUserResource;
    }

    /**
     * @param array<string, array<object>> $companyResourcesByParentId
     *
     * @return array<string, list<string>>
     */
    protected function getCompanyUuidsByParentId(array $companyResourcesByParentId): array
    {
        return array_map(
            fn (array $companyResources): array => array_map(
                fn (CompaniesBackendResource $companyResource): string => (string)$companyResource->uuid,
                $companyResources,
            ),
            $companyResourcesByParentId,
        );
    }
}
