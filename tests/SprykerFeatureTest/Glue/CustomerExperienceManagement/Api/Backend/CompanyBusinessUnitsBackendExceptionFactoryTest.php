<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitResponseTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressResponseTransfer;
use Generated\Shared\Transfer\ResponseMessageTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyBusinessUnitsBackendExceptionFactoryTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitsBackendExceptionFactoryTest extends Unit
{
    protected const string FIRST_MESSAGE = 'First rejection.';

    protected const string SECOND_MESSAGE = 'Second rejection.';

    public function testAddressErrorEntriesCarryTheAddressValidationCode(): void
    {
        // Arrange
        $companyUnitAddressResponseTransfer = (new CompanyUnitAddressResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new ResponseMessageTransfer())->setText(static::FIRST_MESSAGE))
            ->addMessage((new ResponseMessageTransfer())->setText(static::SECOND_MESSAGE));

        // Act
        $glueApiException = $this->createFactory()->createExceptionFromCompanyUnitAddressResponse(
            $companyUnitAddressResponseTransfer,
        );

        // Assert
        $this->assertSame(
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_VALIDATION,
            $glueApiException->getErrorCode(),
        );

        foreach ($glueApiException->getErrors() as $error) {
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_VALIDATION,
                $error['code'] ?? null,
                'An address error entry must not carry the business unit validation code.',
            );
        }
    }

    public function testBusinessUnitErrorEntriesCarryTheBusinessUnitValidationCode(): void
    {
        // Arrange
        $companyBusinessUnitResponseTransfer = (new CompanyBusinessUnitResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new ResponseMessageTransfer())->setText(static::FIRST_MESSAGE))
            ->addMessage((new ResponseMessageTransfer())->setText(static::SECOND_MESSAGE));

        // Act
        $glueApiException = $this->createFactory()->createExceptionFromCompanyBusinessUnitResponse(
            $companyBusinessUnitResponseTransfer,
        );

        // Assert
        foreach ($glueApiException->getErrors() as $error) {
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_VALIDATION,
                $error['code'] ?? null,
            );
        }
    }

    public function testASingleMessageBuildsNoErrorEntries(): void
    {
        // Arrange
        $companyUnitAddressResponseTransfer = (new CompanyUnitAddressResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new ResponseMessageTransfer())->setText(static::FIRST_MESSAGE));

        // Act
        $glueApiException = $this->createFactory()->createExceptionFromCompanyUnitAddressResponse(
            $companyUnitAddressResponseTransfer,
        );

        // Assert
        $this->assertSame([], $glueApiException->getErrors());
        $this->assertSame(static::FIRST_MESSAGE, $glueApiException->getMessage());
    }

    protected function createFactory(): CompanyBusinessUnitsBackendExceptionFactory
    {
        return new CompanyBusinessUnitsBackendExceptionFactory(new CustomerExperienceManagementConfig());
    }
}
