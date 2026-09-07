<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CustomerNoteCollectionTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;

interface CustomerNoteReaderInterface
{
    public function getNoteForCustomer(string $uuid, CustomerTransfer $customerTransfer): SpyCustomerNoteEntityTransfer;

    /**
     * @param array<int, \Generated\Shared\Transfer\SortTransfer> $sortTransfers
     */
    public function getNoteCollectionForCustomer(
        CustomerTransfer $customerTransfer,
        ?PaginationTransfer $paginationTransfer = null,
        array $sortTransfers = []
    ): CustomerNoteCollectionTransfer;
}
