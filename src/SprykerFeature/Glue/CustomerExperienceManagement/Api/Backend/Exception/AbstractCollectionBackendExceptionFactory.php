<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCollectionBackendExceptionFactory
{
    protected const string SUPPORTED_FIELDS_NONE = 'none';

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
                $supportedFields === [] ? static::SUPPORTED_FIELDS_NONE : implode(', ', $supportedFields),
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
}
