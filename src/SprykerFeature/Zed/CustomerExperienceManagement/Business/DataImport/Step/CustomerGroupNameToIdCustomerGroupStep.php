<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;

class CustomerGroupNameToIdCustomerGroupStep extends AbstractCustomerGroupLookupStep
{
    protected const int CACHE_SIZE = 1000;

    protected const string ERROR_MESSAGE_CUSTOMER_GROUP_NOT_FOUND = 'Customer group "%s" was not found.';

    /**
     * @var array<string, int>
     */
    protected array $idCustomerGroupCache = [];

    public function execute(DataSetInterface $dataSet): void
    {
        $customerGroupName = (string)$dataSet[CustomerGroupMembershipDataSetInterface::COLUMN_CUSTOMER_GROUP_NAME];

        if ($customerGroupName === '') {
            throw new InvalidDataException(static::ERROR_MESSAGE_CUSTOMER_GROUP_NAME_EMPTY);
        }

        if (isset($this->idCustomerGroupCache[$customerGroupName])) {
            $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER_GROUP] = $this->idCustomerGroupCache[$customerGroupName];

            return;
        }

        $idCustomerGroup = $this->findIdCustomerGroupByName($customerGroupName);

        if ($idCustomerGroup === null) {
            throw new EntityNotFoundException(sprintf(static::ERROR_MESSAGE_CUSTOMER_GROUP_NOT_FOUND, $customerGroupName));
        }

        $this->clearCacheIfLimitReached();
        $this->idCustomerGroupCache[$customerGroupName] = $idCustomerGroup;
        $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER_GROUP] = $idCustomerGroup;
    }

    protected function findIdCustomerGroupByName(string $customerGroupName): ?int
    {
        $idCustomerGroup = $this->findIdCustomerGroupByExactName($customerGroupName);

        if ($idCustomerGroup !== null) {
            return $idCustomerGroup;
        }

        $customerGroupIds = $this->findCustomerGroupIdsByNameIgnoringCase($customerGroupName);
        $this->assertCustomerGroupNameIsUnambiguous($customerGroupName, count($customerGroupIds));

        return $customerGroupIds[0] ?? null;
    }

    protected function findIdCustomerGroupByExactName(string $customerGroupName): ?int
    {
        $customerGroupIds = $this->createCustomerGroupQueryByExactName($customerGroupName)
            ->select([SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP])
            ->limit(1)
            ->find()
            ->getData();

        return isset($customerGroupIds[0]) ? (int)$customerGroupIds[0] : null;
    }

    /**
     * @return list<int>
     */
    protected function findCustomerGroupIdsByNameIgnoringCase(string $customerGroupName): array
    {
        $customerGroupIds = $this->createCustomerGroupQueryByNameIgnoringCase($customerGroupName)
            ->select([SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP])
            ->find()
            ->getData();

        return array_values(array_map('intval', $customerGroupIds));
    }

    protected function clearCacheIfLimitReached(): void
    {
        if (count($this->idCustomerGroupCache) >= static::CACHE_SIZE) {
            $this->idCustomerGroupCache = [];
        }
    }
}
