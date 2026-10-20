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

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return array<string, \Generated\Shared\Transfer\CompanyBusinessUnitTransfer>
     */
    public function getCompanyBusinessUnitsIndexedByUuid(array $companyBusinessUnitUuids): array
    {
        if ($companyBusinessUnitUuids === []) {
            return [];
        }

        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())->setUuids($companyBusinessUnitUuids),
        );

        $companyBusinessUnitTransfersByUuid = [];

        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $companyBusinessUnitTransfersByUuid[$companyBusinessUnitTransfer->getUuidOrFail()] = $companyBusinessUnitTransfer;
        }

        return $companyBusinessUnitTransfersByUuid;
    }

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return array<string, int>
     */
    public function getCompanyBusinessUnitIdsIndexedByUuid(array $companyBusinessUnitUuids): array
    {
        if ($companyBusinessUnitUuids === []) {
            return [];
        }

        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())
                ->setUuids($companyBusinessUnitUuids)
                ->setWithoutExpanders(true),
        );

        $companyBusinessUnitIdsByUuid = [];

        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $companyBusinessUnitIdsByUuid[$companyBusinessUnitTransfer->getUuidOrFail()] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        return $companyBusinessUnitIdsByUuid;
    }

    protected function findCompanyBusinessUnitByUuid(string $uuid): ?CompanyBusinessUnitTransfer
    {
        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())->addUuid($uuid),
        );

        return $companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits()[0] ?? null;
    }
}
