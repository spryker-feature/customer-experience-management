<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;

class CompanyBusinessUnitKeyToIdCompanyBusinessUnitStep implements DataImportStepInterface
{
    protected const int CACHE_SIZE = 1000;

    protected const int AMBIGUOUS_MATCH_COUNT = 2;

    protected const string ERROR_MESSAGE_BUSINESS_UNIT_KEY_EMPTY = 'The business unit key is empty.';

    protected const string ERROR_MESSAGE_BUSINESS_UNIT_NOT_FOUND = 'Business unit with key "%s" was not found.';

    protected const string ERROR_MESSAGE_BUSINESS_UNIT_KEY_AMBIGUOUS = 'Business unit key "%s" matches more than one business unit.';

    /**
     * @var array<string, int>
     */
    protected array $idCompanyBusinessUnitCache = [];

    public function execute(DataSetInterface $dataSet): void
    {
        $companyBusinessUnitKey = (string)$dataSet[CustomerGroupMembershipDataSetInterface::COLUMN_BUSINESS_UNIT_KEY];

        if ($companyBusinessUnitKey === '') {
            throw new InvalidDataException(static::ERROR_MESSAGE_BUSINESS_UNIT_KEY_EMPTY);
        }

        if (isset($this->idCompanyBusinessUnitCache[$companyBusinessUnitKey])) {
            $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT] = $this->idCompanyBusinessUnitCache[$companyBusinessUnitKey];

            return;
        }

        $companyBusinessUnitIds = $this->findCompanyBusinessUnitIdsByKey($companyBusinessUnitKey);

        if ($companyBusinessUnitIds === []) {
            throw new EntityNotFoundException(sprintf(static::ERROR_MESSAGE_BUSINESS_UNIT_NOT_FOUND, $companyBusinessUnitKey));
        }

        if (count($companyBusinessUnitIds) >= static::AMBIGUOUS_MATCH_COUNT) {
            throw new InvalidDataException(sprintf(static::ERROR_MESSAGE_BUSINESS_UNIT_KEY_AMBIGUOUS, $companyBusinessUnitKey));
        }

        $this->clearCacheIfLimitReached();
        $this->idCompanyBusinessUnitCache[$companyBusinessUnitKey] = $companyBusinessUnitIds[0];
        $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT] = $companyBusinessUnitIds[0];
    }

    /**
     * @return list<int>
     */
    protected function findCompanyBusinessUnitIdsByKey(string $companyBusinessUnitKey): array
    {
        $companyBusinessUnitIds = SpyCompanyBusinessUnitQuery::create()
            ->filterByKey($companyBusinessUnitKey)
            ->select([SpyCompanyBusinessUnitTableMap::COL_ID_COMPANY_BUSINESS_UNIT])
            ->limit(static::AMBIGUOUS_MATCH_COUNT)
            ->find()
            ->getData();

        return array_values(array_map('intval', $companyBusinessUnitIds));
    }

    protected function clearCacheIfLimitReached(): void
    {
        if (count($this->idCompanyBusinessUnitCache) >= static::CACHE_SIZE) {
            $this->idCompanyBusinessUnitCache = [];
        }
    }
}
