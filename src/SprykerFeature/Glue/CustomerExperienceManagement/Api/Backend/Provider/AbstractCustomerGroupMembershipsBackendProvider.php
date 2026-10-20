<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\ApiPlatform\State\Request\CollectionQueryReaderInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerGroupsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerGroupResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerGroupReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig as CustomerExperienceManagementGlueConfig;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementFacadeInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig;

abstract class AbstractCustomerGroupMembershipsBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string QUERY_PARAM_SEARCH = 'q';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerGroupReaderInterface $customerGroupReader,
        protected CustomerExperienceManagementFacadeInterface $customerExperienceManagementFacade,
        protected CustomerGroupResourceMapperInterface $customerGroupResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CustomerGroupsBackendExceptionFactory $customerGroupsBackendExceptionFactory,
        protected CustomerExperienceManagementConfig $customerExperienceManagementConfig,
    ) {
    }

    protected function buildPaginationTransfer(int $limit = self::DEFAULT_PER_PAGE, int $offset = self::DEFAULT_OFFSET): PaginationTransfer
    {
        $paginationTransfer = parent::buildPaginationTransfer($limit, $offset);
        $pageLimit = $paginationTransfer->getLimitOrFail();

        return $paginationTransfer->setOffset(intdiv($paginationTransfer->getOffsetOrFail(), $pageLimit) * $pageLimit);
    }

    protected function resolveSearchTerm(): ?string
    {
        $searchTerm = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);

        return is_string($searchTerm) && $searchTerm !== '' ? $searchTerm : null;
    }

    /**
     * @param array<string, string> $sortableFieldMap
     *
     * @return array<int, \Generated\Shared\Transfer\SortTransfer>
     */
    protected function resolveSortCollection(array $sortableFieldMap): array
    {
        return $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($sortableFieldMap),
            CustomerExperienceManagementGlueConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
        );
    }
}
