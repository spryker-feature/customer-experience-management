<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence;

use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\Propel\Mapper\CustomerGroupMembershipMapper;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementPersistenceFactory getFactory()
 */
class CustomerExperienceManagementRepository extends AbstractRepository implements CustomerExperienceManagementRepositoryInterface
{
    protected const string LIKE_PATTERN = '%%%s%%';

    /**
     * @module Company
     */
    public function getCustomerGroupCompanyCollection(
        CustomerGroupCompanyCriteriaTransfer $customerGroupCompanyCriteriaTransfer
    ): CustomerGroupCompanyCollectionTransfer {
        $customerGroupToCompanyQuery = $this->getFactory()->createCustomerGroupToCompanyQuery()->joinCompany();
        $customerGroupToCompanyQuery = $this->applyCustomerGroupCompanyConditions(
            $customerGroupToCompanyQuery,
            $customerGroupCompanyCriteriaTransfer->getCustomerGroupCompanyConditions(),
        );
        $paginationTransfer = $this->resolvePagination($customerGroupCompanyCriteriaTransfer->getPagination());
        $customerGroupToCompanyQuery = $this->applyPagination($customerGroupToCompanyQuery, $paginationTransfer);
        $customerGroupToCompanyQuery = $this->applySort(
            $customerGroupToCompanyQuery,
            $customerGroupCompanyCriteriaTransfer->getSortCollection(),
            $this->getFactory()->getConfig()->getCustomerGroupCompanyCollectionSortableFieldMap(),
            SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY,
        );

        /** @var array<array<string, mixed>> $customerGroupCompanyRows */
        $customerGroupCompanyRows = $customerGroupToCompanyQuery
            ->withColumn(SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP, CustomerGroupMembershipMapper::COLUMN_ID_CUSTOMER_GROUP)
            ->withColumn(SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY, CustomerGroupMembershipMapper::COLUMN_ID_COMPANY)
            ->withColumn(SpyCompanyTableMap::COL_UUID, CustomerGroupMembershipMapper::COLUMN_COMPANY_UUID)
            ->withColumn(SpyCompanyTableMap::COL_NAME, CustomerGroupMembershipMapper::COLUMN_COMPANY_NAME)
            ->select([
                CustomerGroupMembershipMapper::COLUMN_ID_CUSTOMER_GROUP,
                CustomerGroupMembershipMapper::COLUMN_ID_COMPANY,
                CustomerGroupMembershipMapper::COLUMN_COMPANY_UUID,
                CustomerGroupMembershipMapper::COLUMN_COMPANY_NAME,
            ])
            ->find()
            ->getData();

        return $this->getFactory()
            ->createCustomerGroupMembershipMapper()
            ->mapCustomerGroupCompanyRowsToCustomerGroupCompanyCollectionTransfer(
                $customerGroupCompanyRows,
                (new CustomerGroupCompanyCollectionTransfer())->setPagination($paginationTransfer),
            );
    }

    /**
     * @module Company
     * @module CompanyBusinessUnit
     */
    public function getCustomerGroupCompanyBusinessUnitCollection(
        CustomerGroupCompanyBusinessUnitCriteriaTransfer $customerGroupCompanyBusinessUnitCriteriaTransfer
    ): CustomerGroupCompanyBusinessUnitCollectionTransfer {
        $customerGroupToCompanyBusinessUnitQuery = $this->getFactory()
            ->createCustomerGroupToCompanyBusinessUnitQuery()
            ->useCompanyBusinessUnitQuery()
                ->joinCompany()
            ->endUse();
        $customerGroupToCompanyBusinessUnitQuery = $this->applyCustomerGroupCompanyBusinessUnitConditions(
            $customerGroupToCompanyBusinessUnitQuery,
            $customerGroupCompanyBusinessUnitCriteriaTransfer->getCustomerGroupCompanyBusinessUnitConditions(),
        );
        $paginationTransfer = $this->resolvePagination($customerGroupCompanyBusinessUnitCriteriaTransfer->getPagination());
        $customerGroupToCompanyBusinessUnitQuery = $this->applyPagination($customerGroupToCompanyBusinessUnitQuery, $paginationTransfer);
        $customerGroupToCompanyBusinessUnitQuery = $this->applySort(
            $customerGroupToCompanyBusinessUnitQuery,
            $customerGroupCompanyBusinessUnitCriteriaTransfer->getSortCollection(),
            $this->getFactory()->getConfig()->getCustomerGroupCompanyBusinessUnitCollectionSortableFieldMap(),
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
        );

        /** @var array<array<string, mixed>> $customerGroupCompanyBusinessUnitRows */
        $customerGroupCompanyBusinessUnitRows = $customerGroupToCompanyBusinessUnitQuery
            ->withColumn(SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP, CustomerGroupMembershipMapper::COLUMN_ID_CUSTOMER_GROUP)
            ->withColumn(
                SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
                CustomerGroupMembershipMapper::COLUMN_ID_COMPANY_BUSINESS_UNIT,
            )
            ->withColumn(SpyCompanyBusinessUnitTableMap::COL_UUID, CustomerGroupMembershipMapper::COLUMN_COMPANY_BUSINESS_UNIT_UUID)
            ->withColumn(SpyCompanyTableMap::COL_UUID, CustomerGroupMembershipMapper::COLUMN_COMPANY_UUID)
            ->withColumn(SpyCompanyBusinessUnitTableMap::COL_NAME, CustomerGroupMembershipMapper::COLUMN_COMPANY_BUSINESS_UNIT_NAME)
            ->withColumn(SpyCompanyTableMap::COL_NAME, CustomerGroupMembershipMapper::COLUMN_COMPANY_NAME)
            ->select([
                CustomerGroupMembershipMapper::COLUMN_ID_CUSTOMER_GROUP,
                CustomerGroupMembershipMapper::COLUMN_ID_COMPANY_BUSINESS_UNIT,
                CustomerGroupMembershipMapper::COLUMN_COMPANY_BUSINESS_UNIT_UUID,
                CustomerGroupMembershipMapper::COLUMN_COMPANY_UUID,
                CustomerGroupMembershipMapper::COLUMN_COMPANY_BUSINESS_UNIT_NAME,
                CustomerGroupMembershipMapper::COLUMN_COMPANY_NAME,
            ])
            ->find()
            ->getData();

        return $this->getFactory()
            ->createCustomerGroupMembershipMapper()
            ->mapCustomerGroupCompanyBusinessUnitRowsToCustomerGroupCompanyBusinessUnitCollectionTransfer(
                $customerGroupCompanyBusinessUnitRows,
                (new CustomerGroupCompanyBusinessUnitCollectionTransfer())->setPagination($paginationTransfer),
            );
    }

    /**
     * @module Company
     *
     * @param list<int> $companyIds
     *
     * @return list<int>
     */
    public function getExistingCompanyIds(array $companyIds): array
    {
        if ($companyIds === []) {
            return [];
        }

        $existingCompanyIds = $this->getFactory()
            ->getCompanyPropelQuery()
            ->filterByIdCompany_In($companyIds)
            ->select([SpyCompanyTableMap::COL_ID_COMPANY])
            ->find()
            ->getData();

        return array_values(array_map('intval', $existingCompanyIds));
    }

    /**
     * @module CompanyBusinessUnit
     *
     * @param list<int> $companyBusinessUnitIds
     *
     * @return list<int>
     */
    public function getExistingCompanyBusinessUnitIds(array $companyBusinessUnitIds): array
    {
        if ($companyBusinessUnitIds === []) {
            return [];
        }

        $existingCompanyBusinessUnitIds = $this->getFactory()
            ->getCompanyBusinessUnitPropelQuery()
            ->filterByIdCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->select([SpyCompanyBusinessUnitTableMap::COL_ID_COMPANY_BUSINESS_UNIT])
            ->find()
            ->getData();

        return array_values(array_map('intval', $existingCompanyBusinessUnitIds));
    }

    /**
     * @param list<int> $companyIds
     *
     * @return list<int>
     */
    public function getAssignedCompanyIds(int $idCustomerGroup, array $companyIds): array
    {
        if ($companyIds === []) {
            return [];
        }

        $assignedCompanyIds = $this->getFactory()
            ->createCustomerGroupToCompanyQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCompany_In($companyIds)
            ->select([SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY])
            ->find()
            ->getData();

        return array_values(array_map('intval', $assignedCompanyIds));
    }

    /**
     * @param list<int> $companyBusinessUnitIds
     *
     * @return list<int>
     */
    public function getAssignedCompanyBusinessUnitIds(int $idCustomerGroup, array $companyBusinessUnitIds): array
    {
        if ($companyBusinessUnitIds === []) {
            return [];
        }

        $assignedCompanyBusinessUnitIds = $this->getFactory()
            ->createCustomerGroupToCompanyBusinessUnitQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->select([SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT])
            ->find()
            ->getData();

        return array_values(array_map('intval', $assignedCompanyBusinessUnitIds));
    }

    protected function applyCustomerGroupCompanyConditions(
        ModelCriteria $query,
        ?CustomerGroupCompanyConditionsTransfer $customerGroupCompanyConditionsTransfer
    ): ModelCriteria {
        if ($customerGroupCompanyConditionsTransfer === null) {
            return $query;
        }

        $this->applyCustomerGroupIds(
            $query,
            SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP,
            $customerGroupCompanyConditionsTransfer->getCustomerGroupIds(),
        );
        $this->applyUuids($query, SpyCompanyTableMap::COL_UUID, $customerGroupCompanyConditionsTransfer->getCompanyUuids());

        $searchTerm = $customerGroupCompanyConditionsTransfer->getSearchTerm();

        if ($searchTerm !== null && $searchTerm !== '') {
            $query->where(sprintf('LOWER(%s) LIKE ?', SpyCompanyTableMap::COL_NAME), $this->buildLikePattern($searchTerm));
        }

        return $query;
    }

    protected function applyCustomerGroupCompanyBusinessUnitConditions(
        ModelCriteria $query,
        ?CustomerGroupCompanyBusinessUnitConditionsTransfer $customerGroupCompanyBusinessUnitConditionsTransfer
    ): ModelCriteria {
        if ($customerGroupCompanyBusinessUnitConditionsTransfer === null) {
            return $query;
        }

        $this->applyCustomerGroupIds(
            $query,
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP,
            $customerGroupCompanyBusinessUnitConditionsTransfer->getCustomerGroupIds(),
        );
        $this->applyUuids(
            $query,
            SpyCompanyBusinessUnitTableMap::COL_UUID,
            $customerGroupCompanyBusinessUnitConditionsTransfer->getCompanyBusinessUnitUuids(),
        );
        $this->applyUuids($query, SpyCompanyTableMap::COL_UUID, $customerGroupCompanyBusinessUnitConditionsTransfer->getCompanyUuids());

        $searchTerm = $customerGroupCompanyBusinessUnitConditionsTransfer->getSearchTerm();

        if ($searchTerm !== null && $searchTerm !== '') {
            $query->where(sprintf('LOWER(%s) LIKE ?', SpyCompanyBusinessUnitTableMap::COL_NAME), $this->buildLikePattern($searchTerm));
        }

        return $query;
    }

    /**
     * @param array<int> $customerGroupIds
     */
    protected function applyCustomerGroupIds(ModelCriteria $query, string $column, array $customerGroupIds): void
    {
        if ($customerGroupIds === []) {
            return;
        }

        $query->addAnd($column, $customerGroupIds, Criteria::IN);
    }

    /**
     * @param array<string> $uuids
     */
    protected function applyUuids(ModelCriteria $query, string $column, array $uuids): void
    {
        if ($uuids === []) {
            return;
        }

        $query->addAnd($column, $uuids, Criteria::IN);
    }

    protected function buildLikePattern(string $searchTerm): string
    {
        return sprintf(static::LIKE_PATTERN, mb_strtolower($searchTerm));
    }

    protected function resolvePagination(?PaginationTransfer $paginationTransfer): PaginationTransfer
    {
        $paginationTransfer ??= new PaginationTransfer();
        $maxLimit = $this->getFactory()->getConfig()->getCustomerGroupMembershipCollectionMaxLimit();

        return $paginationTransfer->setLimit(min($paginationTransfer->getLimit() ?? $maxLimit, $maxLimit));
    }

    protected function applyPagination(ModelCriteria $query, PaginationTransfer $paginationTransfer): ModelCriteria
    {
        $paginationTransfer->setNbResults($query->count());

        if ($paginationTransfer->getOffset() !== null) {
            $query->offset($paginationTransfer->getOffset());
        }

        return $query->limit($paginationTransfer->getLimitOrFail());
    }

    /**
     * @param iterable<\Generated\Shared\Transfer\SortTransfer> $sortTransfers
     * @param array<string, string> $columnBySortField
     */
    protected function applySort(
        ModelCriteria $query,
        iterable $sortTransfers,
        array $columnBySortField,
        string $tiebreakerColumn
    ): ModelCriteria {
        $tiebreakerDirection = Criteria::ASC;

        foreach ($sortTransfers as $sortTransfer) {
            $column = $columnBySortField[$sortTransfer->getField()] ?? null;

            if ($column === null) {
                continue;
            }

            $tiebreakerDirection = $sortTransfer->getIsAscending() === false ? Criteria::DESC : Criteria::ASC;
            $query->orderBy($column, $tiebreakerDirection);
        }

        return $query->orderBy($tiebreakerColumn, $tiebreakerDirection);
    }
}
