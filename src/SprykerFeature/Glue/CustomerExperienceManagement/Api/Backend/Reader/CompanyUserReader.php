<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\CompanyUser\Business\CompanyUserFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyUsersBackendExceptionFactory;

class CompanyUserReader implements CompanyUserReaderInterface
{
    protected const int ID_MATCHING_NOTHING = 0;

    public function __construct(
        protected CompanyUserFacadeInterface $companyUserFacade,
        protected CompanyFacadeInterface $companyFacade,
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected CompanyUsersBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getCompanyUserByUuid(string $uuid): CompanyUserTransfer
    {
        $companyUserTransfers = $this->companyUserFacade
            ->getCompanyUserCollection(
                (new CompanyUserCriteriaFilterTransfer())->setUuid($uuid),
            )
            ->getCompanyUsers();

        if ($companyUserTransfers->count() === 0) {
            throw $this->exceptionFactory->createCompanyUserNotFoundException($uuid);
        }

        return $companyUserTransfers->offsetGet(0);
    }

    public function getCompanyUserCollection(
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
    ): CompanyUserCollectionTransfer {
        return $this->companyUserFacade->getCompanyUserCollection($companyUserCriteriaFilterTransfer);
    }

    public function findIdCompanyByUuid(string $companyUuid): int
    {
        $companyResponseTransfer = $this->companyFacade->findCompanyByUuid(
            (new CompanyTransfer())->setUuid($companyUuid),
        );

        $companyTransfer = $companyResponseTransfer->getCompanyTransfer();

        if (!$companyResponseTransfer->getIsSuccessful() || $companyTransfer === null) {
            return static::ID_MATCHING_NOTHING;
        }

        return $companyTransfer->getIdCompanyOrFail();
    }

    public function findIdCompanyBusinessUnitByUuid(string $companyBusinessUnitUuid): int
    {
        $companyBusinessUnitResponseTransfer = $this->companyBusinessUnitFacade->findCompanyBusinessUnitByUuid(
            (new CompanyBusinessUnitTransfer())->setUuid($companyBusinessUnitUuid),
        );

        $companyBusinessUnitTransfer = $companyBusinessUnitResponseTransfer->getCompanyBusinessUnitTransfer();

        if (!$companyBusinessUnitResponseTransfer->getIsSuccessful() || $companyBusinessUnitTransfer === null) {
            return static::ID_MATCHING_NOTHING;
        }

        return $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
    }
}
