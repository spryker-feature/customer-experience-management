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

class CustomerAccessBackendExceptionFactory
{
    /**
     * @var non-empty-string
     */
    protected const string CONTENT_TYPE_SEPARATOR = ', ';

    /**
     * @param array<int, string> $configuredContentTypes
     */
    public function createUnknownContentTypeException(
        string $contentType,
        array $configuredContentTypes
    ): GlueApiException {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_ACCESS_UNKNOWN_CONTENT_TYPE,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_ACCESS_UNKNOWN_CONTENT_TYPE,
                $contentType,
                implode(static::CONTENT_TYPE_SEPARATOR, $configuredContentTypes),
            ),
        );
    }

    public function createDuplicateContentTypeException(string $contentType): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_CUSTOMER_ACCESS_DUPLICATE_CONTENT_TYPE,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_CUSTOMER_ACCESS_DUPLICATE_CONTENT_TYPE,
                $contentType,
            ),
        );
    }
}
