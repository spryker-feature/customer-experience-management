<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomerGroupsCompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitTransfer;

class CustomerGroupCompanyBusinessUnitsBackendProvider extends AbstractCustomerGroupMembershipsBackendProvider
{
    protected const string FILTER_KEY_PREFIX = 'customer-group-company-business-units.';

    protected const string FILTER_COMPANY_UUID = 'companyUuid';

    /**
     * @return array<\Generated\Api\Backend\CustomerGroupsCompanyBusinessUnitsBackendResource>
     */
    protected function provideCollection(): array
    {
        $uuid = $this->getUuidUriVariable();
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($uuid);
        $customerGroupCompanyBusinessUnitConditionsTransfer = $this->applyFilters(
            (new CustomerGroupCompanyBusinessUnitConditionsTransfer())
                ->addIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
                ->setSearchTerm($this->resolveSearchTerm()),
        );

        $paginationTransfer = $this->buildPaginationTransfer();
        $customerGroupCompanyBusinessUnitCriteriaTransfer = (new CustomerGroupCompanyBusinessUnitCriteriaTransfer())
            ->setCustomerGroupCompanyBusinessUnitConditions($customerGroupCompanyBusinessUnitConditionsTransfer)
            ->setPagination($paginationTransfer);

        $sortableFieldMap = $this->customerExperienceManagementConfig->getCustomerGroupCompanyBusinessUnitCollectionSortableFieldMap();

        foreach ($this->resolveSortCollection($sortableFieldMap) as $sortTransfer) {
            $customerGroupCompanyBusinessUnitCriteriaTransfer->addSort($sortTransfer);
        }

        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->customerExperienceManagementFacade
            ->getCustomerGroupCompanyBusinessUnitCollection($customerGroupCompanyBusinessUnitCriteriaTransfer);

        $resources = [];

        foreach ($customerGroupCompanyBusinessUnitCollectionTransfer->getCustomerGroupCompanyBusinessUnits() as $customerGroupCompanyBusinessUnitTransfer) {
            $resources[] = $this->buildResource($customerGroupCompanyBusinessUnitTransfer, $uuid);
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? count($resources),
        );

        return $resources;
    }

    protected function applyFilters(
        CustomerGroupCompanyBusinessUnitConditionsTransfer $customerGroupCompanyBusinessUnitConditionsTransfer
    ): CustomerGroupCompanyBusinessUnitConditionsTransfer {
        foreach ($this->collectionQueryReader->getFilters($this->getRequest(), static::FILTER_KEY_PREFIX) as $property => $value) {
            if ($property !== static::FILTER_COMPANY_UUID) {
                throw $this->customerGroupsBackendExceptionFactory->createUnsupportedFilterFieldException((string)$property, [static::FILTER_COMPANY_UUID]);
            }

            if (!is_string($value)) {
                throw $this->customerGroupsBackendExceptionFactory->createNonScalarFilterValueException((string)$property);
            }

            if ($value === '') {
                continue;
            }

            $customerGroupCompanyBusinessUnitConditionsTransfer->addCompanyUuid($value);
        }

        return $customerGroupCompanyBusinessUnitConditionsTransfer;
    }

    protected function buildResource(
        CustomerGroupCompanyBusinessUnitTransfer $customerGroupCompanyBusinessUnitTransfer,
        string $uuid
    ): CustomerGroupsCompanyBusinessUnitsBackendResource {
        /** @var \Generated\Api\Backend\CustomerGroupsCompanyBusinessUnitsBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerGroupResourceMapper->mapCustomerGroupCompanyBusinessUnitTransferToResourceData($customerGroupCompanyBusinessUnitTransfer, $uuid),
            CustomerGroupsCompanyBusinessUnitsBackendResource::class,
        );

        return $resource;
    }
}
