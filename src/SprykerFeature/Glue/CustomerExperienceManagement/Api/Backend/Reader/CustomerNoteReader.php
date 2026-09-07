<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CustomerNoteCollectionTransfer;
use Generated\Shared\Transfer\CustomerNoteConditionsTransfer;
use Generated\Shared\Transfer\CustomerNoteCriteriaTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;
use Spryker\Zed\CustomerNote\Business\CustomerNoteFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerNotesBackendExceptionFactory;

class CustomerNoteReader implements CustomerNoteReaderInterface
{
    public function __construct(
        protected CustomerNoteFacadeInterface $customerNoteFacade,
        protected CustomerNotesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getNoteForCustomer(string $uuid, CustomerTransfer $customerTransfer): SpyCustomerNoteEntityTransfer
    {
        $customerNoteCriteriaTransfer = (new CustomerNoteCriteriaTransfer())
            ->setCustomerNoteConditions(
                (new CustomerNoteConditionsTransfer())
                    ->addUuid($uuid)
                    ->addIdCustomer($customerTransfer->getIdCustomerOrFail()),
            );

        $customerNoteEntityTransfers = $this->customerNoteFacade
            ->getCustomerNoteCollection($customerNoteCriteriaTransfer)
            ->getNotes();

        if ($customerNoteEntityTransfers->count() === 0) {
            throw $this->exceptionFactory->createNoteNotFoundException(
                $uuid,
                (string)$customerTransfer->getCustomerReference(),
            );
        }

        return $customerNoteEntityTransfers->offsetGet(0);
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\SortTransfer> $sortTransfers
     */
    public function getNoteCollectionForCustomer(
        CustomerTransfer $customerTransfer,
        ?PaginationTransfer $paginationTransfer = null,
        array $sortTransfers = []
    ): CustomerNoteCollectionTransfer {
        $customerNoteCriteriaTransfer = (new CustomerNoteCriteriaTransfer())
            ->setCustomerNoteConditions(
                (new CustomerNoteConditionsTransfer())->addIdCustomer($customerTransfer->getIdCustomerOrFail()),
            )
            ->setPagination($paginationTransfer);

        foreach ($sortTransfers as $sortTransfer) {
            $customerNoteCriteriaTransfer->addSort($sortTransfer);
        }

        return $this->customerNoteFacade->getCustomerNoteCollection($customerNoteCriteriaTransfer);
    }
}
