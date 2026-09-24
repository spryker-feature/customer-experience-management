<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;

class CompanyBusinessUnitReader implements CompanyBusinessUnitReaderInterface
{
    public function __construct(
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getCompanyBusinessUnitByUuid(string $uuid): CompanyBusinessUnitTransfer
    {
        $companyBusinessUnitTransfer = $this->findCompanyBusinessUnitByUuid($uuid);

        if ($companyBusinessUnitTransfer === null) {
            throw $this->exceptionFactory->createCompanyBusinessUnitNotFoundException($uuid);
        }

        return $companyBusinessUnitTransfer;
    }

    public function getParentCompanyBusinessUnitByUuid(string $uuid): CompanyBusinessUnitTransfer
    {
        $companyBusinessUnitTransfer = $this->findCompanyBusinessUnitByUuid($uuid);

        if ($companyBusinessUnitTransfer === null) {
            throw $this->exceptionFactory->createParentCompanyBusinessUnitNotFoundException($uuid);
        }

        return $companyBusinessUnitTransfer;
    }

    protected function findCompanyBusinessUnitByUuid(string $uuid): ?CompanyBusinessUnitTransfer
    {
        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())->addUuid($uuid),
        );

        return $companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits()[0] ?? null;
    }
}
