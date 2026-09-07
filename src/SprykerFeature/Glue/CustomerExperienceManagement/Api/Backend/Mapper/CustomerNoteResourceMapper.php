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

class CustomerNoteResourceMapper implements CustomerNoteResourceMapperInterface
{
    protected const string FIELD_UUID = 'uuid';

    protected const string FIELD_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FIELD_MESSAGE = 'message';

    protected const string FIELD_USERNAME = 'username';

    protected const string FIELD_CREATED_AT = 'createdAt';

    protected const string FIELD_UPDATED_AT = 'updatedAt';

    /**
     * @return array<string, mixed>
     */
    public function mapCustomerNoteEntityTransferToResourceData(
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer,
        CustomerTransfer $customerTransfer
    ): array {
        return [
            static::FIELD_UUID => $customerNoteEntityTransfer->getUuid(),
            static::FIELD_CUSTOMER_REFERENCE => $customerTransfer->getCustomerReference(),
            static::FIELD_MESSAGE => $customerNoteEntityTransfer->getMessage(),
            static::FIELD_USERNAME => $customerNoteEntityTransfer->getUsername(),
            static::FIELD_CREATED_AT => $customerNoteEntityTransfer->getCreatedAt(),
            static::FIELD_UPDATED_AT => $customerNoteEntityTransfer->getUpdatedAt(),
        ];
    }

    public function mapResourceToCustomerNoteEntityTransfer(
        CustomersNotesBackendResource $resource,
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer
    ): SpyCustomerNoteEntityTransfer {
        return $customerNoteEntityTransfer->setMessage($resource->message);
    }
}
