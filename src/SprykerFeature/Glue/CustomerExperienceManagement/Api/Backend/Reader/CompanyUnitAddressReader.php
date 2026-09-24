<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyUnitAddressCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Spryker\Zed\CompanyUnitAddress\Business\CompanyUnitAddressFacadeInterface;
use Spryker\Zed\CompanyUnitAddressLabel\Business\CompanyUnitAddressLabelFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;

class CompanyUnitAddressReader implements CompanyUnitAddressReaderInterface
{
    public function __construct(
        protected CompanyUnitAddressFacadeInterface $companyUnitAddressFacade,
        protected CompanyUnitAddressLabelFacadeInterface $companyUnitAddressLabelFacade,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When no address matches the uuid.
     */
    public function getCompanyUnitAddressByUuid(string $uuid): CompanyUnitAddressTransfer
    {
        $companyUnitAddressResponseTransfer = $this->companyUnitAddressFacade->findCompanyBusinessUnitAddressByUuid(
            (new CompanyUnitAddressTransfer())->setUuid($uuid),
        );

        if (!$companyUnitAddressResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createCompanyUnitAddressNotFoundException($uuid);
        }

        return $this->companyUnitAddressLabelFacade->hydrateCompanyUnitAddressWithLabelCollection(
            $companyUnitAddressResponseTransfer->getCompanyUnitAddressTransferOrFail(),
        );
    }

    /**
     * @param array<int, string> $uuids
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When the list holds an address that cannot be assigned to the company.
     */
    public function getCompanyUnitAddressesByUuids(
        array $uuids,
        ?int $idCompany
    ): CompanyUnitAddressCollectionTransfer {
        $companyUnitAddressCollectionTransfer = new CompanyUnitAddressCollectionTransfer();

        if ($uuids === []) {
            return $companyUnitAddressCollectionTransfer;
        }

        $companyUnitAddressTransfersByUuid = $this->indexCompanyUnitAddressesByUuid($uuids);
        $errorCollection = $this->exceptionFactory->createCompanyBusinessUnitsBackendErrorCollection();

        foreach ($uuids as $uuid) {
            $companyUnitAddressTransfer = $companyUnitAddressTransfersByUuid[$uuid] ?? null;

            if ($companyUnitAddressTransfer === null) {
                $errorCollection->addCompanyUnitAddressNotFound($uuid);

                continue;
            }

            if ($companyUnitAddressTransfer->getFkCompany() !== $idCompany) {
                $errorCollection->addCompanyUnitAddressCompanyMismatch($uuid);

                continue;
            }

            $companyUnitAddressCollectionTransfer->addCompanyUnitAddress($companyUnitAddressTransfer);
        }

        if (!$errorCollection->isEmpty()) {
            throw $errorCollection->toGlueApiException();
        }

        return $companyUnitAddressCollectionTransfer;
    }

    /**
     * @param array<int, string> $uuids
     *
     * @return array<string, \Generated\Shared\Transfer\CompanyUnitAddressTransfer>
     */
    protected function indexCompanyUnitAddressesByUuid(array $uuids): array
    {
        $companyUnitAddressCollectionTransfer = $this->companyUnitAddressFacade->getCompanyUnitAddressCollection(
            (new CompanyUnitAddressCriteriaFilterTransfer())->setUuids($uuids),
        );

        $companyUnitAddressTransfersByUuid = [];

        foreach ($companyUnitAddressCollectionTransfer->getCompanyUnitAddresses() as $companyUnitAddressTransfer) {
            $companyUnitAddressTransfersByUuid[$companyUnitAddressTransfer->getUuidOrFail()] = $companyUnitAddressTransfer;
        }

        return $companyUnitAddressTransfersByUuid;
    }
}
