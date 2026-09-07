<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CustomersNotesBackendResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;

interface CustomerNoteResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCustomerNoteEntityTransferToResourceData(
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer,
        CustomerTransfer $customerTransfer
    ): array;

    public function mapResourceToCustomerNoteEntityTransfer(
        CustomersNotesBackendResource $resource,
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer
    ): SpyCustomerNoteEntityTransfer;
}
