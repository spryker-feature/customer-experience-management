<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyBusinessUnitReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUnitAddressReaderInterface;

class CompanyBusinessUnitResourceMapper implements CompanyBusinessUnitResourceMapperInterface
{
    protected const string FIELD_COMPANY_UUID = 'companyUuid';

    protected const string FIELD_PARENT_BUSINESS_UNIT_UUID = 'parentBusinessUnitUuid';

    protected const string FIELD_ADDRESS_UUIDS = 'addressUuids';

    public function __construct(
        protected CompanyReaderInterface $companyReader,
        protected CompanyBusinessUnitReaderInterface $companyBusinessUnitReader,
        protected CompanyUnitAddressReaderInterface $companyUnitAddressReader,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function mapResourceToCompanyBusinessUnitTransfer(
        CompanyBusinessUnitsBackendResource $resource,
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): CompanyBusinessUnitTransfer {
        if ($resource->name !== null) {
            $companyBusinessUnitTransfer->setName($resource->name);
        }

        if ($resource->iban !== null) {
            $companyBusinessUnitTransfer->setIban($resource->iban);
        }

        if ($resource->bic !== null) {
            $companyBusinessUnitTransfer->setBic($resource->bic);
        }

        if ($resource->phone !== null) {
            $companyBusinessUnitTransfer->setPhone($resource->phone);
        }

        if ($resource->companyUuid !== null) {
            $companyBusinessUnitTransfer->setFkCompany(
                $this->companyReader->getCompanyByUuid($resource->companyUuid)->getIdCompanyOrFail(),
            );
        }

        $companyBusinessUnitTransfer = $this->applyParentCompanyBusinessUnit(
            $companyBusinessUnitTransfer,
            $resource->parentBusinessUnitUuid,
        );

        return $companyBusinessUnitTransfer->setAddressCollection(
            $this->companyUnitAddressReader->getCompanyUnitAddressesByUuids(
                $resource->addressUuids,
                $companyBusinessUnitTransfer->getFkCompany(),
            ),
        );
    }

    protected function applyParentCompanyBusinessUnit(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer,
        ?string $parentBusinessUnitUuid
    ): CompanyBusinessUnitTransfer {
        if ($parentBusinessUnitUuid === null) {
            return $companyBusinessUnitTransfer->setFkParentCompanyBusinessUnit(null);
        }

        $parentCompanyBusinessUnitTransfer = $this->companyBusinessUnitReader
            ->getParentCompanyBusinessUnitByUuid($parentBusinessUnitUuid);

        if ($parentCompanyBusinessUnitTransfer->getFkCompany() !== $companyBusinessUnitTransfer->getFkCompany()) {
            throw $this->exceptionFactory->createParentCompanyBusinessUnitCompanyMismatchException(
                $parentBusinessUnitUuid,
            );
        }

        return $companyBusinessUnitTransfer->setFkParentCompanyBusinessUnit(
            $parentCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function mapCompanyBusinessUnitTransferToResourceData(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): array {
        $resourceData = $companyBusinessUnitTransfer->toArray(false, true);

        $resourceData[static::FIELD_COMPANY_UUID] = $companyBusinessUnitTransfer->getCompany()?->getUuid();
        $resourceData[static::FIELD_PARENT_BUSINESS_UNIT_UUID] = $companyBusinessUnitTransfer
            ->getParentCompanyBusinessUnit()?->getUuid();
        $resourceData[static::FIELD_ADDRESS_UUIDS] = $this->extractAddressUuids($companyBusinessUnitTransfer);

        return $resourceData;
    }

    /**
     * @return array<int, string>
     */
    protected function extractAddressUuids(CompanyBusinessUnitTransfer $companyBusinessUnitTransfer): array
    {
        $addressCollectionTransfer = $companyBusinessUnitTransfer->getAddressCollection();

        if ($addressCollectionTransfer === null) {
            return [];
        }

        $addressUuids = [];

        foreach ($addressCollectionTransfer->getCompanyUnitAddresses() as $companyUnitAddressTransfer) {
            $uuid = $companyUnitAddressTransfer->getUuid();

            if ($uuid !== null) {
                $addressUuids[] = $uuid;
            }
        }

        return $addressUuids;
    }
}
