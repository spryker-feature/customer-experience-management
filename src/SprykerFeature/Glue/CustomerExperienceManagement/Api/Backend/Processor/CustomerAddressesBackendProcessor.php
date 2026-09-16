<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CustomersAddressesBackendResource;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerAddressesBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerAddressResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerAddressReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CustomerAddressesBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected CustomerFacadeInterface $customerFacade,
        protected SerializerServiceInterface $serializer,
        protected CustomerAddressReaderInterface $customerAddressReader,
        protected CustomerAddressesBackendExceptionFactory $exceptionFactory,
        protected CustomerAddressResourceMapperInterface $customerAddressResourceMapper,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        return $this->createAddress($data);
    }

    protected function processPatch(mixed $data): object
    {
        return $this->updateAddress($data);
    }

    protected function processDelete(): null
    {
        return $this->deleteAddress();
    }

    protected function createAddress(mixed $data): CustomersAddressesBackendResource
    {
        $customerTransfer = $this->getCustomerByUriVariable();

        $addressTransfer = $this->buildAddressTransfer($data, new AddressTransfer())
            ->setFkCustomer($customerTransfer->getIdCustomerOrFail());

        $uuid = (string)$this->customerFacade->createAddress($addressTransfer)->getUuid();

        $customerTransfer = $this->reloadCustomer($customerTransfer);

        return $this->buildResource(
            $this->customerAddressReader->getAddressForCustomer($uuid, $customerTransfer),
            $customerTransfer,
        );
    }

    protected function updateAddress(mixed $data): CustomersAddressesBackendResource
    {
        $customerTransfer = $this->getCustomerByUriVariable();
        $uuid = $this->getUuidUriVariable();

        $addressTransfer = $this->buildAddressTransfer(
            $data,
            $this->customerAddressReader->getAddressForCustomer($uuid, $customerTransfer),
        );

        $this->customerFacade->updateAddressAndCustomerDefaultAddresses($addressTransfer);

        $customerTransfer = $this->reloadCustomer($customerTransfer);

        return $this->buildResource(
            $this->customerAddressReader->getAddressForCustomer($uuid, $customerTransfer),
            $customerTransfer,
        );
    }

    protected function deleteAddress(): null
    {
        $customerTransfer = $this->getCustomerByUriVariable();

        $addressTransfer = $this->customerAddressReader->getAddressForCustomer(
            $this->getUuidUriVariable(),
            $customerTransfer,
        );

        $this->customerFacade->deleteAddress($addressTransfer);

        return null;
    }

    protected function buildAddressTransfer(mixed $data, AddressTransfer $addressTransfer): AddressTransfer
    {
        /** @var \Generated\Api\Backend\CustomersAddressesBackendResource $resource */
        $resource = $data;

        $addressTransfer = $this->customerAddressResourceMapper->mapResourceToAddressTransfer($resource, $addressTransfer);

        $addressResponseTransfer = $this->customerFacade->validateAddress($addressTransfer);

        if (!$addressResponseTransfer->getIsSuccess()) {
            throw $this->exceptionFactory->createAddressValidationException($addressResponseTransfer);
        }

        return $addressTransfer;
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
        return $this->customerAddressReader->getCustomerByReference($this->getCustomerReferenceUriVariable());
    }

    protected function reloadCustomer(CustomerTransfer $customerTransfer): CustomerTransfer
    {
        return $this->customerAddressReader->getCustomerByReference(
            (string)$customerTransfer->getCustomerReference(),
        );
    }

    protected function getCustomerReferenceUriVariable(): string
    {
        return (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE);
    }
}
