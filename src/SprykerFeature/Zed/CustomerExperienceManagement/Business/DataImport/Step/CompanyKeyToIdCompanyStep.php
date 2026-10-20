<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;

class CompanyKeyToIdCompanyStep implements DataImportStepInterface
{
    protected const int CACHE_SIZE = 1000;

    protected const int AMBIGUOUS_MATCH_COUNT = 2;

    protected const string ERROR_MESSAGE_COMPANY_KEY_EMPTY = 'The company key is empty.';

    protected const string ERROR_MESSAGE_COMPANY_NOT_FOUND = 'Company with key "%s" was not found.';

    protected const string ERROR_MESSAGE_COMPANY_KEY_AMBIGUOUS = 'Company key "%s" matches more than one company.';

    /**
     * @var array<string, int>
     */
    protected array $idCompanyCache = [];

    public function execute(DataSetInterface $dataSet): void
    {
        $companyKey = (string)$dataSet[CustomerGroupMembershipDataSetInterface::COLUMN_COMPANY_KEY];

        if ($companyKey === '') {
            throw new InvalidDataException(static::ERROR_MESSAGE_COMPANY_KEY_EMPTY);
        }

        if (isset($this->idCompanyCache[$companyKey])) {
            $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_COMPANY] = $this->idCompanyCache[$companyKey];

            return;
        }

        $companyIds = $this->findCompanyIdsByKey($companyKey);

        if ($companyIds === []) {
            throw new EntityNotFoundException(sprintf(static::ERROR_MESSAGE_COMPANY_NOT_FOUND, $companyKey));
        }

        if (count($companyIds) >= static::AMBIGUOUS_MATCH_COUNT) {
            throw new InvalidDataException(sprintf(static::ERROR_MESSAGE_COMPANY_KEY_AMBIGUOUS, $companyKey));
        }

        $this->clearCacheIfLimitReached();
        $this->idCompanyCache[$companyKey] = $companyIds[0];
        $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_COMPANY] = $companyIds[0];
    }

    /**
     * @return list<int>
     */
    protected function findCompanyIdsByKey(string $companyKey): array
    {
        $companyIds = SpyCompanyQuery::create()
            ->filterByKey($companyKey)
            ->select([SpyCompanyTableMap::COL_ID_COMPANY])
            ->limit(static::AMBIGUOUS_MATCH_COUNT)
            ->find()
            ->getData();

        return array_values(array_map('intval', $companyIds));
    }

    protected function clearCacheIfLimitReached(): void
    {
        if (count($this->idCompanyCache) >= static::CACHE_SIZE) {
            $this->idCompanyCache = [];
        }
    }
}
