<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Expander;

use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\QueryJoinCollectionTransfer;

interface CustomerGroupQueryJoinExpanderInterface
{
    public function expandWithCompanyConditions(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer;

    public function expandWithCompanyBusinessUnitConditions(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer;
}
