<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomerGroupsCompaniesBackendResource;
use Generated\Shared\Transfer\CustomerGroupCompanyConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyTransfer;

class CustomerGroupCompaniesBackendProvider extends AbstractCustomerGroupMembershipsBackendProvider
{
    protected const string FILTER_KEY_PREFIX = 'customer-group-companies.';

    /**
     * @return array<\Generated\Api\Backend\CustomerGroupsCompaniesBackendResource>
     */
    protected function provideCollection(): array
    {
        $uuid = $this->getUuidUriVariable();
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($uuid);
        $this->assertNoFilters();

        $paginationTransfer = $this->buildPaginationTransfer();
        $customerGroupCompanyCriteriaTransfer = (new CustomerGroupCompanyCriteriaTransfer())
            ->setCustomerGroupCompanyConditions(
                (new CustomerGroupCompanyConditionsTransfer())
                    ->addIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
                    ->setSearchTerm($this->resolveSearchTerm()),
            )
            ->setPagination($paginationTransfer);

        foreach ($this->resolveSortCollection($this->customerExperienceManagementConfig->getCustomerGroupCompanyCollectionSortableFieldMap()) as $sortTransfer) {
            $customerGroupCompanyCriteriaTransfer->addSort($sortTransfer);
        }

        $customerGroupCompanyCollectionTransfer = $this->customerExperienceManagementFacade
            ->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer);

        $resources = [];

        foreach ($customerGroupCompanyCollectionTransfer->getCustomerGroupCompanies() as $customerGroupCompanyTransfer) {
            $resources[] = $this->buildResource($customerGroupCompanyTransfer, $uuid);
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? count($resources),
        );

        return $resources;
    }

    protected function assertNoFilters(): void
    {
        $filters = $this->collectionQueryReader->getFilters($this->getRequest(), static::FILTER_KEY_PREFIX);

        if ($filters === []) {
            return;
        }

        throw $this->customerGroupsBackendExceptionFactory->createUnsupportedFilterFieldException((string)array_key_first($filters), []);
    }

    protected function buildResource(
        CustomerGroupCompanyTransfer $customerGroupCompanyTransfer,
        string $uuid
    ): CustomerGroupsCompaniesBackendResource {
        /** @var \Generated\Api\Backend\CustomerGroupsCompaniesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerGroupResourceMapper->mapCustomerGroupCompanyTransferToResourceData($customerGroupCompanyTransfer, $uuid),
            CustomerGroupsCompaniesBackendResource::class,
        );

        return $resource;
    }
}
