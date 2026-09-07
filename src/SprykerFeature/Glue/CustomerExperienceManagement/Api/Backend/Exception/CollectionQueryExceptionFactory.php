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

class CollectionQueryExceptionFactory
{
    /**
     * @param array<int, string> $sortableFields
     */
    public function createInvalidSortFieldException(string $sortField, array $sortableFields): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_INVALID_SORT_FIELD,
                $sortField,
                implode(', ', $sortableFields),
            ),
        );
    }
}
