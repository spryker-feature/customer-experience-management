<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship\CompanyBusinessUnitsRelationshipResolver;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyBusinessUnitsRelationshipResolverTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitsRelationshipResolverTest extends BackendApiTestCase
{
    protected const string FIRST_COMPANY_USER_UUID = '0f7c8a1e-3b2d-5c4f-9a6e-1d2b3c4d5e61';

    protected const string SECOND_COMPANY_USER_UUID = '0f7c8a1e-3b2d-5c4f-9a6e-1d2b3c4d5e62';

    protected const string THIRD_COMPANY_USER_UUID = '0f7c8a1e-3b2d-5c4f-9a6e-1d2b3c4d5e63';

    protected const string FIRST_COMPANY_BUSINESS_UNIT_UUID = '5c6d7e8f-9a0b-5c1d-8e2f-3a4b5c6d7e81';

    protected const string SECOND_COMPANY_BUSINESS_UNIT_UUID = '5c6d7e8f-9a0b-5c1d-8e2f-3a4b5c6d7e82';

    protected const string UNKNOWN_COMPANY_BUSINESS_UNIT_UUID = '5c6d7e8f-9a0b-5c1d-8e2f-3a4b5c6d7e89';

    protected const string COMPANY_UUID = '7a1b2c3d-4e5f-5a6b-8c7d-9e0f1a2b3c41';

    protected const string ADDRESS_UUID = '9d8c7b6a-5f4e-5d3c-8b2a-1f0e9d8c7b61';

    protected CustomerExperienceManagementApiTester $tester;

    public function testResolvePerItemLinksEveryParentToItsOwnBusinessUnitWithOneFacadeCall(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver(
            $capturedCriteriaFilters,
            [static::FIRST_COMPANY_BUSINESS_UNIT_UUID, static::SECOND_COMPANY_BUSINESS_UNIT_UUID],
        );

        // Act
        $companyBusinessUnitResourcesByParentId = $resolver->resolvePerItem([
            $this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, static::FIRST_COMPANY_BUSINESS_UNIT_UUID),
            $this->createCompanyUserResource(static::SECOND_COMPANY_USER_UUID, static::SECOND_COMPANY_BUSINESS_UNIT_UUID),
            $this->createCompanyUserResource(static::THIRD_COMPANY_USER_UUID, static::FIRST_COMPANY_BUSINESS_UNIT_UUID),
        ], $this->tester->getContext()->toArray());

        // Assert
        $this->assertCount(1, $capturedCriteriaFilters);
        $this->assertSame(
            [static::FIRST_COMPANY_BUSINESS_UNIT_UUID, static::SECOND_COMPANY_BUSINESS_UNIT_UUID],
            $capturedCriteriaFilters[0]->getUuids(),
        );
        $this->assertSame([
            static::FIRST_COMPANY_USER_UUID => [static::FIRST_COMPANY_BUSINESS_UNIT_UUID],
            static::SECOND_COMPANY_USER_UUID => [static::SECOND_COMPANY_BUSINESS_UNIT_UUID],
            static::THIRD_COMPANY_USER_UUID => [static::FIRST_COMPANY_BUSINESS_UNIT_UUID],
        ], $this->getCompanyBusinessUnitUuidsByParentId($companyBusinessUnitResourcesByParentId));
    }

    public function testResolvePerItemBuildsTheBusinessUnitAsTheBusinessUnitsResourceDoes(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, [static::FIRST_COMPANY_BUSINESS_UNIT_UUID]);

        // Act
        $companyBusinessUnitResourcesByParentId = $resolver->resolvePerItem(
            [$this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, static::FIRST_COMPANY_BUSINESS_UNIT_UUID)],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        /** @var \Generated\Api\Backend\CompanyBusinessUnitsBackendResource $companyBusinessUnitResource */
        $companyBusinessUnitResource = $companyBusinessUnitResourcesByParentId[static::FIRST_COMPANY_USER_UUID][0];
        $this->assertSame(static::COMPANY_UUID, $companyBusinessUnitResource->companyUuid);
        $this->assertSame([static::ADDRESS_UUID], $companyBusinessUnitResource->addressUuids);
    }

    public function testResolvePerItemGivesAParentWithoutABusinessUnitOrWithAnUnknownBusinessUnitAnEmptyList(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, [static::FIRST_COMPANY_BUSINESS_UNIT_UUID]);

        // Act
        $companyBusinessUnitResourcesByParentId = $resolver->resolvePerItem([
            $this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, null),
            $this->createCompanyUserResource(static::SECOND_COMPANY_USER_UUID, static::UNKNOWN_COMPANY_BUSINESS_UNIT_UUID),
        ], $this->tester->getContext()->toArray());

        // Assert
        $this->assertSame([
            static::FIRST_COMPANY_USER_UUID => [],
            static::SECOND_COMPANY_USER_UUID => [],
        ], $this->getCompanyBusinessUnitUuidsByParentId($companyBusinessUnitResourcesByParentId));
    }

    public function testResolvePerItemDoesNotCallTheFacadeWhenNoParentNamesABusinessUnit(): void
    {
        // Arrange
        $capturedCriteriaFilters = [];
        $resolver = $this->createResolver($capturedCriteriaFilters, []);

        // Act
        $resolver->resolvePerItem(
            [$this->createCompanyUserResource(static::FIRST_COMPANY_USER_UUID, null)],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame([], $capturedCriteriaFilters);
    }

    /**
     * @param array<\Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer> $capturedCriteriaFilters
     * @param list<string> $knownCompanyBusinessUnitUuids
     */
    protected function createResolver(
        array &$capturedCriteriaFilters,
        array $knownCompanyBusinessUnitUuids
    ): CompanyBusinessUnitsRelationshipResolver {
        $this->tester->setService(
            CompanyBusinessUnitFacadeInterface::class,
            $this->tester->createClientStub(CompanyBusinessUnitFacadeInterface::class, [
                'getCompanyBusinessUnitCollection' => function (
                    CompanyBusinessUnitCriteriaFilterTransfer $companyBusinessUnitCriteriaFilterTransfer
                ) use (
                    &$capturedCriteriaFilters,
                    $knownCompanyBusinessUnitUuids,
                ): CompanyBusinessUnitCollectionTransfer {
                    $capturedCriteriaFilters[] = $companyBusinessUnitCriteriaFilterTransfer;

                    return $this->createCompanyBusinessUnitCollection(
                        array_intersect($knownCompanyBusinessUnitUuids, $companyBusinessUnitCriteriaFilterTransfer->getUuids()),
                        $companyBusinessUnitCriteriaFilterTransfer->getWithoutExpanders() !== true,
                    );
                },
            ]),
        );

        return $this->tester->getRelationshipResolver(CompanyBusinessUnitsRelationshipResolver::class);
    }

    /**
     * @param array<string> $companyBusinessUnitUuids
     */
    protected function createCompanyBusinessUnitCollection(
        array $companyBusinessUnitUuids,
        bool $isExpanded
    ): CompanyBusinessUnitCollectionTransfer {
        $companyBusinessUnitCollectionTransfer = new CompanyBusinessUnitCollectionTransfer();

        foreach (array_values($companyBusinessUnitUuids) as $index => $companyBusinessUnitUuid) {
            $companyBusinessUnitTransfer = (new CompanyBusinessUnitTransfer())
                ->setIdCompanyBusinessUnit($index + 1)
                ->setUuid($companyBusinessUnitUuid)
                ->setName($companyBusinessUnitUuid)
                ->setCompany((new CompanyTransfer())->setUuid(static::COMPANY_UUID));

            if ($isExpanded) {
                $companyBusinessUnitTransfer->setAddressCollection(
                    (new CompanyUnitAddressCollectionTransfer())
                        ->addCompanyUnitAddress((new CompanyUnitAddressTransfer())->setUuid(static::ADDRESS_UUID)),
                );
            }

            $companyBusinessUnitCollectionTransfer->addCompanyBusinessUnit($companyBusinessUnitTransfer);
        }

        return $companyBusinessUnitCollectionTransfer;
    }

    protected function createCompanyUserResource(string $uuid, ?string $companyBusinessUnitUuid): CompanyUsersBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyUsersBackendResource $companyUserResource */
        $companyUserResource = $this->tester->getResource(CompanyUsersBackendResource::class, [
            'uuid' => $uuid,
            'companyBusinessUnitUuid' => $companyBusinessUnitUuid,
        ]);

        return $companyUserResource;
    }

    /**
     * @param array<string, array<object>> $companyBusinessUnitResourcesByParentId
     *
     * @return array<string, list<string>>
     */
    protected function getCompanyBusinessUnitUuidsByParentId(array $companyBusinessUnitResourcesByParentId): array
    {
        return array_map(
            fn (array $companyBusinessUnitResources): array => array_map(
                fn (CompanyBusinessUnitsBackendResource $companyBusinessUnitResource): string => (string)$companyBusinessUnitResource->uuid,
                $companyBusinessUnitResources,
            ),
            $companyBusinessUnitResourcesByParentId,
        );
    }
}
