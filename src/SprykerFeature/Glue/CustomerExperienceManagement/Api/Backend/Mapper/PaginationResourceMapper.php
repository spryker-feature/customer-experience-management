<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\Pagination;
use Generated\Shared\Transfer\PaginationTransfer;

class PaginationResourceMapper implements PaginationResourceMapperInterface
{
    protected const string FIELD_NUM_FOUND = 'numFound';

    protected const string FIELD_CURRENT_PAGE = 'currentPage';

    protected const string FIELD_MAX_PAGE = 'maxPage';

    protected const string FIELD_CURRENT_ITEMS_PER_PAGE = 'currentItemsPerPage';

    protected const int DEFAULT_PAGE = 1;

    protected const int DEFAULT_COUNT = 0;

    public function mapPaginationTransferToPagination(PaginationTransfer $paginationTransfer): Pagination
    {
        return Pagination::fromArray([
            static::FIELD_NUM_FOUND => $paginationTransfer->getNbResults() ?? static::DEFAULT_COUNT,
            static::FIELD_CURRENT_PAGE => $paginationTransfer->getPage() ?? static::DEFAULT_PAGE,
            static::FIELD_MAX_PAGE => $paginationTransfer->getLastPage() ?? static::DEFAULT_PAGE,
            static::FIELD_CURRENT_ITEMS_PER_PAGE => $paginationTransfer->getMaxPerPage() ?? static::DEFAULT_COUNT,
        ]);
    }
}
