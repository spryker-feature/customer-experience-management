<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\CompanyBusinessUnitConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyBusinessUnitResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;

class CompanyBusinessUnitsBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string FILTER_KEY_PREFIX = 'company-business-units.';

    protected const string QUERY_PARAM_SEARCH = 'q';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected CompanyBusinessUnitResourceMapperInterface $companyBusinessUnitResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CompanyBusinessUnitConfig $companyBusinessUnitConfig,
        protected CustomerExperienceManagementConfig $config,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function provideItem(): ?object
    {
        $uuid = $this->getUuidUriVariable();

        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())->addUuid($uuid),
        );

        if (!$companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits()->count()) {
            throw $this->exceptionFactory->createCompanyBusinessUnitNotFoundException($uuid);
        }

        return $this->buildResource($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits()->getIterator()->current());
    }

    protected function provideCollection(): array
    {
        $companyBusinessUnitCriteriaFilterTransfer = $this->buildCriteriaFromRequest();
        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            $companyBusinessUnitCriteriaFilterTransfer,
        );

        $resources = [];

        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $resources[] = $this->buildResource($companyBusinessUnitTransfer);
        }

        $paginationTransfer = $companyBusinessUnitCriteriaFilterTransfer->getPaginationOrFail();

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $companyBusinessUnitCollectionTransfer->getPagination()?->getNbResults() ?? 0,
        );

        return $resources;
    }

    protected function buildCriteriaFromRequest(): CompanyBusinessUnitCriteriaFilterTransfer
    {
        $companyBusinessUnitCriteriaFilterTransfer = (new CompanyBusinessUnitCriteriaFilterTransfer())
            ->setPagination($this->buildPaginationTransfer())
            ->setSearchTerm($this->findSearchTerm());

        $companyBusinessUnitCriteriaFilterTransfer = $this->applyFiltersFromRequest(
            $companyBusinessUnitCriteriaFilterTransfer,
        );

        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->companyBusinessUnitConfig->getCompanyBusinessUnitCollectionSortableFieldMap()),
        );

        foreach ($sortTransfers as $sortTransfer) {
            $companyBusinessUnitCriteriaFilterTransfer->addSort($sortTransfer);
        }

        return $companyBusinessUnitCriteriaFilterTransfer;
    }

    protected function findSearchTerm(): ?string
    {
        $searchTerm = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);

        return is_string($searchTerm) && $searchTerm !== '' ? $searchTerm : null;
    }

    protected function applyFiltersFromRequest(
        CompanyBusinessUnitCriteriaFilterTransfer $companyBusinessUnitCriteriaFilterTransfer
    ): CompanyBusinessUnitCriteriaFilterTransfer {
        $filters = $this->collectionQueryReader->getFilters($this->getRequest(), static::FILTER_KEY_PREFIX);
        $filterableFieldMap = $this->companyBusinessUnitConfig->getCompanyBusinessUnitCollectionFilterableFieldMap();

        foreach ($filters as $filterField => $value) {
            $criteriaProperty = $filterableFieldMap[$filterField] ?? null;

            if ($criteriaProperty === null) {
                throw $this->exceptionFactory->createUnsupportedFilterFieldException(
                    $filterField,
                    array_keys($filterableFieldMap),
                );
            }

            if (!is_string($value)) {
                throw $this->exceptionFactory->createNonScalarFilterValueException($filterField);
            }

            if ($value === '') {
                continue;
            }

            $companyBusinessUnitCriteriaFilterTransfer->fromArray([$criteriaProperty => $value], true);
        }

        return $companyBusinessUnitCriteriaFilterTransfer;
    }

    protected function buildResource(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): CompanyBusinessUnitsBackendResource {
        /** @var \Generated\Api\Backend\CompanyBusinessUnitsBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyBusinessUnitResourceMapper->mapCompanyBusinessUnitTransferToResourceData(
                $companyBusinessUnitTransfer,
            ),
            CompanyBusinessUnitsBackendResource::class,
        );

        return $resource;
    }
}
