<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\UserConditionsTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\ApiPlatform\EventSubscriber\IdentityRequestSubscriber;
use Spryker\Zed\User\Business\UserFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerNotesBackendExceptionFactory;
use Symfony\Component\HttpFoundation\Request;

class ActingUserReader implements ActingUserReaderInterface
{
    protected const string CLAIM_ID_USER = 'id_user';

    public function __construct(
        protected UserFacadeInterface $userFacade,
        protected CustomerNotesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getActingUser(Request $request): UserTransfer
    {
        $idUser = $this->findIdUserInClaims($request);

        if ($idUser === null) {
            throw $this->exceptionFactory->createActingUserNotResolvedException();
        }

        $userTransfers = $this->userFacade->getUserCollection(
            (new UserCriteriaTransfer())->setUserConditions(
                (new UserConditionsTransfer())->addIdUser($idUser),
            ),
        )->getUsers();

        if ($userTransfers->count() === 0) {
            throw $this->exceptionFactory->createActingUserNotResolvedException();
        }

        return $userTransfers->offsetGet(0);
    }

    protected function findIdUserInClaims(Request $request): ?int
    {
        $claims = $request->attributes->get(IdentityRequestSubscriber::ATTRIBUTE_OAUTH_IDENTITY_CLAIMS);

        if (!is_array($claims) || !isset($claims[static::CLAIM_ID_USER])) {
            return null;
        }

        $idUser = $claims[static::CLAIM_ID_USER];

        return is_numeric($idUser) ? (int)$idUser : null;
    }
}
