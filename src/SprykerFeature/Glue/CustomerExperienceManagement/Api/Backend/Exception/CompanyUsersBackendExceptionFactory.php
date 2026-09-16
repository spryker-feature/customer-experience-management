<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Generated\Shared\Transfer\CompanyUserResponseTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CompanyUsersBackendExceptionFactory
{
    /**
     * @uses \Spryker\ApiPlatform\State\TranslatingErrorProvider::MESSAGE_SEPARATOR
     */
    protected const string MESSAGE_SEPARATOR = '; ';

    public function createCompanyUserNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_USER_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_USER_NOT_FOUND, $uuid),
        );
    }

    public function createCompanyNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_NOT_FOUND, $uuid),
        );
    }

    public function createCompanyBusinessUnitNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_BUSINESS_UNIT_NOT_FOUND, $uuid),
        );
    }

    public function createCompanyRoleNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_ROLE_NOT_FOUND, $uuid),
        );
    }

    public function createMissingCustomerException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_USER_CUSTOMER_MISSING,
            CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_USER_CUSTOMER_MISSING,
        );
    }

    public function createInvalidStatusPayloadException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_USER_STATUS_INVALID,
            CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_USER_STATUS_INVALID,
        );
    }

    public function createInvalidDefaultPayloadException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_USER_DEFAULT_INVALID,
            CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_USER_DEFAULT_INVALID,
        );
    }

    public function createCompanyUserValidationException(
        CompanyUserResponseTransfer $companyUserResponseTransfer
    ): GlueApiException {
        $messages = [];

        foreach ($companyUserResponseTransfer->getMessages() as $responseMessageTransfer) {
            $messages[] = (string)$responseMessageTransfer->getText();
        }

        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION,
            $messages === []
                ? CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_USER_VALIDATION
                : implode(static::MESSAGE_SEPARATOR, $messages),
        );
    }
}
