<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Generated\Shared\Transfer\AddressResponseTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CustomerAddressesBackendExceptionFactory
{
    public function __construct(protected CustomerExperienceManagementConfig $config)
    {
    }

    public function createAddressNotFoundException(string $uuid, string $customerReference): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_ADDRESS_NOT_FOUND,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_ADDRESS_NOT_FOUND,
                $uuid,
                $customerReference,
            ),
        );
    }

    public function createAddressValidationException(AddressResponseTransfer $addressResponseTransfer): GlueApiException
    {
        $messages = [];

        foreach ($addressResponseTransfer->getErrors() as $customerErrorTransfer) {
            $messages[] = (string)$customerErrorTransfer->getMessage();
        }

        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->resolveResponseCode($messages),
            $messages === [] ? CustomerExperienceManagementConfig::RESPONSE_DETAILS_VALIDATION : implode(' ', $messages),
        );
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
