<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CompanyBusinessUnitAddressesBackendResource;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyUnitAddress\Business\CompanyUnitAddressFacadeInterface;
use Spryker\Zed\CompanyUnitAddress\CompanyUnitAddressConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyBusinessUnitAddressResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyBusinessUnitReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUnitAddressReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;

class CompanyBusinessUnitAddressesBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string FILTER_KEY_PREFIX = 'company-business-unit-addresses.';

    protected const string QUERY_PARAM_SEARCH = 'q';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CompanyUnitAddressFacadeInterface $companyUnitAddressFacade,
        protected CompanyUnitAddressReaderInterface $companyUnitAddressReader,
        protected CompanyBusinessUnitAddressResourceMapperInterface $companyBusinessUnitAddressResourceMapper,
        protected CompanyReaderInterface $companyReader,
        protected CompanyBusinessUnitReaderInterface $companyBusinessUnitReader,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CompanyUnitAddressConfig $companyUnitAddressConfig,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function provideItem(): ?object
    {
        return $this->buildResource(
            $this->companyUnitAddressReader->getCompanyUnitAddressByUuid($this->getUuidUriVariable()),
        );
    }

    protected function provideCollection(): array
    {
        $companyUnitAddressCriteriaFilterTransfer = $this->buildCriteriaFromRequest();
        $companyUnitAddressCollectionTransfer = $this->companyUnitAddressFacade->getCompanyUnitAddressCollection(
            $companyUnitAddressCriteriaFilterTransfer,
        );

        $companyUuidsByIdCompany = $this->companyReader->getCompanyUuidsByCompanyIds(
            $this->extractCompanyIds($companyUnitAddressCollectionTransfer),
        );

        $resources = [];

        foreach ($companyUnitAddressCollectionTransfer->getCompanyUnitAddresses() as $companyUnitAddressTransfer) {
            $companyUnitAddressTransfer->setCompany(
                (new CompanyTransfer())->setUuid($companyUuidsByIdCompany[$companyUnitAddressTransfer->getFkCompany()] ?? null),
            );

            $resources[] = $this->buildResource($companyUnitAddressTransfer);
        }

        $paginationTransfer = $companyUnitAddressCriteriaFilterTransfer->getPaginationOrFail();

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? 0,
        );

        return $resources;
    }

    /**
     * @return array<int, int>
     */
    protected function extractCompanyIds(
        CompanyUnitAddressCollectionTransfer $companyUnitAddressCollectionTransfer
    ): array {
        $companyIds = [];

        foreach ($companyUnitAddressCollectionTransfer->getCompanyUnitAddresses() as $companyUnitAddressTransfer) {
            $idCompany = $companyUnitAddressTransfer->getFkCompany();

            if ($idCompany !== null) {
                $companyIds[$idCompany] = $idCompany;
            }
        }

        return array_values($companyIds);
    }

    protected function buildCriteriaFromRequest(): CompanyUnitAddressCriteriaFilterTransfer
    {
        $companyUnitAddressCriteriaFilterTransfer = (new CompanyUnitAddressCriteriaFilterTransfer())
            ->setPagination($this->buildPaginationTransfer())
            ->setSearchTerm($this->findSearchTerm());

        $companyUnitAddressCriteriaFilterTransfer = $this->applyFiltersFromRequest(
            $companyUnitAddressCriteriaFilterTransfer,
        );

        return $this->applySortFromRequest($companyUnitAddressCriteriaFilterTransfer);
    }

    protected function findSearchTerm(): ?string
    {
        $searchTerm = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);

        return is_string($searchTerm) && $searchTerm !== '' ? $searchTerm : null;
    }

    protected function applyFiltersFromRequest(
        CompanyUnitAddressCriteriaFilterTransfer $companyUnitAddressCriteriaFilterTransfer
    ): CompanyUnitAddressCriteriaFilterTransfer {
        $filters = $this->collectionQueryReader->getFilters($this->getRequest(), static::FILTER_KEY_PREFIX);
        $filterableFieldMap = $this->companyUnitAddressConfig->getCompanyUnitAddressCollectionFilterableFieldMap();

        foreach ($filters as $property => $value) {
            $criteriaProperty = $filterableFieldMap[$property] ?? null;

            if ($criteriaProperty === null) {
                throw $this->exceptionFactory->createUnsupportedFilterFieldException(
                    $property,
                    array_keys($filterableFieldMap),
                );
            }

            if (!is_string($value)) {
                throw $this->exceptionFactory->createNonScalarFilterValueException($property);
            }

            if ($value === '') {
                continue;
            }

            $companyUnitAddressCriteriaFilterTransfer->fromArray(
                [$criteriaProperty => $this->resolveOwnerId($property, $value)],
                true,
            );
        }

        return $companyUnitAddressCriteriaFilterTransfer;
    }

    protected function resolveOwnerId(string $property, string $uuid): int
    {
        if ($property === CustomerExperienceManagementConfig::FILTER_FIELD_COMPANY_UNIT_ADDRESS_COMPANY_UUID) {
            return $this->companyReader->getCompanyByUuid($uuid)->getIdCompanyOrFail();
        }

        return $this->companyBusinessUnitReader->getCompanyBusinessUnitByUuid($uuid)->getIdCompanyBusinessUnitOrFail();
    }

    protected function applySortFromRequest(
        CompanyUnitAddressCriteriaFilterTransfer $companyUnitAddressCriteriaFilterTransfer
    ): CompanyUnitAddressCriteriaFilterTransfer {
        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->companyUnitAddressConfig->getCompanyUnitAddressCollectionSortableFieldMap()),
        );

        foreach ($sortTransfers as $sortTransfer) {
            $companyUnitAddressCriteriaFilterTransfer->addSort($sortTransfer);
        }

        return $companyUnitAddressCriteriaFilterTransfer;
    }

    protected function buildResource(
        CompanyUnitAddressTransfer $companyUnitAddressTransfer
    ): CompanyBusinessUnitAddressesBackendResource {
        /** @var \Generated\Api\Backend\CompanyBusinessUnitAddressesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyBusinessUnitAddressResourceMapper->mapCompanyUnitAddressTransferToResourceData(
                $companyUnitAddressTransfer,
            ),
            CompanyBusinessUnitAddressesBackendResource::class,
        );

        return $resource;
    }
}
