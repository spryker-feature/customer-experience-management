<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Generated\Shared\Transfer\CompanyResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CompaniesBackendExceptionFactory
{
    public function __construct(protected CustomerExperienceManagementConfig $config)
    {
    }

    public function createCompanyNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_NOT_FOUND, $uuid),
        );
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

    public function createNonScalarFilterValueException(string $field): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_NON_SCALAR_FILTER_VALUE, $field),
        );
    }

    public function createExceptionFromCompanyResponse(
        CompanyResponseTransfer $companyResponseTransfer
    ): GlueApiException {
        $messages = $this->extractErrorMessages($companyResponseTransfer);

        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->resolveResponseCode($messages),
            $messages[0] ?? CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_VALIDATION,
        );

        if (count($messages) > 1) {
            $glueApiException->setErrors($this->mapMessagesToErrors($messages));
        }

        return $glueApiException;
    }

    /**
     * @return array<int, string>
     */
    protected function extractErrorMessages(CompanyResponseTransfer $companyResponseTransfer): array
    {
        $messages = [];

        foreach ($companyResponseTransfer->getMessages() as $responseMessageTransfer) {
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
    protected function mapMessagesToErrors(array $messages): array
    {
        return array_map(static fn (string $message): array => [
            RestErrorMessageTransfer::CODE => CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_VALIDATION,
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

        return CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_VALIDATION;
    }
}
