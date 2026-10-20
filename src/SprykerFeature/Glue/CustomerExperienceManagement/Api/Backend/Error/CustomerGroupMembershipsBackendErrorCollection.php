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

class CustomerGroupMembershipsBackendErrorCollection
{
    /**
     * @var array<int, array{code: string, status: int, detail: string}>
     */
    protected array $errors = [];

    public function addCompanyNotFound(string $companyUuid): static
    {
        return $this->addError(
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_GROUP_COMPANY_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_COMPANY_NOT_FOUND, $companyUuid),
        );
    }

    public function addCompanyBusinessUnitNotFound(string $companyBusinessUnitUuid): static
    {
        return $this->addError(
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_GROUP_COMPANY_BUSINESS_UNIT_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_GROUP_COMPANY_BUSINESS_UNIT_NOT_FOUND, $companyBusinessUnitUuid),
        );
    }

    public function isEmpty(): bool
    {
        return $this->errors === [];
    }

    public function toGlueApiException(): GlueApiException
    {
        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->errors[0]['code'],
            $this->errors[0]['detail'],
        );

        if (count($this->errors) > 1) {
            $glueApiException->setErrors($this->errors);
        }

        return $glueApiException;
    }

    protected function addError(string $code, string $detail): static
    {
        $this->errors[] = [
            'code' => $code,
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'detail' => $detail,
        ];

        return $this;
    }
}
