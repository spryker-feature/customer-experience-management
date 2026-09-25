<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerGroupsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerGroupResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerGroupReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CustomerGroupsBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    public function __construct(
        protected CustomerGroupFacadeInterface $customerGroupFacade,
        protected SerializerServiceInterface $serializer,
        protected CustomerGroupReaderInterface $customerGroupReader,
        protected CustomerGroupResourceMapperInterface $customerGroupResourceMapper,
        protected CustomerGroupsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        /** @var \Generated\Api\Backend\CustomerGroupsBackendResource $resource */
        $resource = $data;

        $customerGroupTransfer = $this->buildCustomerGroup($resource, new CustomerGroupTransfer())
            ->setHasAssignmentChange($resource->customerReferences !== null)
            ->setCustomerReferences($resource->customerReferences ?? []);

        $customerGroupCollectionResponseTransfer = $this->assertSuccessful(
            $this->customerGroupFacade->createCustomerGroupCollection(
                $this->buildCollectionRequest($customerGroupTransfer),
            ),
        );

        return $this->buildResourceByUuid(
            (string)$customerGroupCollectionResponseTransfer->getCustomerGroups()->offsetGet(0)->getUuid(),
        );
    }

    protected function processPatch(mixed $data): object
    {
        /** @var \Generated\Api\Backend\CustomerGroupsBackendResource $resource */
        $resource = $data;

        $uuid = $this->getUuidUriVariable();

        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($uuid);

        $this->assertSuccessful(
            $this->customerGroupFacade->updateCustomerGroupCollection(
                $this->buildCollectionRequest($this->buildCustomerGroup($resource, $customerGroupTransfer)),
            ),
        );

        return $this->buildResourceByUuid($uuid);
    }

    protected function processDelete(): null
    {
        $customerGroupTransfer = $this->customerGroupReader
            ->getCustomerGroupByUuid($this->getUuidUriVariable());

        $this->customerGroupFacade->delete(
            (new CustomerGroupTransfer())->setIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );

        return null;
    }

    protected function buildCustomerGroup(
        CustomerGroupsBackendResource $resource,
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupTransfer {
        return $this->customerGroupResourceMapper
            ->mapResourceToCustomerGroupTransfer($resource, $customerGroupTransfer);
    }

    protected function buildCollectionRequest(
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupCollectionRequestTransfer {
        return (new CustomerGroupCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCustomerGroup($customerGroupTransfer);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertSuccessful(
        CustomerGroupCollectionResponseTransfer $customerGroupCollectionResponseTransfer
    ): CustomerGroupCollectionResponseTransfer {
        if ($customerGroupCollectionResponseTransfer->getErrors()->count() > 0) {
            throw $this->exceptionFactory->createValidationException($customerGroupCollectionResponseTransfer);
        }

        return $customerGroupCollectionResponseTransfer;
    }

    protected function buildResourceByUuid(string $uuid): CustomerGroupsBackendResource
    {
        /** @var \Generated\Api\Backend\CustomerGroupsBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerGroupResourceMapper->mapCustomerGroupTransferToResourceData(
                $this->customerGroupReader->getCustomerGroupByUuid($uuid),
            ),
            CustomerGroupsBackendResource::class,
        );

        return $resource;
    }
}
