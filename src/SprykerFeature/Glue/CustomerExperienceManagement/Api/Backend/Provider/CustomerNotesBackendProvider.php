<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use ApiPlatform\Metadata\HttpOperation;
use Generated\Api\Backend\CustomersNotesBackendResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerNote\CustomerNoteConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerNoteResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\PaginationResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerNoteReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;

class CustomerNotesBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    protected const string URI_VARIABLE_UUID = 'uuid';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerReaderInterface $customerReader,
        protected CustomerNoteReaderInterface $customerNoteReader,
        protected CustomerNoteResourceMapperInterface $customerNoteResourceMapper,
        protected PaginationResourceMapperInterface $paginationResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CustomerNoteConfig $customerNoteConfig,
    ) {
    }

    /**
     * @return array<\Generated\Api\Backend\CustomersNotesBackendResource>
     */
    protected function provideCollection(): array
    {
        $customerTransfer = $this->getCustomerByUriVariable();
        $isRelationshipResolution = $this->isRelationshipResolution();

        $customerNoteCollectionTransfer = $this->customerNoteReader->getNoteCollectionForCustomer(
            $customerTransfer,
            $this->resolvePaginationTransfer($isRelationshipResolution),
            $this->resolveSortCollection($isRelationshipResolution),
        );

        $resources = [];

        foreach ($customerNoteCollectionTransfer->getNotes() as $customerNoteEntityTransfer) {
            $resources[] = $this->buildResource($customerNoteEntityTransfer, $customerTransfer);
        }

        if ($isRelationshipResolution) {
            return $resources;
        }

        return $this->expandFirstResourceWithPagination(
            $resources,
            $customerNoteCollectionTransfer->getPagination(),
        );
    }

    protected function provideItem(): ?object
    {
        $customerTransfer = $this->getCustomerByUriVariable();

        return $this->buildResource(
            $this->customerNoteReader->getNoteForCustomer($this->getUuidUriVariable(), $customerTransfer),
            $customerTransfer,
        );
    }

    protected function isRelationshipResolution(): bool
    {
        $operation = $this->getOperation();

        return !$operation instanceof HttpOperation || $operation->getUriTemplate() === null;
    }

    protected function resolvePaginationTransfer(bool $isRelationshipResolution): PaginationTransfer
    {
        if (!$isRelationshipResolution) {
            return $this->collectionQueryReader->getPaginationTransfer(
                $this->getRequest(),
                $this->getOperation()->getPaginationItemsPerPage(),
            );
        }

        return (new PaginationTransfer())
            ->setPage(static::DEFAULT_PAGE)
            ->setMaxPerPage($this->getOperation()->getPaginationItemsPerPage() ?? static::DEFAULT_PER_PAGE);
    }

    /**
     * @return array<int, \Generated\Shared\Transfer\SortTransfer>
     */
    protected function resolveSortCollection(bool $isRelationshipResolution): array
    {
        if ($isRelationshipResolution) {
            return [];
        }

        return $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->customerNoteConfig->getCustomerNoteCollectionSortableFieldMap()),
        );
    }

    /**
     * @param array<\Generated\Api\Backend\CustomersNotesBackendResource> $resources
     *
     * @return array<\Generated\Api\Backend\CustomersNotesBackendResource>
     */
    protected function expandFirstResourceWithPagination(array $resources, ?PaginationTransfer $paginationTransfer): array
    {
        if ($resources === [] || $paginationTransfer === null) {
            return $resources;
        }

        $resources[0]->pagination = $this->paginationResourceMapper
            ->mapPaginationTransferToPagination($paginationTransfer);

        return $resources;
    }

    protected function buildResource(
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer,
        CustomerTransfer $customerTransfer
    ): CustomersNotesBackendResource {
        /** @var \Generated\Api\Backend\CustomersNotesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerNoteResourceMapper->mapCustomerNoteEntityTransferToResourceData(
                $customerNoteEntityTransfer,
                $customerTransfer,
            ),
            CustomersNotesBackendResource::class,
        );

        return $resource;
    }

    protected function getCustomerByUriVariable(): CustomerTransfer
    {
        return $this->customerReader->getCustomerByReference(
            (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE),
        );
    }

    protected function getUuidUriVariable(): string
    {
        return (string)($this->findUriVariable(static::URI_VARIABLE_UUID)
            ?? $this->getRequest()->attributes->get(static::URI_VARIABLE_UUID));
    }
}
