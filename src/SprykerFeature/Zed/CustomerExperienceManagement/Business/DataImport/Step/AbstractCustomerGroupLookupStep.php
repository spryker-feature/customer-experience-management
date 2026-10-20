<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;

abstract class AbstractCustomerGroupLookupStep implements DataImportStepInterface
{
    protected const string ERROR_MESSAGE_CUSTOMER_GROUP_NAME_EMPTY = 'The customer group name is empty.';

    protected const string ERROR_MESSAGE_CUSTOMER_GROUP_NAME_AMBIGUOUS = 'Customer group name "%s" matches more than one customer group.';

    protected const int AMBIGUOUS_MATCH_COUNT = 2;

    protected function createCustomerGroupQueryByExactName(string $customerGroupName): SpyCustomerGroupQuery
    {
        return SpyCustomerGroupQuery::create()->filterByName($customerGroupName);
    }

    protected function createCustomerGroupQueryByNameIgnoringCase(string $customerGroupName): SpyCustomerGroupQuery
    {
        return SpyCustomerGroupQuery::create()
            ->where(sprintf('LOWER(%s) = ?', SpyCustomerGroupTableMap::COL_NAME), mb_strtolower($customerGroupName))
            ->limit(static::AMBIGUOUS_MATCH_COUNT);
    }

    /**
     * @throws \Spryker\Zed\DataImport\Business\Exception\InvalidDataException
     */
    protected function assertCustomerGroupNameIsUnambiguous(string $customerGroupName, int $matchCount): void
    {
        if ($matchCount < static::AMBIGUOUS_MATCH_COUNT) {
            return;
        }

        throw new InvalidDataException(sprintf(static::ERROR_MESSAGE_CUSTOMER_GROUP_NAME_AMBIGUOUS, $customerGroupName));
    }
}
