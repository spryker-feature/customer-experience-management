<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Propel\Runtime\Collection\Collection;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupDataSetInterface;

class CustomerGroupWriterStep extends AbstractCustomerGroupLookupStep
{
    public function execute(DataSetInterface $dataSet): void
    {
        $customerGroupName = (string)$dataSet[CustomerGroupDataSetInterface::COLUMN_NAME];

        if ($customerGroupName === '') {
            throw new InvalidDataException(static::ERROR_MESSAGE_CUSTOMER_GROUP_NAME_EMPTY);
        }

        $customerGroupEntity = $this->findCustomerGroupEntityByName($customerGroupName)
            ?? (new SpyCustomerGroup())->setName($customerGroupName);

        $customerGroupEntity->setDescription($this->getDescription($dataSet));

        if ($customerGroupEntity->isNew() || $customerGroupEntity->isModified()) {
            $customerGroupEntity->save();
        }
    }

    protected function findCustomerGroupEntityByName(string $customerGroupName): ?SpyCustomerGroup
    {
        $customerGroupEntity = $this->findCustomerGroupEntityByExactName($customerGroupName);

        if ($customerGroupEntity !== null) {
            return $customerGroupEntity;
        }

        $customerGroupEntities = $this->findCustomerGroupEntitiesByNameIgnoringCase($customerGroupName);
        $this->assertCustomerGroupNameIsUnambiguous($customerGroupName, $customerGroupEntities->count());

        return $customerGroupEntities->getFirst();
    }

    protected function findCustomerGroupEntityByExactName(string $customerGroupName): ?SpyCustomerGroup
    {
        return $this->createCustomerGroupQueryByExactName($customerGroupName)->findOne();
    }

    /**
     * @return \Propel\Runtime\Collection\Collection<\Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup>
     */
    protected function findCustomerGroupEntitiesByNameIgnoringCase(string $customerGroupName): Collection
    {
        return $this->createCustomerGroupQueryByNameIgnoringCase($customerGroupName)->find();
    }

    protected function getDescription(DataSetInterface $dataSet): ?string
    {
        $description = (string)$dataSet[CustomerGroupDataSetInterface::COLUMN_DESCRIPTION];

        return $description !== '' ? $description : null;
    }
}
