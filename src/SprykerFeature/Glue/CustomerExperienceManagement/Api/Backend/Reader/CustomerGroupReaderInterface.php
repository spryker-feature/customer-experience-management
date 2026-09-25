<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomerGroupReaderInterface
{
    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function getCustomerGroupByUuid(string $uuid): CustomerGroupTransfer;

    public function getCustomerGroupCollection(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): CustomerGroupCollectionTransfer;

    public function getCustomerCollectionByCustomerGroupCriteria(
        CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
    ): CustomerCollectionTransfer;

    public function findAssignedCustomer(int $idCustomerGroup, string $customerReference): ?CustomerTransfer;
}
