<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomerGroupsCustomersBackendResource;
use Generated\Shared\Transfer\CustomerGroupCustomerConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerGroupResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerGroupReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CustomerGroupCustomersBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string QUERY_PARAM_SEARCH = 'q';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerGroupReaderInterface $customerGroupReader,
        protected CustomerGroupResourceMapperInterface $customerGroupResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CustomerGroupConfig $customerGroupConfig,
    ) {
    }

    /**
     * @return array<\Generated\Api\Backend\CustomerGroupsCustomersBackendResource>
     */
    protected function provideCollection(): array
    {
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable());

        $paginationTransfer = $this->buildPaginationTransfer();

        $customerGroupCustomerCriteriaTransfer = (new CustomerGroupCustomerCriteriaTransfer())
            ->setCustomerGroupCustomerConditions(
                (new CustomerGroupCustomerConditionsTransfer())
                    ->setCustomerGroupIds([$customerGroupTransfer->getIdCustomerGroupOrFail()])
                    ->setSearchTerm($this->resolveSearchTerm()),
            )
            ->setPagination($paginationTransfer);

        foreach ($this->resolveSortCollection() as $sortTransfer) {
            $customerGroupCustomerCriteriaTransfer->addSort($sortTransfer);
        }

        $customerCollectionTransfer = $this->customerGroupReader
            ->getCustomerCollectionByCustomerGroupCriteria($customerGroupCustomerCriteriaTransfer);

        $resources = [];

        foreach ($customerCollectionTransfer->getCustomers() as $customerTransfer) {
            $resources[] = $this->buildResource($customerTransfer);
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? count($resources),
        );

        return $resources;
    }

    protected function resolveSearchTerm(): ?string
    {
        $searchTerm = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);

        return is_string($searchTerm) && $searchTerm !== '' ? $searchTerm : null;
    }

    /**
     * @return array<int, \Generated\Shared\Transfer\SortTransfer>
     */
    protected function resolveSortCollection(): array
    {
        return $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->customerGroupConfig->getCustomerGroupCustomerCollectionSortableFieldMap()),
        );
    }

    protected function buildResource(CustomerTransfer $customerTransfer): CustomerGroupsCustomersBackendResource
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCustomersBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerGroupResourceMapper->mapCustomerTransferToMemberResourceData($customerTransfer),
            CustomerGroupsCustomersBackendResource::class,
        );

        return $resource;
    }
}
