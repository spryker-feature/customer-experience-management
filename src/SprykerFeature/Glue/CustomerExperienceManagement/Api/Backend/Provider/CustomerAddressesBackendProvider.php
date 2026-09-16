<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomersAddressesBackendResource;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\Customer\CustomerConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerAddressResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerAddressReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CustomerAddressesBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerAddressReaderInterface $customerAddressReader,
        protected CustomerAddressResourceMapperInterface $customerAddressResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CustomerConfig $customerConfig,
    ) {
    }

    /**
     * @return array<\Generated\Api\Backend\CustomersAddressesBackendResource>
     */
    protected function provideCollection(): array
    {
        $customerTransfer = $this->getCustomerByUriVariable();
        $paginationTransfer = $this->buildPaginationTransfer();

        $addressCollectionTransfer = $this->customerAddressReader->getAddressCollectionForCustomer(
            $customerTransfer,
            $paginationTransfer,
            $this->collectionQueryReader->getSortCollection(
                $this->getRequest(),
                array_keys($this->customerConfig->getAddressCollectionSortableFieldMap()),
            ),
        );

        $resources = [];

        foreach ($addressCollectionTransfer->getAddresses() as $addressTransfer) {
            $resources[] = $this->buildResource($addressTransfer, $customerTransfer);
        }

        $nbResults = $addressCollectionTransfer->getPagination()?->getNbResults();
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

        $addressTransfer = $this->customerAddressReader->getAddressForCustomer(
            $this->getUuidUriVariable(),
            $customerTransfer,
        );

        return $this->buildResource($addressTransfer, $customerTransfer);
    }

    protected function buildResource(
        AddressTransfer $addressTransfer,
        CustomerTransfer $customerTransfer
    ): CustomersAddressesBackendResource {
        /** @var \Generated\Api\Backend\CustomersAddressesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerAddressResourceMapper->mapAddressTransferToResourceData($addressTransfer, $customerTransfer),
            CustomersAddressesBackendResource::class,
        );

        return $resource;
    }

    protected function getCustomerByUriVariable(): CustomerTransfer
    {
        return $this->customerAddressReader->getCustomerByReference(
            (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE),
        );
    }
}
