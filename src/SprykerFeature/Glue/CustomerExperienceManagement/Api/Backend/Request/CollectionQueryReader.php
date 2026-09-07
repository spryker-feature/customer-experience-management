<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request;

use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CollectionQueryExceptionFactory;
use Symfony\Component\HttpFoundation\Request;

class CollectionQueryReader implements CollectionQueryReaderInterface
{
    protected const string QUERY_PARAM_PAGE = 'page';

    protected const string PAGE_PARAM_LIMIT = 'limit';

    protected const string PAGE_PARAM_OFFSET = 'offset';

    protected const int DEFAULT_LIMIT = 10;

    protected const int DEFAULT_OFFSET = 0;

    protected const int FIRST_PAGE = 1;

    protected const string QUERY_PARAM_SORT = 'sort';

    protected const string SORT_DESCENDING_PREFIX = '-';

    /**
     * @var non-empty-string The native `string` type widens the literal, which `explode()` rejects.
     */
    protected const string SORT_FIELD_SEPARATOR = ',';

    public function __construct(protected CollectionQueryExceptionFactory $exceptionFactory)
    {
    }

    public function getPaginationTransfer(Request $request, ?int $defaultItemsPerPage = null): PaginationTransfer
    {
        $page = $request->query->all()[static::QUERY_PARAM_PAGE] ?? null;
        $limit = $this->resolveLimit($page, $defaultItemsPerPage);

        return (new PaginationTransfer())
            ->setPage($this->resolvePage($page, $limit))
            ->setMaxPerPage($limit);
    }

    protected function resolveLimit(mixed $page, ?int $defaultItemsPerPage): int
    {
        $limit = $defaultItemsPerPage ?? static::DEFAULT_LIMIT;

        if (is_array($page) && isset($page[static::PAGE_PARAM_LIMIT])) {
            $limit = (int)$page[static::PAGE_PARAM_LIMIT];
        }

        return $limit < 1 ? static::DEFAULT_LIMIT : (int)$limit;
    }

    protected function resolvePage(mixed $page, int $limit): int
    {
        if (!is_array($page)) {
            return max((int)$page, static::FIRST_PAGE);
        }

        $offset = (int)($page[static::PAGE_PARAM_OFFSET] ?? static::DEFAULT_OFFSET);

        return intdiv(max($offset, 0), $limit) + static::FIRST_PAGE;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<int, string> $sortableFields
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When a sort field is outside the allow list.
     *
     * @return array<int, \Generated\Shared\Transfer\SortTransfer>
     */
    public function getSortCollection(Request $request, array $sortableFields): array
    {
        $sort = $request->query->get(static::QUERY_PARAM_SORT);

        if (!is_string($sort) || $sort === '') {
            return [];
        }

        $sortTransfers = [];

        foreach (explode(static::SORT_FIELD_SEPARATOR, $sort) as $sortField) {
            $sortField = trim($sortField);

            if ($sortField === '') {
                continue;
            }

            $isAscending = !str_starts_with($sortField, static::SORT_DESCENDING_PREFIX);
            $field = ltrim($sortField, static::SORT_DESCENDING_PREFIX);

            if (!in_array($field, $sortableFields, true)) {
                throw $this->exceptionFactory->createInvalidSortFieldException($field, $sortableFields);
            }

            $sortTransfers[] = (new SortTransfer())->setField($field)->setIsAscending($isAscending);
        }

        return $sortTransfers;
    }
}
