<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

class CustomerGroupResourceMapper implements CustomerGroupResourceMapperInterface
{
    protected const string FIELD_UUID = 'uuid';

    protected const string FIELD_NAME = 'name';

    protected const string FIELD_DESCRIPTION = 'description';

    protected const string FIELD_CREATED_AT = 'createdAt';

    protected const string FIELD_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FIELD_EMAIL = 'email';

    protected const string FIELD_FIRST_NAME = 'firstName';

    protected const string FIELD_LAST_NAME = 'lastName';

    /**
     * @return array<string, mixed>
     */
    public function mapCustomerGroupTransferToResourceData(CustomerGroupTransfer $customerGroupTransfer): array
    {
        return [
            static::FIELD_UUID => $customerGroupTransfer->getUuid(),
            static::FIELD_NAME => $customerGroupTransfer->getName(),
            static::FIELD_DESCRIPTION => $customerGroupTransfer->getDescription(),
            static::FIELD_CREATED_AT => $customerGroupTransfer->getCreatedAt(),
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function mapCustomerTransferToMemberResourceData(CustomerTransfer $customerTransfer): array
    {
        return [
            static::FIELD_CUSTOMER_REFERENCE => $customerTransfer->getCustomerReference(),
            static::FIELD_EMAIL => $customerTransfer->getEmail(),
            static::FIELD_FIRST_NAME => $customerTransfer->getFirstName(),
            static::FIELD_LAST_NAME => $customerTransfer->getLastName(),
        ];
    }

    public function mapResourceToCustomerGroupTransfer(
        CustomerGroupsBackendResource $resource,
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupTransfer {
        if ($resource->name !== null) {
            $customerGroupTransfer->setName($resource->name);
        }

        if ($resource->description !== null) {
            $customerGroupTransfer->setDescription($resource->description);
        }

        return $customerGroupTransfer;
    }
}
