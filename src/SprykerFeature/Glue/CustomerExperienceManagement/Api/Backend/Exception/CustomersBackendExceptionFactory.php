<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CustomersBackendExceptionFactory
{
    public function __construct(protected CustomerExperienceManagementConfig $config)
    {
    }

    public function createCustomerNotFoundException(string $customerReference): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_NOT_FOUND, $customerReference),
        );
    }

    public function createStoreNameRequiredException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_STORE_NAME_REQUIRED,
            CustomerExperienceManagementConfig::RESPONSE_DETAILS_STORE_NAME_REQUIRED,
        );
    }

    public function createExceptionFromCustomerResponse(
        CustomerResponseTransfer $customerResponseTransfer
    ): GlueApiException {
        $messages = $this->extractErrorMessages($customerResponseTransfer);

        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->resolveResponseCode($messages),
            $messages[0] ?? CustomerExperienceManagementConfig::RESPONSE_DETAILS_VALIDATION,
        );

        if (count($messages) > 1) {
            $glueApiException->setErrors($this->mapMessagesToErrors($messages));
        }

        return $glueApiException;
    }

    /**
     * @return array<int, string>
     */
    protected function extractErrorMessages(CustomerResponseTransfer $customerResponseTransfer): array
    {
        $messages = [];

        foreach ($customerResponseTransfer->getErrors() as $customerErrorTransfer) {
            $message = $customerErrorTransfer->getMessage();

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
            RestErrorMessageTransfer::CODE => CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION,
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

        return CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION;
    }
}
