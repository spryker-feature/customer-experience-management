<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerGroupsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerGroupReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CustomerGroupCustomersBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected CustomerGroupFacadeInterface $customerGroupFacade,
        protected CustomerGroupReaderInterface $customerGroupReader,
        protected CustomerGroupsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): null
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCustomersBackendResource $resource */
        $resource = $data;

        $this->assertSuccessful(
            $this->customerGroupFacade->updateCustomerGroupCollection(
                $this->buildCollectionRequest($this->buildCustomerGroup($resource->customerReferences ?? [])),
            ),
        );

        return null;
    }

    protected function processPatch(mixed $data): null
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCustomersBackendResource $resource */
        $resource = $data;

        $this->assertSuccessful(
            $this->customerGroupFacade->updateCustomerGroupCollection(
                $this->buildCollectionRequest(
                    $this->buildCustomerGroup($resource->customerReferences ?? [])
                        ->setIsAssignmentReplacement(true),
                ),
            ),
        );

        return null;
    }

    protected function processDelete(): null
    {
        $uuid = $this->getUuidUriVariable();
        $customerReference = (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE);

        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($uuid);
        $idCustomerGroup = $customerGroupTransfer->getIdCustomerGroupOrFail();

        $customerTransfer = $this->customerGroupReader->findAssignedCustomer($idCustomerGroup, $customerReference);

        if ($customerTransfer === null) {
            throw $this->exceptionFactory->createAssignmentNotFoundException($customerReference, $uuid);
        }

        $this->customerGroupFacade->removeCustomersFromGroup(
            (new CustomerGroupTransfer())
                ->setIdCustomerGroup($idCustomerGroup)
                ->setCustomerAssignment(
                    (new CustomerGroupToCustomerAssignmentTransfer())
                        ->setIdCustomerGroup($idCustomerGroup)
                        ->setIdsCustomerToAssign([])
                        ->setIdsCustomerToDeAssign([$customerTransfer->getIdCustomerOrFail()]),
                ),
        );

        return null;
    }

    /**
     * @param array<int, string> $customerReferences
     */
    protected function buildCustomerGroup(array $customerReferences): CustomerGroupTransfer
    {
        return $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable())
            ->setHasAssignmentChange(true)
            ->setCustomerReferences($customerReferences);
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
}
