<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Generated\Shared\Transfer\CompanyBusinessUnitResponseTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Error\CompanyBusinessUnitsBackendErrorCollection;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CompanyBusinessUnitsBackendExceptionFactory
{
    public function __construct(protected CustomerExperienceManagementConfig $config)
    {
    }

    public function createCompanyBusinessUnitNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_BUSINESS_UNIT_NOT_FOUND, $uuid),
        );
    }

    public function createParentCompanyBusinessUnitNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_NOT_FOUND,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_PARENT_COMPANY_BUSINESS_UNIT_NOT_FOUND,
                $uuid,
            ),
        );
    }

    public function createParentCompanyBusinessUnitCompanyMismatchException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_COMPANY_MISMATCH,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_PARENT_COMPANY_BUSINESS_UNIT_COMPANY_MISMATCH,
                $uuid,
            ),
        );
    }

    public function createCompanyUnitAddressNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_NOT_FOUND, $uuid),
        );
    }

    public function createCompanyBusinessUnitsBackendErrorCollection(): CompanyBusinessUnitsBackendErrorCollection
    {
        return new CompanyBusinessUnitsBackendErrorCollection();
    }

    public function createExceptionFromCompanyBusinessUnitResponse(
        CompanyBusinessUnitResponseTransfer $companyBusinessUnitResponseTransfer
    ): GlueApiException {
        $messages = $this->extractErrorMessages($companyBusinessUnitResponseTransfer);

        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->resolveResponseCode($messages),
            $messages[0] ?? CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_BUSINESS_UNIT_VALIDATION,
        );

        if (count($messages) > 1) {
            $glueApiException->setErrors($this->mapMessagesToCompanyBusinessUnitErrors($messages));
        }

        return $glueApiException;
    }

    public function createExceptionFromCompanyUnitAddressResponse(
        CompanyUnitAddressResponseTransfer $companyUnitAddressResponseTransfer
    ): GlueApiException {
        $messages = $this->extractErrorMessages($companyUnitAddressResponseTransfer);

        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_VALIDATION,
            $messages[0] ?? CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_VALIDATION,
        );

        if (count($messages) > 1) {
            $glueApiException->setErrors($this->mapMessagesToCompanyUnitAddressErrors($messages));
        }

        return $glueApiException;
    }

    /**
     * @param array<int, string> $supportedFields
     */
    public function createUnsupportedFilterFieldException(string $field, array $supportedFields): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_UNSUPPORTED_FILTER_FIELD,
                $field,
                implode(', ', $supportedFields),
            ),
        );
    }

    /**
     * @param array<int, string> $configuredLabelNames
     */
    public function createUnknownCompanyUnitAddressLabelException(
        string $labelName,
        array $configuredLabelNames
    ): GlueApiException {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_VALIDATION,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_UNKNOWN_COMPANY_UNIT_ADDRESS_LABEL,
                $labelName,
                implode(', ', $configuredLabelNames),
            ),
        );
    }

    public function createUnknownCountryException(string $iso2Code): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_UNKNOWN_COUNTRY,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_UNKNOWN_COUNTRY, $iso2Code),
        );
    }

    public function createNonScalarFilterValueException(string $field): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_NON_SCALAR_FILTER_VALUE, $field),
        );
    }

    /**
     * @return array<int, string>
     */

    /**
     * @return array<int, string>
     */
    protected function extractErrorMessages(
        CompanyBusinessUnitResponseTransfer|CompanyUnitAddressResponseTransfer $companyBusinessUnitResponseTransfer
    ): array {
        $messages = [];

        foreach ($companyBusinessUnitResponseTransfer->getMessages() as $responseMessageTransfer) {
            $message = $responseMessageTransfer->getText();

            if ($message !== null && $message !== '') {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * @param array<int, string> $messages
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    protected function mapMessagesToCompanyBusinessUnitErrors(array $messages): array
    {
        return array_map(static fn (string $message): array => [
            RestErrorMessageTransfer::CODE => CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_VALIDATION,
            RestErrorMessageTransfer::STATUS => Response::HTTP_UNPROCESSABLE_ENTITY,
            RestErrorMessageTransfer::DETAIL => $message,
        ], $messages);
    }

    /**
     * @param array<int, string> $messages
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    protected function mapMessagesToCompanyUnitAddressErrors(array $messages): array
    {
        return array_map(static fn (string $message): array => [
            RestErrorMessageTransfer::CODE => CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_VALIDATION,
            RestErrorMessageTransfer::STATUS => Response::HTTP_UNPROCESSABLE_ENTITY,
            RestErrorMessageTransfer::DETAIL => $message,
        ], $messages);
    }

    /**
     * @param array<int, string> $messages
     */
    protected function resolveResponseCode(array $messages): string
    {
        $responseCodeByErrorMessage = $this->config->getResponseCodeByErrorMessage();

        foreach ($messages as $message) {
            if (isset($responseCodeByErrorMessage[$message])) {
                return $responseCodeByErrorMessage[$message];
            }
        }

        return CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_VALIDATION;
    }
}
