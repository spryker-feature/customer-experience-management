<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CustomersBackendResource;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomersBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerResourceMapperInterface;

class CustomersBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected CustomerFacadeInterface $customerFacade,
        protected SerializerServiceInterface $serializer,
        protected CustomersBackendExceptionFactory $exceptionFactory,
        protected CustomerResourceMapperInterface $customerResourceMapper,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        return $this->registerCustomer($data);
    }

    protected function processPatch(mixed $data): object
    {
        return $this->updateCustomer($data);
    }

    protected function processDelete(): null
    {
        return $this->anonymizeCustomer();
    }

    protected function registerCustomer(mixed $data): CustomersBackendResource
    {
        $customerTransfer = $this->buildCustomerTransfer($data, new CustomerTransfer());

        $this->assertStoreNameForPasswordToken($customerTransfer);

        return $this->saveCustomer($this->customerFacade->registerCustomer($customerTransfer));
    }

    protected function updateCustomer(mixed $data): CustomersBackendResource
    {
        $customerTransfer = $this->buildCustomerTransfer($data, $this->getCustomerByUriVariable());
        $customerTransfer->setIsEditedInBackoffice(true);

        $this->assertStoreNameForPasswordToken($customerTransfer);

        return $this->saveCustomer($this->customerFacade->updateCustomer($customerTransfer));
    }

    protected function assertStoreNameForPasswordToken(CustomerTransfer $customerTransfer): void
    {
        if ($customerTransfer->getSendPasswordToken() !== true || $customerTransfer->getStoreName()) {
            return;
        }

        throw $this->exceptionFactory->createStoreNameRequiredException();
    }

    protected function anonymizeCustomer(): null
    {
        $this->customerFacade->anonymizeCustomer($this->getCustomerByUriVariable());

        return null;
    }

    protected function buildCustomerTransfer(mixed $data, CustomerTransfer $customerTransfer): CustomerTransfer
    {
        /** @var \Generated\Api\Backend\CustomersBackendResource $data */
        return $this->customerResourceMapper->mapResourceToCustomerTransfer($data, $customerTransfer);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function saveCustomer(CustomerResponseTransfer $customerResponseTransfer): CustomersBackendResource
    {
        if (!$customerResponseTransfer->getIsSuccess()) {
            throw $this->exceptionFactory->createExceptionFromCustomerResponse($customerResponseTransfer);
        }

        return $this->buildCustomersBackendResource($customerResponseTransfer->getCustomerTransferOrFail());
    }

    protected function buildCustomersBackendResource(CustomerTransfer $customerTransfer): CustomersBackendResource
    {
        /** @var \Generated\Api\Backend\CustomersBackendResource $customersBackendResource */
        $customersBackendResource = $this->serializer->denormalize(
            $this->customerResourceMapper->mapCustomerTransferToResourceData($customerTransfer),
            CustomersBackendResource::class,
        );

        return $customersBackendResource;
    }

    protected function getCustomerByUriVariable(): CustomerTransfer
    {
        $customerReference = (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE);

        $customerResponseTransfer = $this->customerFacade->findCustomerByReference($customerReference);

        if (!$customerResponseTransfer->getHasCustomer()) {
            throw $this->exceptionFactory->createCustomerNotFoundException($customerReference);
        }

        return $customerResponseTransfer->getCustomerTransferOrFail();
    }
}
