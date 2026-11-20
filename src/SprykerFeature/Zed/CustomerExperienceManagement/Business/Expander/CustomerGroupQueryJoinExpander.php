<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Expander;

use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Generated\Shared\Transfer\QueryJoinTransfer;
use Generated\Shared\Transfer\QueryWhereConditionTransfer;
use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyTableMap;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

class CustomerGroupQueryJoinExpander implements CustomerGroupQueryJoinExpanderInterface
{
    public function expandWithCompanyConditions(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer {
        $customerGroupConditionsTransfer = $customerGroupCriteriaTransfer->getCustomerGroupConditions() ?? new CustomerGroupConditionsTransfer();

        return $this->expandWithMembershipConditions(
            $queryJoinCollectionTransfer,
            SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY,
            $customerGroupConditionsTransfer->getCompanyIds(),
            [SpyCompanyTableMap::COL_ID_COMPANY, SpyCompanyTableMap::COL_UUID],
            $customerGroupConditionsTransfer->getCompanyUuids(),
        );
    }

    public function expandWithCompanyBusinessUnitConditions(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer {
        $customerGroupConditionsTransfer = $customerGroupCriteriaTransfer->getCustomerGroupConditions() ?? new CustomerGroupConditionsTransfer();

        return $this->expandWithMembershipConditions(
            $queryJoinCollectionTransfer,
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
            $customerGroupConditionsTransfer->getCompanyBusinessUnitIds(),
            [SpyCompanyBusinessUnitTableMap::COL_ID_COMPANY_BUSINESS_UNIT, SpyCompanyBusinessUnitTableMap::COL_UUID],
            $customerGroupConditionsTransfer->getCompanyBusinessUnitUuids(),
        );
    }

    /**
     * @param array<int> $entityIds
     * @param list<string> $entityIdAndUuidColumns
     * @param array<string> $entityUuids
     */
    protected function expandWithMembershipConditions(
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer,
        string $customerGroupColumn,
        string $entityColumn,
        array $entityIds,
        array $entityIdAndUuidColumns,
        array $entityUuids
    ): QueryJoinCollectionTransfer {
        if ($entityIds === [] && $entityUuids === []) {
            return $queryJoinCollectionTransfer;
        }

        $membershipQueryJoinTransfer = (new QueryJoinTransfer())
            ->addLeft(SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP)
            ->addRight($customerGroupColumn)
            ->setJoinType(Criteria::INNER_JOIN);

        if ($entityIds !== []) {
            $membershipQueryJoinTransfer->addQueryWhereCondition($this->createInCondition($entityColumn, $entityIds));
        }

        $queryJoinCollectionTransfer->addQueryJoin($membershipQueryJoinTransfer);

        if ($entityUuids === []) {
            return $queryJoinCollectionTransfer;
        }

        [$entityIdColumn, $entityUuidColumn] = $entityIdAndUuidColumns;

        return $queryJoinCollectionTransfer->addQueryJoin(
            (new QueryJoinTransfer())
                ->addLeft($entityColumn)
                ->addRight($entityIdColumn)
                ->setJoinType(Criteria::INNER_JOIN)
                ->addQueryWhereCondition($this->createInCondition($entityUuidColumn, $entityUuids)),
        );
    }

    /**
     * @param array<int|string> $values
     */
    protected function createInCondition(string $column, array $values): QueryWhereConditionTransfer
    {
        return (new QueryWhereConditionTransfer())
            ->setColumn($column)
            ->setValues($values)
            ->setComparison(Criteria::IN);
    }
}
