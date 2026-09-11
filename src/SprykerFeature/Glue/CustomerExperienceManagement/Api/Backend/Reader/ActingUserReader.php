<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\UserTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerNotesBackendExceptionFactory;
use Symfony\Component\HttpFoundation\Request;

class ActingUserReader implements ActingUserReaderInterface
{
    /**
     * @uses \Spryker\Glue\User\Api\Backend\EventSubscriber\UserIdentityRequestSubscriber::ATTRIBUTE_USER_TRANSFER
     */
    protected const string ATTRIBUTE_USER_TRANSFER = 'UserTransfer';

    public function __construct(
        protected CustomerNotesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getActingUser(Request $request): UserTransfer
    {
        $userTransfer = $request->attributes->get(static::ATTRIBUTE_USER_TRANSFER);

        if (!$userTransfer instanceof UserTransfer) {
            throw $this->exceptionFactory->createActingUserNotResolvedException();
        }

        return $userTransfer;
    }
}
