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
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerNoteReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CustomerNotesBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerReaderInterface $customerReader,
        protected CustomerNoteReaderInterface $customerNoteReader,
        protected CustomerNoteResourceMapperInterface $customerNoteResourceMapper,
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

        $paginationTransfer = $this->resolvePaginationTransfer($isRelationshipResolution);

        $customerNoteCollectionTransfer = $this->customerNoteReader->getNoteCollectionForCustomer(
            $customerTransfer,
            $paginationTransfer,
            $this->resolveSortCollection($isRelationshipResolution),
        );

        $resources = [];

        foreach ($customerNoteCollectionTransfer->getNotes() as $customerNoteEntityTransfer) {
            $resources[] = $this->buildResource($customerNoteEntityTransfer, $customerTransfer);
        }

        if ($isRelationshipResolution) {
            return $resources;
        }

        $nbResults = $customerNoteCollectionTransfer->getPagination()?->getNbResults();
        if ($nbResults !== null) {
            $this->setCollectionPagination(
                $paginationTransfer->getOffsetOrFail(),
                $paginationTransfer->getLimitOrFail(),
                $nbResults,
            );
        }

        return $resources;
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
            return $this->buildPaginationTransfer();
        }

        $limit = $this->getOperation()->getPaginationItemsPerPage() ?? static::DEFAULT_PER_PAGE;

        return (new PaginationTransfer())
            ->setPage(static::DEFAULT_PAGE)
            ->setMaxPerPage($limit)
            ->setLimit($limit)
            ->setOffset(static::DEFAULT_OFFSET);
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
}
