<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\ApiPlatform\State\Request\CollectionQueryReaderInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerGroupResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerGroupReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;

class CustomerGroupsBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string QUERY_PARAM_SEARCH = 'q';

    protected const string FILTER_KEY_PREFIX = 'customer-groups.';

    protected const string FILTER_NAME = 'name';

    protected const string FILTER_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FILTER_COMPANY_UUID = 'companyUuid';

    protected const string FILTER_COMPANY_BUSINESS_UNIT_UUID = 'companyBusinessUnitUuid';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerGroupReaderInterface $customerGroupReader,
        protected CustomerGroupResourceMapperInterface $customerGroupResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CustomerGroupConfig $customerGroupConfig,
    ) {
    }

    protected function provideItem(): ?object
    {
        return $this->buildResource(
            $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable()),
        );
    }

    /**
     * @return array<\Generated\Api\Backend\CustomerGroupsBackendResource>
     */
    protected function provideCollection(): array
    {
        $customerGroupCriteriaTransfer = $this->buildCriteriaFromRequest();

        $customerGroupCollectionTransfer = $this->customerGroupReader
            ->getCustomerGroupCollection($customerGroupCriteriaTransfer);

        $resources = [];

        foreach ($customerGroupCollectionTransfer->getGroups() as $customerGroupTransfer) {
            $resources[] = $this->buildResource($customerGroupTransfer);
        }

        $paginationTransfer = $customerGroupCriteriaTransfer->getPaginationOrFail();
        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? count($resources),
        );

        return $resources;
    }

    protected function buildCriteriaFromRequest(): CustomerGroupCriteriaTransfer
    {
        $request = $this->getRequest();

        $customerGroupConditionsTransfer = new CustomerGroupConditionsTransfer();

        $searchTerm = $request->query->get(static::QUERY_PARAM_SEARCH);

        if (is_string($searchTerm) && $searchTerm !== '') {
            $customerGroupConditionsTransfer->setSearchTerm($searchTerm);
        }

        $filters = $this->collectionQueryReader->getFilters($request, static::FILTER_KEY_PREFIX);

        if (isset($filters[static::FILTER_NAME]) && is_string($filters[static::FILTER_NAME])) {
            $customerGroupConditionsTransfer->addName($filters[static::FILTER_NAME]);
        }

        $customerGroupConditionsTransfer
            ->setCustomerReferences($this->findUniqueFilterValues($filters, static::FILTER_CUSTOMER_REFERENCE))
            ->setCompanyUuids($this->findUniqueFilterValues($filters, static::FILTER_COMPANY_UUID))
            ->setCompanyBusinessUnitUuids($this->findUniqueFilterValues($filters, static::FILTER_COMPANY_BUSINESS_UNIT_UUID));

        $customerGroupCriteriaTransfer = (new CustomerGroupCriteriaTransfer())
            ->setCustomerGroupConditions($customerGroupConditionsTransfer)
            ->setPagination($this->buildPaginationTransfer());

        foreach (
            $this->collectionQueryReader->getSortCollection(
                $request,
                $this->getSortableFields(),
                CustomerExperienceManagementConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
            ) as $sortTransfer
        ) {
            $customerGroupCriteriaTransfer->addSort($sortTransfer);
        }

        return $customerGroupCriteriaTransfer;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return list<string>
     */
    protected function findUniqueFilterValues(array $filters, string $key): array
    {
        return array_values(array_unique($this->collectionQueryReader->findFilterValues($filters, $key)));
    }

    /**
     * @return array<int, string>
     */
    protected function getSortableFields(): array
    {
        return array_keys($this->customerGroupConfig->getCustomerGroupCollectionSortableFieldMap());
    }

    protected function buildResource(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupsBackendResource
    {
        /** @var \Generated\Api\Backend\CustomerGroupsBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerGroupResourceMapper->mapCustomerGroupTransferToResourceData($customerGroupTransfer),
            CustomerGroupsBackendResource::class,
        );

        return $resource;
    }
}
