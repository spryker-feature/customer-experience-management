<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\AddressCollectionTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;

interface CustomerAddressReaderInterface
{
    public function getCustomerByReference(string $customerReference): CustomerTransfer;

    public function getAddressForCustomer(string $uuid, CustomerTransfer $customerTransfer): AddressTransfer;

    /**
     * @param array<int, \Generated\Shared\Transfer\SortTransfer> $sortTransfers
     */
    public function getAddressCollectionForCustomer(
        CustomerTransfer $customerTransfer,
        ?PaginationTransfer $paginationTransfer = null,
        array $sortTransfers = []
    ): AddressCollectionTransfer;
}
