<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Stub;
use Generated\Shared\Transfer\CompanyUnitAddressCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Zed\CompanyUnitAddress\Business\CompanyUnitAddressFacadeInterface;
use Spryker\Zed\CompanyUnitAddressLabel\Business\CompanyUnitAddressLabelFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUnitAddressReader;
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
 * @group CompanyUnitAddressReaderTest
 * Add your own group annotations below this line
 */
class CompanyUnitAddressReaderTest extends BackendApiTestCase
{
    protected const string OWNED_UUID = '9f2c7b41-5d8e-5a3c-b06f-1e4d7a9c2b58';

    protected const string SECOND_OWNED_UUID = '3a8d1e05-7c2b-5f4a-8e19-6d0b3c7f2a94';

    protected const string FOREIGN_UUID = '7c4e2b18-2f6a-5d9b-8c31-0a5f4e6d8b27';

    protected const string FIRST_UNKNOWN_UUID = '11111111-2222-4333-8444-555555555555';

    protected const string SECOND_UNKNOWN_UUID = '66666666-7777-4888-8999-aaaaaaaaaaaa';

    protected const int ID_COMPANY = 41;

    protected const int ID_OTHER_COMPANY = 42;

    public function testReturnsEveryAddressOfTheCompany(): void
    {
        // Act
        $companyUnitAddressCollectionTransfer = $this->createReader()->getCompanyUnitAddressesByUuids(
            [static::OWNED_UUID, static::SECOND_OWNED_UUID],
            static::ID_COMPANY,
        );

        // Assert
        $this->assertCount(2, $companyUnitAddressCollectionTransfer->getCompanyUnitAddresses());
    }

    public function testReadsNothingForAnEmptyList(): void
    {
        // Act
        $companyUnitAddressCollectionTransfer = $this->createReader()->getCompanyUnitAddressesByUuids(
            [],
            static::ID_COMPANY,
        );

        // Assert
        $this->assertCount(0, $companyUnitAddressCollectionTransfer->getCompanyUnitAddresses());
    }

    public function testReportsEveryUnknownUuidRatherThanOnlyTheFirst(): void
    {
        // Act
        $errors = $this->captureErrors([
            static::FIRST_UNKNOWN_UUID,
            static::OWNED_UUID,
            static::SECOND_UNKNOWN_UUID,
        ]);

        // Assert
        $this->assertCount(2, $errors, 'Both unknown uuids are reported, and the known one is not.');
        $this->assertStringContainsString(static::FIRST_UNKNOWN_UUID, $errors[0]['detail']);
        $this->assertStringContainsString(static::SECOND_UNKNOWN_UUID, $errors[1]['detail']);
    }

    public function testRejectsAnAddressOfAnotherCompany(): void
    {
        // Expect
        $this->expectException(GlueApiException::class);
        $this->expectExceptionMessage(static::FOREIGN_UUID);

        // Act
        $this->createReader()->getCompanyUnitAddressesByUuids([static::FOREIGN_UUID], static::ID_COMPANY);
    }

    public function testReportsBothKindsOfRejectedAddressTogether(): void
    {
        // Act
        $errors = $this->captureErrors([static::FOREIGN_UUID, static::FIRST_UNKNOWN_UUID]);

        // Assert
        $this->assertSame(
            [
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_COMPANY_MISMATCH,
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_NOT_FOUND,
            ],
            array_column($errors, 'code'),
        );
    }

    /**
     * @param array<int, string> $uuids
     *
     * @return array<int, array<string, mixed>>
     */
    protected function captureErrors(array $uuids): array
    {
        try {
            $this->createReader()->getCompanyUnitAddressesByUuids($uuids, static::ID_COMPANY);
        } catch (GlueApiException $glueApiException) {
            return $glueApiException->getErrors();
        }

        $this->fail('Expected a GlueApiException, none was thrown.');
    }

    protected function createReader(): CompanyUnitAddressReader
    {
        $idCompanyByUuid = [
            static::OWNED_UUID => static::ID_COMPANY,
            static::SECOND_OWNED_UUID => static::ID_COMPANY,
            static::FOREIGN_UUID => static::ID_OTHER_COMPANY,
        ];

        return new CompanyUnitAddressReader(
            Stub::makeEmpty(CompanyUnitAddressFacadeInterface::class, [
                'getCompanyUnitAddressCollection' => function (
                    CompanyUnitAddressCriteriaFilterTransfer $companyUnitAddressCriteriaFilterTransfer
                ) use ($idCompanyByUuid): CompanyUnitAddressCollectionTransfer {
                    $companyUnitAddressCollectionTransfer = new CompanyUnitAddressCollectionTransfer();

                    foreach ($companyUnitAddressCriteriaFilterTransfer->getUuids() as $uuid) {
                        if (!isset($idCompanyByUuid[$uuid])) {
                            continue;
                        }

                        $companyUnitAddressCollectionTransfer->addCompanyUnitAddress(
                            (new CompanyUnitAddressTransfer())->setUuid($uuid)->setFkCompany($idCompanyByUuid[$uuid]),
                        );
                    }

                    return $companyUnitAddressCollectionTransfer;
                },
            ]),
            Stub::makeEmpty(CompanyUnitAddressLabelFacadeInterface::class),
            new CompanyBusinessUnitsBackendExceptionFactory(new CustomerExperienceManagementConfig()),
        );
    }
}
