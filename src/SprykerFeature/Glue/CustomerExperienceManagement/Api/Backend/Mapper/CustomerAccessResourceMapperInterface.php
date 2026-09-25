<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Shared\Transfer\CustomerAccessTransfer;

interface CustomerAccessResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCustomerAccessTransferToResourceData(CustomerAccessTransfer $customerAccessTransfer): array;
}
