<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Error;

use Spryker\ApiPlatform\Error\GlueApiErrorCollection;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

class CompanyBusinessUnitsBackendErrorCollection extends GlueApiErrorCollection
{
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
}
