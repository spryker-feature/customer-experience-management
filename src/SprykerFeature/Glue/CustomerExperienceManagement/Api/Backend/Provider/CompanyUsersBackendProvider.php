<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyUser\CompanyUserConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyUserResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUserReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;

class CompanyUsersBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_UUID = 'uuid';

    protected const string QUERY_PARAM_FILTER = 'filter';

    protected const string FILTER_KEY_PREFIX = 'company-users.';

    protected const string QUERY_PARAM_SEARCH = 'q';

    protected const string FILTER_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FILTER_COMPANY_UUID = 'companyUuid';

    protected const string FILTER_COMPANY_BUSINESS_UNIT_UUID = 'companyBusinessUnitUuid';

    protected const string FILTER_IS_ACTIVE = 'isActive';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CompanyUserReaderInterface $companyUserReader,
        protected CompanyUserResourceMapperInterface $companyUserResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CompanyUserConfig $companyUserConfig,
    ) {
    }

    protected function provideItem(): ?object
    {
        return $this->buildResource(
            $this->companyUserReader->getCompanyUserByUuid(
                (string)$this->getUriVariable(static::URI_VARIABLE_UUID),
            ),
        );
    }

    /**
     * @return array<\Generated\Api\Backend\CompanyUsersBackendResource>
     */
    protected function provideCollection(): array
    {
        $companyUserCriteriaFilterTransfer = $this->buildCriteriaFilterFromRequest();
        $companyUserCollectionTransfer = $this->companyUserReader
            ->getCompanyUserCollection($companyUserCriteriaFilterTransfer);

        $companyUsersBackendResources = [];

        foreach ($companyUserCollectionTransfer->getCompanyUsers() as $companyUserTransfer) {
            $companyUsersBackendResources[] = $this->buildResource($companyUserTransfer);
        }

        $nbResults = $companyUserCollectionTransfer->getPagination()?->getNbResults();

        if ($nbResults !== null) {
            $paginationTransfer = $companyUserCriteriaFilterTransfer->getPaginationOrFail();
            $this->setCollectionPagination(
                $paginationTransfer->getOffsetOrFail(),
                $paginationTransfer->getLimitOrFail(),
                $nbResults,
            );
        }

        return $companyUsersBackendResources;
    }

    protected function buildResource(CompanyUserTransfer $companyUserTransfer): CompanyUsersBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyUsersBackendResource $companyUsersBackendResource */
        $companyUsersBackendResource = $this->serializer->denormalize(
            $this->companyUserResourceMapper->mapCompanyUserTransferToResourceData($companyUserTransfer),
            CompanyUsersBackendResource::class,
        );

        return $companyUsersBackendResource;
    }

    protected function buildCriteriaFilterFromRequest(): CompanyUserCriteriaFilterTransfer
    {
        $companyUserCriteriaFilterTransfer = (new CompanyUserCriteriaFilterTransfer())
            ->setPagination($this->buildPaginationTransfer());

        $filters = $this->extractFiltersFromRequest();

        if (!empty($filters[static::FILTER_CUSTOMER_REFERENCE])) {
            $companyUserCriteriaFilterTransfer->addCustomerReference((string)$filters[static::FILTER_CUSTOMER_REFERENCE]);
        }

        if (isset($filters[static::FILTER_IS_ACTIVE]) && $filters[static::FILTER_IS_ACTIVE] !== '') {
            $companyUserCriteriaFilterTransfer->setIsActive(
                filter_var($filters[static::FILTER_IS_ACTIVE], FILTER_VALIDATE_BOOLEAN),
            );
        }

        $searchQuery = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);

        if (is_string($searchQuery) && $searchQuery !== '') {
            $companyUserCriteriaFilterTransfer->setCustomerName($searchQuery);
        }

        $companyUserCriteriaFilterTransfer = $this->applyUuidFilters($companyUserCriteriaFilterTransfer, $filters);

        return $this->applySort($companyUserCriteriaFilterTransfer);
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function applyUuidFilters(
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer,
        array $filters
    ): CompanyUserCriteriaFilterTransfer {
        if (!empty($filters[static::FILTER_COMPANY_UUID])) {
            $companyUserCriteriaFilterTransfer->setIdCompany(
                $this->companyUserReader->findIdCompanyByUuid((string)$filters[static::FILTER_COMPANY_UUID]),
            );
        }

        if (!empty($filters[static::FILTER_COMPANY_BUSINESS_UNIT_UUID])) {
            $companyUserCriteriaFilterTransfer->setIdCompanyBusinessUnit(
                $this->companyUserReader->findIdCompanyBusinessUnitByUuid(
                    (string)$filters[static::FILTER_COMPANY_BUSINESS_UNIT_UUID],
                ),
            );
        }

        return $companyUserCriteriaFilterTransfer;
    }

    protected function applySort(
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
    ): CompanyUserCriteriaFilterTransfer {
        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->companyUserConfig->getCompanyUserCollectionSortableFieldMap()),
        );

        foreach ($sortTransfers as $sortTransfer) {
            $companyUserCriteriaFilterTransfer->addSort($sortTransfer);
        }

        return $companyUserCriteriaFilterTransfer;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractFiltersFromRequest(): array
    {
        $filters = [];

        foreach ($this->getRequest()->query->all(static::QUERY_PARAM_FILTER) as $key => $value) {
            $property = str_starts_with($key, static::FILTER_KEY_PREFIX)
                ? substr($key, strlen(static::FILTER_KEY_PREFIX))
                : $key;

            $filters[$property] = $value;
        }

        return $filters;
    }
}
