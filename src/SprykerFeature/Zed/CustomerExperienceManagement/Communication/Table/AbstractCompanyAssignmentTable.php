<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyUser\Persistence\Map\SpyCompanyUserTableMap;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

abstract class AbstractCompanyAssignmentTable extends AbstractCustomerGroupAssignmentTable
{
    /**
     * @var list<string>
     */
    public const array DESTINATION_TABLE_COLUMN_TITLES = [
        self::TITLE_ID,
        self::TITLE_COMPANY,
        self::TITLE_CUSTOMER_COUNT,
    ];

    protected const string COL_ID_COMPANY = 'id_company';

    /**
     * @param list<\SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\CustomerGroupAssignmentTableExpanderInterface> $customerGroupAssignmentTableExpanders
     */
    public function __construct(
        protected SpyCompanyQuery $companyQuery,
        ?int $idCustomerGroup,
        array $customerGroupAssignmentTableExpanders = []
    ) {
        parent::__construct($idCustomerGroup, $customerGroupAssignmentTableExpanders);
    }

    abstract protected function filterCompanyQuery(SpyCompanyQuery $companyQuery): SpyCompanyQuery;

    protected function configureColumns(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader([
            static::COL_ID_COMPANY => static::TITLE_ID,
            static::COL_NAME => static::TITLE_COMPANY,
            static::COL_CUSTOMER_COUNT => static::TITLE_CUSTOMER_COUNT,
        ]);

        $config->setSortable([static::COL_ID_COMPANY, static::COL_NAME]);
        $config->setSearchable([SpyCompanyTableMap::COL_NAME]);
        $config->setDefaultSortField(static::COL_NAME, TableConfiguration::SORT_ASC);

        return $config;
    }

    protected function prepareQuery(): ModelCriteria
    {
        return $this->filterCompanyQuery($this->companyQuery)
            ->withColumn(SpyCompanyTableMap::COL_ID_COMPANY, static::COL_ID_COMPANY)
            ->withColumn(SpyCompanyTableMap::COL_NAME, static::COL_NAME)
            ->withColumn($this->getCustomerCountExpression(), static::COL_CUSTOMER_COUNT);
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    protected function formatRow(array $item): array
    {
        return [
            static::COL_ID_COMPANY => $item[static::COL_ID_COMPANY],
            static::COL_NAME => $item[static::COL_NAME],
            static::COL_CUSTOMER_COUNT => $item[static::COL_CUSTOMER_COUNT],
        ];
    }

    protected function getIdColumn(): string
    {
        return static::COL_ID_COMPANY;
    }

    protected function getCustomerCountExpression(): string
    {
        return sprintf(
            '(SELECT COUNT(*) FROM %s WHERE %s = %s)',
            SpyCompanyUserTableMap::TABLE_NAME,
            SpyCompanyUserTableMap::COL_FK_COMPANY,
            SpyCompanyTableMap::COL_ID_COMPANY,
        );
    }
}
