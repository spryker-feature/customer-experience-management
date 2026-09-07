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

class CustomerNotesBackendExceptionFactory
{
    public function createNoteNotFoundException(string $uuid, string $customerReference): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_NOTE_NOT_FOUND,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_NOTE_NOT_FOUND,
                $uuid,
                $customerReference,
            ),
        );
    }

    /**
     * A note records who wrote it, so a request whose token does not resolve to a Back Office user
     * cannot be served — writing the note unattributed is not an option.
     */
    public function createActingUserNotResolvedException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNAUTHORIZED,
            CustomerExperienceManagementConfig::RESPONSE_CODE_ACTING_USER_NOT_RESOLVED,
            CustomerExperienceManagementConfig::RESPONSE_DETAILS_ACTING_USER_NOT_RESOLVED,
        );
    }
}
