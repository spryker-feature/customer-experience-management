<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Orm\Zed\Customer\Persistence\Map\SpyCustomerTableMap;
use Orm\Zed\Customer\Persistence\SpyCustomerQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;

class CustomerReferenceToIdCustomerStep implements DataImportStepInterface
{
    protected const int CACHE_SIZE = 1000;

    protected const string ERROR_MESSAGE_CUSTOMER_REFERENCE_EMPTY = 'The customer reference is empty.';

    protected const string ERROR_MESSAGE_CUSTOMER_NOT_FOUND = 'Customer with reference "%s" was not found.';

    /**
     * @var array<string, int>
     */
    protected array $idCustomerCache = [];

    public function execute(DataSetInterface $dataSet): void
    {
        $customerReference = (string)$dataSet[CustomerGroupMembershipDataSetInterface::COLUMN_CUSTOMER_REFERENCE];

        if ($customerReference === '') {
            throw new InvalidDataException(static::ERROR_MESSAGE_CUSTOMER_REFERENCE_EMPTY);
        }

        if (isset($this->idCustomerCache[$customerReference])) {
            $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER] = $this->idCustomerCache[$customerReference];

            return;
        }

        $idCustomer = $this->findIdCustomerByReference($customerReference);

        if ($idCustomer === null) {
            throw new EntityNotFoundException(sprintf(static::ERROR_MESSAGE_CUSTOMER_NOT_FOUND, $customerReference));
        }

        $this->clearCacheIfLimitReached();
        $this->idCustomerCache[$customerReference] = $idCustomer;
        $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER] = $idCustomer;
    }

    protected function findIdCustomerByReference(string $customerReference): ?int
    {
        $customerIds = SpyCustomerQuery::create()
            ->filterByCustomerReference($customerReference)
            ->select([SpyCustomerTableMap::COL_ID_CUSTOMER])
            ->limit(1)
            ->find()
            ->getData();

        return isset($customerIds[0]) ? (int)$customerIds[0] : null;
    }

    protected function clearCacheIfLimitReached(): void
    {
        if (count($this->idCustomerCache) >= static::CACHE_SIZE) {
            $this->idCustomerCache = [];
        }
    }
}
