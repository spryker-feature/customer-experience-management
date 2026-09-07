<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\Pagination;
use Generated\Shared\Transfer\PaginationTransfer;

interface PaginationResourceMapperInterface
{
    public function mapPaginationTransferToPagination(PaginationTransfer $paginationTransfer): Pagination;
}
