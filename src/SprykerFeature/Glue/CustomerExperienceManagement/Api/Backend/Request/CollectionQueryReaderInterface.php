<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request;

use Symfony\Component\HttpFoundation\Request;

interface CollectionQueryReaderInterface
{
 /**
  * @param array<int, string> $sortableFields
  *
  * @throws \Spryker\ApiPlatform\Exception\GlueApiException When a sort field is outside the allow list.
  *
  * @return array<int, \Generated\Shared\Transfer\SortTransfer>
  */
    public function getSortCollection(Request $request, array $sortableFields): array;

    /**
     * Filters are addressed as `filter[<resourceName>.<property>]` by the JSON:API convention,
     * so the resource prefix is stripped and the property name is returned as the key.
     *
     * @return array<string, mixed>
     */
    public function getFilters(Request $request, string $filterKeyPrefix): array;
}
