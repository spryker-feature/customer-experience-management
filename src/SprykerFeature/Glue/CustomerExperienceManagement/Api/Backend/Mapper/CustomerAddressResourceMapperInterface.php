<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CustomersAddressesBackendResource;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomerAddressResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapAddressTransferToResourceData(
        AddressTransfer $addressTransfer,
        CustomerTransfer $customerTransfer
    ): array;

    public function mapResourceToAddressTransfer(
        CustomersAddressesBackendResource $resource,
        AddressTransfer $addressTransfer
    ): AddressTransfer;
}
