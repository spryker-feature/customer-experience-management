<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\AddressCollectionTransfer;
use Generated\Shared\Transfer\AddressConditionsTransfer;
use Generated\Shared\Transfer\AddressCriteriaTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerAddressesBackendExceptionFactory;

class CustomerAddressReader implements CustomerAddressReaderInterface
{
    public function __construct(
        protected CustomerFacadeInterface $customerFacade,
        protected CustomerAddressesBackendExceptionFactory $exceptionFactory,
        protected CustomerReaderInterface $customerReader,
    ) {
    }

    public function getCustomerByReference(string $customerReference): CustomerTransfer
    {
        return $this->customerReader->getCustomerByReference($customerReference);
    }

    public function getAddressForCustomer(string $uuid, CustomerTransfer $customerTransfer): AddressTransfer
    {
        $addressCriteriaTransfer = (new AddressCriteriaTransfer())
            ->setAddressConditions(
                (new AddressConditionsTransfer())
                    ->addUuid($uuid)
                    ->addIdCustomer($customerTransfer->getIdCustomerOrFail()),
            );

        $addressTransfers = $this->customerFacade
            ->getAddressCollection($addressCriteriaTransfer)
            ->getAddresses();

        if ($addressTransfers->count() === 0) {
            throw $this->exceptionFactory->createAddressNotFoundException(
                $uuid,
                (string)$customerTransfer->getCustomerReference(),
            );
        }

        return $addressTransfers->offsetGet(0);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<int, \Generated\Shared\Transfer\SortTransfer> $sortTransfers
     */
    public function getAddressCollectionForCustomer(
        CustomerTransfer $customerTransfer,
        ?PaginationTransfer $paginationTransfer = null,
        array $sortTransfers = []
    ): AddressCollectionTransfer {
        $addressCriteriaTransfer = (new AddressCriteriaTransfer())
            ->setAddressConditions(
                (new AddressConditionsTransfer())->addIdCustomer($customerTransfer->getIdCustomerOrFail()),
            )
            ->setPagination($paginationTransfer);

        foreach ($sortTransfers as $sortTransfer) {
            $addressCriteriaTransfer->addSort($sortTransfer);
        }

        return $this->customerFacade->getAddressCollection($addressCriteriaTransfer);
    }
}
