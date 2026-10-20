<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomerGroupResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCustomerGroupTransferToResourceData(CustomerGroupTransfer $customerGroupTransfer): array;

    /**
     * @return array<string, mixed>
     */
    public function mapCustomerTransferToMemberResourceData(CustomerTransfer $customerTransfer): array;

    /**
     * @return array<string, mixed>
     */
    public function mapCustomerGroupCompanyTransferToResourceData(
        CustomerGroupCompanyTransfer $customerGroupCompanyTransfer,
        string $customerGroupUuid
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function mapCustomerGroupCompanyBusinessUnitTransferToResourceData(
        CustomerGroupCompanyBusinessUnitTransfer $customerGroupCompanyBusinessUnitTransfer,
        string $customerGroupUuid
    ): array;

    public function mapResourceToCustomerGroupTransfer(
        CustomerGroupsBackendResource $resource,
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupTransfer;
}
