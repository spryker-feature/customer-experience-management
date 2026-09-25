<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerGroupsBackendExceptionFactory;

class CustomerGroupReader implements CustomerGroupReaderInterface
{
    public function __construct(
        protected CustomerGroupFacadeInterface $customerGroupFacade,
        protected CustomerGroupsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function getCustomerGroupByUuid(string $uuid): CustomerGroupTransfer
    {
        $customerGroupTransfers = $this->customerGroupFacade
            ->getCustomerGroupCollection(
                (new CustomerGroupCriteriaTransfer())->setCustomerGroupConditions(
                    (new CustomerGroupConditionsTransfer())->addUuid($uuid),
                ),
            )
            ->getGroups();

        if ($customerGroupTransfers->count() === 0) {
            throw $this->exceptionFactory->createCustomerGroupNotFoundException($uuid);
        }

        return $customerGroupTransfers->offsetGet(0);
    }

    public function getCustomerGroupCollection(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): CustomerGroupCollectionTransfer {
        return $this->customerGroupFacade->getCustomerGroupCollection($customerGroupCriteriaTransfer);
    }

    public function getCustomerCollectionByCustomerGroupCriteria(
        CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
    ): CustomerCollectionTransfer {
        return $this->customerGroupFacade
            ->getCustomerCollectionByCustomerGroupCriteria($customerGroupCustomerCriteriaTransfer);
    }

    public function findAssignedCustomer(int $idCustomerGroup, string $customerReference): ?CustomerTransfer
    {
        $customerCollectionTransfer = $this->customerGroupFacade->getCustomerCollectionByCustomerGroupCriteria(
            (new CustomerGroupCustomerCriteriaTransfer())->setCustomerGroupCustomerConditions(
                (new CustomerGroupCustomerConditionsTransfer())
                    ->setCustomerGroupIds([$idCustomerGroup])
                    ->addCustomerReference($customerReference),
            ),
        );

        return $customerCollectionTransfer->getCustomers()->count() === 0
            ? null
            : $customerCollectionTransfer->getCustomers()->offsetGet(0);
    }
}
