<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CustomerGroupsBackendExceptionFactory
{
    public function __construct(
        protected CustomerExperienceManagementConfig $config,
    ) {
    }

    public function createCustomerGroupNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_GROUP_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_NOT_FOUND, $uuid),
        );
    }

    public function createAssignmentNotFoundException(string $customerReference, string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_GROUP_ASSIGNMENT_NOT_FOUND,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_ASSIGNMENT_NOT_FOUND,
                $customerReference,
                $uuid,
            ),
        );
    }

    public function createValidationException(
        CustomerGroupCollectionResponseTransfer $customerGroupCollectionResponseTransfer
    ): GlueApiException {
        $errors = $this->mapResponseErrorsToGlueErrors($customerGroupCollectionResponseTransfer);

        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->resolveResponseCode($errors),
            $errors[0][RestErrorMessageTransfer::DETAIL]
                ?? CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_VALIDATION,
        );

        if (count($errors) > 1) {
            $glueApiException->setErrors($errors);
        }

        return $glueApiException;
    }

    /**
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    protected function mapResponseErrorsToGlueErrors(
        CustomerGroupCollectionResponseTransfer $customerGroupCollectionResponseTransfer
    ): array {
        $responseCodeByErrorMessage = $this->config->getResponseCodeByErrorMessage();
        $errors = [];

        foreach ($customerGroupCollectionResponseTransfer->getErrors() as $errorTransfer) {
            $message = (string)$errorTransfer->getMessage();

            $errors[] = [
                RestErrorMessageTransfer::CODE => $responseCodeByErrorMessage[$message]
                    ?? CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION,
                RestErrorMessageTransfer::STATUS => Response::HTTP_UNPROCESSABLE_ENTITY,
                RestErrorMessageTransfer::DETAIL => $this->formatErrorDetail(
                    $message,
                    (string)$errorTransfer->getEntityIdentifier(),
                ),
            ];
        }

        return $errors;
    }

    /**
     * @param array<int, array{code: string, status: int, detail: string}> $errors
     */
    protected function resolveResponseCode(array $errors): string
    {
        foreach ($errors as $error) {
            if ($error[RestErrorMessageTransfer::CODE] !== CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION) {
                return $error[RestErrorMessageTransfer::CODE];
            }
        }

        return CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION;
    }

    protected function formatErrorDetail(string $message, string $entityIdentifier): string
    {
        return match ($message) {
            CustomerExperienceManagementConfig::ERROR_MESSAGE_CUSTOMER_GROUP_NAME_TAKEN => sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_NAME_TAKEN,
                $entityIdentifier,
            ),
            CustomerExperienceManagementConfig::ERROR_MESSAGE_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND => sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND,
                $entityIdentifier,
            ),
            default => $message,
        };
    }
}
