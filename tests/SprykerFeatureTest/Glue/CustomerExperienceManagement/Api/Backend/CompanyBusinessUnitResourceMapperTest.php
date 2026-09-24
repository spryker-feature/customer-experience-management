<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Stub;
use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyBusinessUnitResourceMapper;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyBusinessUnitReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUnitAddressReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyBusinessUnitResourceMapperTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitResourceMapperTest extends BackendApiTestCase
{
    protected const string UUID = '4d1b3f9a-9d4c-5c1e-9f6b-2b5a7c8d9e01';

    protected const string COMPANY_UUID = '0818f408-cc84-575d-ad54-92118a0e4273';

    protected const string PARENT_UUID = 'b7c2e4d6-1a3f-5b8c-9d0e-4f6a8b2c1d3e';

    protected const string ADDRESS_UUID = '9f2c7b41-5d8e-5a3c-b06f-1e4d7a9c2b58';

    protected const string NAME = 'Acme Procurement';

    protected const string RENAMED = 'Acme Sourcing';

    protected const string IBAN = 'DE89370400440532013000';

    protected const string BIC = 'COBADEFFXXX';

    protected const string PHONE = '+49301234567';

    protected const int ID_COMPANY = 41;

    protected const int ID_OTHER_COMPANY = 42;

    protected const int ID_PARENT_COMPANY_BUSINESS_UNIT = 7;

    protected const int ID_ADDRESS = 91;

    public function testMapCompanyBusinessUnitTransferToResourceDataFlattensEveryRelationToItsUuid(): void
    {
        // Act
        $resourceData = $this->createMapper()
            ->mapCompanyBusinessUnitTransferToResourceData($this->createStoredCompanyBusinessUnitTransfer());

        // Assert
        $this->assertSame(static::UUID, $resourceData[CompanyBusinessUnitTransfer::UUID]);
        $this->assertSame(static::NAME, $resourceData[CompanyBusinessUnitTransfer::NAME]);
        $this->assertSame(static::COMPANY_UUID, $resourceData['companyUuid']);
        $this->assertSame(static::PARENT_UUID, $resourceData['parentBusinessUnitUuid']);
        $this->assertSame([static::ADDRESS_UUID], $resourceData['addressUuids']);
    }

    public function testMapCompanyBusinessUnitTransferToResourceDataLeavesAbsentRelationsNull(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = (new CompanyBusinessUnitTransfer())
            ->setUuid(static::UUID)
            ->setName(static::NAME);

        // Act
        $resourceData = $this->createMapper()
            ->mapCompanyBusinessUnitTransferToResourceData($companyBusinessUnitTransfer);

        // Assert
        $this->assertNull($resourceData['companyUuid']);
        $this->assertNull($resourceData['parentBusinessUnitUuid']);
        $this->assertSame([], $resourceData['addressUuids']);
    }

    public function testMapResourceToCompanyBusinessUnitTransferAppliesEverySuppliedProperty(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->name = static::RENAMED;
        $resource->iban = static::IBAN;
        $resource->bic = static::BIC;
        $resource->phone = static::PHONE;

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()
            ->mapResourceToCompanyBusinessUnitTransfer($resource, new CompanyBusinessUnitTransfer());

        // Assert
        $this->assertSame(static::RENAMED, $companyBusinessUnitTransfer->getName());
        $this->assertSame(static::IBAN, $companyBusinessUnitTransfer->getIban());
        $this->assertSame(static::BIC, $companyBusinessUnitTransfer->getBic());
        $this->assertSame(static::PHONE, $companyBusinessUnitTransfer->getPhone());
    }

    public function testMapResourceToCompanyBusinessUnitTransferLeavesAbsentPropertiesUntouched(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->name = static::RENAMED;

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()
            ->mapResourceToCompanyBusinessUnitTransfer($resource, $this->createStoredCompanyBusinessUnitTransfer());

        // Assert
        $this->assertSame(static::RENAMED, $companyBusinessUnitTransfer->getName());
        $this->assertSame(static::IBAN, $companyBusinessUnitTransfer->getIban());
        $this->assertSame(static::PHONE, $companyBusinessUnitTransfer->getPhone());
    }

    public function testMapResourceToCompanyBusinessUnitTransferAppliesAnEmptyStringRatherThanSkippingIt(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->iban = '';

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()
            ->mapResourceToCompanyBusinessUnitTransfer($resource, $this->createStoredCompanyBusinessUnitTransfer());

        // Assert
        $this->assertSame('', $companyBusinessUnitTransfer->getIban());
    }

    public function testMapResourceToCompanyBusinessUnitTransferResolvesBothUuidsToIds(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->companyUuid = static::COMPANY_UUID;
        $resource->parentBusinessUnitUuid = static::PARENT_UUID;

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()->mapResourceToCompanyBusinessUnitTransfer(
            $resource,
            new CompanyBusinessUnitTransfer(),
            ['parentBusinessUnitUuid' => static::PARENT_UUID],
        );

        // Assert
        $this->assertSame(static::ID_COMPANY, $companyBusinessUnitTransfer->getFkCompany());
        $this->assertSame(
            static::ID_PARENT_COMPANY_BUSINESS_UNIT,
            $companyBusinessUnitTransfer->getFkParentCompanyBusinessUnit(),
        );
    }

    public function testMapResourceToCompanyBusinessUnitTransferRejectsAParentOfAnotherCompany(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->companyUuid = static::COMPANY_UUID;
        $resource->parentBusinessUnitUuid = static::PARENT_UUID;

        // Act & Assert
        try {
            $this->createMapper(static::ID_OTHER_COMPANY)->mapResourceToCompanyBusinessUnitTransfer(
                $resource,
                new CompanyBusinessUnitTransfer(),
                ['parentBusinessUnitUuid' => static::PARENT_UUID],
            );
        } catch (GlueApiException $glueApiException) {
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_COMPANY_MISMATCH,
                $glueApiException->getErrorCode(),
            );

            return;
        }

        $this->fail('Expected a parent belonging to another company to be rejected.');
    }

    public function testMapResourceToCompanyBusinessUnitTransferReadsNothingWhenNoUuidIsSent(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->name = static::RENAMED;

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()
            ->mapResourceToCompanyBusinessUnitTransfer($resource, new CompanyBusinessUnitTransfer());

        // Assert
        $this->assertNull($companyBusinessUnitTransfer->getFkCompany());
        $this->assertNull($companyBusinessUnitTransfer->getFkParentCompanyBusinessUnit());
    }

    protected function createMapper(int $idCompanyOfParent = self::ID_COMPANY): CompanyBusinessUnitResourceMapper
    {
        return new CompanyBusinessUnitResourceMapper(
            Stub::makeEmpty(CompanyReaderInterface::class, [
                'getCompanyByUuid' => fn (): CompanyTransfer => (new CompanyTransfer())
                    ->setUuid(static::COMPANY_UUID)
                    ->setIdCompany(static::ID_COMPANY),
            ]),
            Stub::makeEmpty(CompanyBusinessUnitReaderInterface::class, [
                'getParentCompanyBusinessUnitByUuid' => fn (): CompanyBusinessUnitTransfer => (new CompanyBusinessUnitTransfer())
                    ->setUuid(static::PARENT_UUID)
                    ->setIdCompanyBusinessUnit(static::ID_PARENT_COMPANY_BUSINESS_UNIT)
                    ->setFkCompany($idCompanyOfParent),
            ]),
            Stub::makeEmpty(CompanyUnitAddressReaderInterface::class, [
                'getCompanyUnitAddressesByUuids' => function (array $uuids): CompanyUnitAddressCollectionTransfer {
                    $companyUnitAddressCollectionTransfer = new CompanyUnitAddressCollectionTransfer();

                    foreach ($uuids as $uuid) {
                        $companyUnitAddressCollectionTransfer->addCompanyUnitAddress(
                            (new CompanyUnitAddressTransfer())
                                ->setUuid($uuid)
                                ->setIdCompanyUnitAddress(static::ID_ADDRESS),
                        );
                    }

                    return $companyUnitAddressCollectionTransfer;
                },
            ]),
            new CompanyBusinessUnitsBackendExceptionFactory(new CustomerExperienceManagementConfig()),
        );
    }

    public function testMapResourceToCompanyBusinessUnitTransferDetachesTheParentWhenSubmittedAsNull(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $companyBusinessUnitTransfer = (new CompanyBusinessUnitTransfer())
            ->setFkParentCompanyBusinessUnit(static::ID_PARENT_COMPANY_BUSINESS_UNIT);

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()->mapResourceToCompanyBusinessUnitTransfer(
            $resource,
            $companyBusinessUnitTransfer,
            ['parentBusinessUnitUuid' => null],
        );

        // Assert
        $this->assertNull($companyBusinessUnitTransfer->getFkParentCompanyBusinessUnit());
    }

    public function testMapResourceToCompanyBusinessUnitTransferResolvesSubmittedAddressUuids(): void
    {
        // Arrange
        $resource = new CompanyBusinessUnitsBackendResource();
        $resource->addressUuids = [static::ADDRESS_UUID];

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()->mapResourceToCompanyBusinessUnitTransfer(
            $resource,
            new CompanyBusinessUnitTransfer(),
            ['addressUuids' => [static::ADDRESS_UUID]],
        );

        // Assert
        $companyUnitAddresses = $companyBusinessUnitTransfer->getAddressCollectionOrFail()->getCompanyUnitAddresses();
        $this->assertCount(1, $companyUnitAddresses);
        $this->assertSame(static::ADDRESS_UUID, $companyUnitAddresses[0]->getUuid());
    }

    public function testMapResourceToCompanyBusinessUnitTransferUnassignsEveryAddressWhenAnEmptyListIsSubmitted(): void
    {
        // Arrange
        $companyBusinessUnitTransfer = (new CompanyBusinessUnitTransfer())->setAddressCollection(
            (new CompanyUnitAddressCollectionTransfer())->addCompanyUnitAddress(
                (new CompanyUnitAddressTransfer())->setIdCompanyUnitAddress(static::ID_ADDRESS),
            ),
        );

        // Act
        $companyBusinessUnitTransfer = $this->createMapper()->mapResourceToCompanyBusinessUnitTransfer(
            new CompanyBusinessUnitsBackendResource(),
            $companyBusinessUnitTransfer,
            ['addressUuids' => []],
        );

        // Assert
        $this->assertCount(0, $companyBusinessUnitTransfer->getAddressCollectionOrFail()->getCompanyUnitAddresses());
    }

    protected function createStoredCompanyBusinessUnitTransfer(): CompanyBusinessUnitTransfer
    {
        return (new CompanyBusinessUnitTransfer())
            ->setUuid(static::UUID)
            ->setName(static::NAME)
            ->setIban(static::IBAN)
            ->setBic(static::BIC)
            ->setPhone(static::PHONE)
            ->setCompany((new CompanyTransfer())->setUuid(static::COMPANY_UUID))
            ->setParentCompanyBusinessUnit((new CompanyBusinessUnitTransfer())->setUuid(static::PARENT_UUID))
            ->setAddressCollection(
                (new CompanyUnitAddressCollectionTransfer())->addCompanyUnitAddress(
                    (new CompanyUnitAddressTransfer())->setUuid(static::ADDRESS_UUID),
                ),
            );
    }
}
