<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use ArrayObject;
use Generated\Api\Backend\CompanyBusinessUnitAddressesBackendResource;
use Generated\Shared\Transfer\CompanyUnitAddressLabelCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUnitAddressLabelReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CountryReaderInterface;

class CompanyBusinessUnitAddressResourceMapper implements CompanyBusinessUnitAddressResourceMapperInterface
{
    protected const string ATTRIBUTE_UUID = 'uuid';

    protected const string ATTRIBUTE_COMPANY_UUID = 'companyUuid';

    protected const string ATTRIBUTE_ISO2_CODE = 'iso2Code';

    protected const string ATTRIBUTE_STREET = 'street';

    protected const string ATTRIBUTE_NUMBER = 'number';

    protected const string ATTRIBUTE_ADDITION_TO_ADDRESS = 'additionToAddress';

    protected const string ATTRIBUTE_CITY = 'city';

    protected const string ATTRIBUTE_ZIP_CODE = 'zipCode';

    protected const string ATTRIBUTE_COMMENT = 'comment';

    protected const string ATTRIBUTE_LABELS = 'labels';

    public function __construct(
        protected CompanyReaderInterface $companyReader,
        protected CompanyUnitAddressLabelReaderInterface $companyUnitAddressLabelReader,
        protected CountryReaderInterface $countryReader,
    ) {
    }

    public function mapResourceToCompanyUnitAddressTransfer(
        CompanyBusinessUnitAddressesBackendResource $resource,
        CompanyUnitAddressTransfer $companyUnitAddressTransfer
    ): CompanyUnitAddressTransfer {
        if ($resource->companyUuid !== null) {
            $companyTransfer = $this->companyReader->getCompanyByUuid($resource->companyUuid);
            $companyUnitAddressTransfer
                ->setFkCompany($companyTransfer->getIdCompanyOrFail())
                ->setCompany($companyTransfer);
        }

        if ($resource->iso2Code !== null) {
            $countryTransfer = $this->countryReader->getCountryByIso2Code($resource->iso2Code);
            $companyUnitAddressTransfer
                ->setFkCountry($countryTransfer->getIdCountryOrFail())
                ->setIso2Code($countryTransfer->getIso2Code());
        }

        $companyUnitAddressTransfer
            ->setAddress1($resource->street)
            ->setAddress2($resource->number)
            ->setAddress3($resource->additionToAddress)
            ->setCity($resource->city)
            ->setZipCode($resource->zipCode)
            ->setComment($resource->comment);

        $companyUnitAddressTransfer->setLabelCollection($this->buildLabelCollection($resource->labels));

        return $companyUnitAddressTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function mapCompanyUnitAddressTransferToResourceData(
        CompanyUnitAddressTransfer $companyUnitAddressTransfer
    ): array {
        return [
            static::ATTRIBUTE_UUID => $companyUnitAddressTransfer->getUuid(),
            static::ATTRIBUTE_COMPANY_UUID => $companyUnitAddressTransfer->getCompany()?->getUuid(),
            static::ATTRIBUTE_ISO2_CODE => $companyUnitAddressTransfer->getIso2Code(),
            static::ATTRIBUTE_STREET => $companyUnitAddressTransfer->getAddress1(),
            static::ATTRIBUTE_NUMBER => $companyUnitAddressTransfer->getAddress2(),
            static::ATTRIBUTE_ADDITION_TO_ADDRESS => $companyUnitAddressTransfer->getAddress3(),
            static::ATTRIBUTE_CITY => $companyUnitAddressTransfer->getCity(),
            static::ATTRIBUTE_ZIP_CODE => $companyUnitAddressTransfer->getZipCode(),
            static::ATTRIBUTE_COMMENT => $companyUnitAddressTransfer->getComment(),
            static::ATTRIBUTE_LABELS => $this->extractLabelNames($companyUnitAddressTransfer),
        ];
    }

    /**
     * @param array<int, mixed> $labelNames
     */
    protected function buildLabelCollection(array $labelNames): CompanyUnitAddressLabelCollectionTransfer
    {
        return $this->companyUnitAddressLabelReader->getLabelCollectionByNames(
            array_map(static fn ($labelName): string => (string)$labelName, $labelNames),
        );
    }

    /**
     * @return array<int, string>
     */
    protected function extractLabelNames(CompanyUnitAddressTransfer $companyUnitAddressTransfer): array
    {
        $companyUnitAddressLabelTransfers = $companyUnitAddressTransfer->getLabelCollection()?->getLabels()
            ?? new ArrayObject();

        $labelNames = [];

        foreach ($companyUnitAddressLabelTransfers as $companyUnitAddressLabelTransfer) {
            $labelNames[] = (string)$companyUnitAddressLabelTransfer->getName();
        }

        return $labelNames;
    }
}
