<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CompaniesBackendResource;
use Generated\Shared\Transfer\CompanyCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\Company\CompanyConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompaniesBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CompaniesBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string FILTER_KEY_PREFIX = 'companies.';

    protected const string FILTER_NAME = 'name';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CompanyFacadeInterface $companyFacade,
        protected CompanyReaderInterface $companyReader,
        protected CompanyResourceMapperInterface $companyResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CompanyConfig $companyConfig,
        protected CompaniesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function provideItem(): ?object
    {
        return $this->buildResource($this->companyReader->getCompanyByUuid($this->getUuidUriVariable()));
    }

    protected function provideCollection(): array
    {
        $companyCriteriaFilterTransfer = $this->buildCriteriaFromRequest();
        $companyCollectionTransfer = $this->companyFacade->getCompanyCollection($companyCriteriaFilterTransfer);

        $resources = [];

        foreach ($companyCollectionTransfer->getCompanies() as $companyTransfer) {
            $resources[] = $this->buildResource($companyTransfer);
        }

        $paginationTransfer = $companyCriteriaFilterTransfer->getPaginationOrFail();

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $companyCollectionTransfer->getPagination()?->getNbResults() ?? 0,
        );

        return $resources;
    }

    protected function buildCriteriaFromRequest(): CompanyCriteriaFilterTransfer
    {
        $companyCriteriaFilterTransfer = (new CompanyCriteriaFilterTransfer())
            ->setPagination($this->buildPaginationTransfer());

        $companyCriteriaFilterTransfer = $this->applyFiltersFromRequest($companyCriteriaFilterTransfer);

        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->companyConfig->getCompanyCollectionSortableFieldMap()),
        );

        foreach ($sortTransfers as $sortTransfer) {
            $companyCriteriaFilterTransfer->addSort($sortTransfer);
        }

        return $companyCriteriaFilterTransfer;
    }

    protected function applyFiltersFromRequest(
        CompanyCriteriaFilterTransfer $companyCriteriaFilterTransfer
    ): CompanyCriteriaFilterTransfer {
        $filters = $this->collectionQueryReader->getFilters($this->getRequest(), static::FILTER_KEY_PREFIX);

        foreach ($filters as $property => $value) {
            if ($property !== static::FILTER_NAME) {
                throw $this->exceptionFactory->createUnsupportedFilterFieldException(
                    $property,
                    [static::FILTER_NAME],
                );
            }

            if (!is_string($value)) {
                throw $this->exceptionFactory->createNonScalarFilterValueException($property);
            }

            if ($value === '') {
                continue;
            }

            $companyCriteriaFilterTransfer->setName($value);
        }

        return $companyCriteriaFilterTransfer;
    }

    protected function buildResource(CompanyTransfer $companyTransfer): CompaniesBackendResource
    {
        /** @var \Generated\Api\Backend\CompaniesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyResourceMapper->mapCompanyTransferToResourceData($companyTransfer),
            CompaniesBackendResource::class,
        );

        return $resource;
    }
}
