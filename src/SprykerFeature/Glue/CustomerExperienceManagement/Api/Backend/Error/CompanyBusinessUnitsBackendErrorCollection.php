<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Error;

use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CompanyBusinessUnitsBackendErrorCollection
{
    /**
     * @var array<int, array{code: string, status: int, detail: string}>
     */
    protected array $errors = [];

    public function addCompanyUnitAddressNotFound(string $uuid): static
    {
        return $this->addError(
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_NOT_FOUND,
            Response::HTTP_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_NOT_FOUND, $uuid),
        );
    }

    public function addCompanyUnitAddressCompanyMismatch(string $uuid): static
    {
        return $this->addError(
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_UNIT_ADDRESS_COMPANY_MISMATCH,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_COMPANY_MISMATCH, $uuid),
        );
    }

    public function isEmpty(): bool
    {
        return $this->errors === [];
    }

    /**
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toGlueApiException(): GlueApiException
    {
        $glueApiException = new GlueApiException(
            $this->resolveStatusCode(),
            $this->errors[0]['code'],
            $this->errors[0]['detail'],
        );

        if (count($this->errors) > 1) {
            $glueApiException->setErrors($this->errors);
        }

        return $glueApiException;
    }

    protected function addError(string $code, int $statusCode, string $detail): static
    {
        $this->errors[] = $this->buildError($code, $statusCode, $detail);

        return $this;
    }

    /**
     * @return array{code: string, status: int, detail: string}
     */
    protected function buildError(string $code, int $statusCode, string $detail): array
    {
        return [
            'code' => $code,
            'status' => $statusCode,
            'detail' => $detail,
        ];
    }

    protected function resolveStatusCode(): int
    {
        $statusCodes = array_unique(array_column($this->errors, 'status'));

        if (count($statusCodes) === 1) {
            return (int)reset($statusCodes);
        }

        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
