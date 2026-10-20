<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence;

use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompany;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyBusinessUnit;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupToCustomerTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementPersistenceFactory getFactory()
 */
class CustomerExperienceManagementEntityManager extends AbstractEntityManager implements CustomerExperienceManagementEntityManagerInterface
{
    use ActiveRecordBatchProcessorTrait;

    protected const string CONDITION_NAME_CUSTOMER_GROUP = 'customerGroup%d';

    protected const string CONDITION_NAME_MEMBERS = 'members%d';

    protected const string CONDITION_NAME_MEMBERSHIPS = 'memberships%d';

    /**
     * @param array<int, array<int>> $companyIdsByIdCustomerGroup
     */
    public function createCustomerGroupToCompanyRelations(array $companyIdsByIdCustomerGroup): void
    {
        $companyIdsToAssignByIdCustomerGroup = $this->getMemberIdsToAssignIndexedByIdCustomerGroup(
            $this->getFactory()->createCustomerGroupToCompanyQuery(),
            SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY,
            $companyIdsByIdCustomerGroup,
        );

        foreach ($companyIdsToAssignByIdCustomerGroup as $idCustomerGroup => $companyIds) {
            foreach ($companyIds as $idCompany) {
                $this->persist(
                    (new SpyCustomerGroupToCompany())
                        ->setFkCustomerGroup($idCustomerGroup)
                        ->setFkCompany($idCompany),
                );
            }
        }

        $this->commitPersistedEntities();
    }

    /**
     * @param array<int> $companyIds
     */
    public function deleteCustomerGroupToCompanyRelations(int $idCustomerGroup, array $companyIds): void
    {
        $this->getFactory()
            ->createCustomerGroupToCompanyQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCompany_In($companyIds)
            ->delete();
    }

    /**
     * @param array<int> $companyIdsToKeep
     */
    public function deleteCustomerGroupToCompanyRelationsExcept(int $idCustomerGroup, array $companyIdsToKeep): void
    {
        $this->getFactory()
            ->createCustomerGroupToCompanyQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCompany($companyIdsToKeep, Criteria::NOT_IN)
            ->delete();
    }

    /**
     * @param array<int, array<int>> $companyBusinessUnitIdsByIdCustomerGroup
     */
    public function createCustomerGroupToCompanyBusinessUnitRelations(array $companyBusinessUnitIdsByIdCustomerGroup): void
    {
        $companyBusinessUnitIdsToAssignByIdCustomerGroup = $this->getMemberIdsToAssignIndexedByIdCustomerGroup(
            $this->getFactory()->createCustomerGroupToCompanyBusinessUnitQuery(),
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
            $companyBusinessUnitIdsByIdCustomerGroup,
        );

        foreach ($companyBusinessUnitIdsToAssignByIdCustomerGroup as $idCustomerGroup => $companyBusinessUnitIds) {
            foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
                $this->persist(
                    (new SpyCustomerGroupToCompanyBusinessUnit())
                        ->setFkCustomerGroup($idCustomerGroup)
                        ->setFkCompanyBusinessUnit($idCompanyBusinessUnit),
                );
            }
        }

        $this->commitPersistedEntities();
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     */
    public function deleteCustomerGroupToCompanyBusinessUnitRelations(int $idCustomerGroup, array $companyBusinessUnitIds): void
    {
        $this->getFactory()
            ->createCustomerGroupToCompanyBusinessUnitQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->delete();
    }

    /**
     * @param array<int> $companyBusinessUnitIdsToKeep
     */
    public function deleteCustomerGroupToCompanyBusinessUnitRelationsExcept(int $idCustomerGroup, array $companyBusinessUnitIdsToKeep): void
    {
        $this->getFactory()
            ->createCustomerGroupToCompanyBusinessUnitQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCompanyBusinessUnit($companyBusinessUnitIdsToKeep, Criteria::NOT_IN)
            ->delete();
    }

    /**
     * @module CustomerGroup
     *
     * @param array<int, array<int>> $customerIdsByIdCustomerGroup
     */
    public function createCustomerGroupToCustomerRelations(array $customerIdsByIdCustomerGroup): void
    {
        $customerIdsToAssignByIdCustomerGroup = $this->getMemberIdsToAssignIndexedByIdCustomerGroup(
            $this->getFactory()->getCustomerGroupToCustomerPropelQuery(),
            SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER_GROUP,
            SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER,
            $customerIdsByIdCustomerGroup,
        );

        foreach ($customerIdsToAssignByIdCustomerGroup as $idCustomerGroup => $customerIds) {
            foreach ($customerIds as $idCustomer) {
                $this->persist(
                    (new SpyCustomerGroupToCustomer())
                        ->setFkCustomerGroup($idCustomerGroup)
                        ->setFkCustomer($idCustomer),
                );
            }
        }

        $this->commitPersistedEntities();
    }

    protected function commitPersistedEntities(): void
    {
        try {
            $this->commitIdentical();
        } finally {
            $this->resetEntitiesForCommit();
        }
    }

    /**
     * @param array<int, array<int>> $memberIdsByIdCustomerGroup
     *
     * @return array<int, array<int>>
     */
    protected function getMemberIdsToAssignIndexedByIdCustomerGroup(
        ModelCriteria $membershipQuery,
        string $customerGroupIdColumn,
        string $memberIdColumn,
        array $memberIdsByIdCustomerGroup
    ): array {
        $assignedMemberIdsByIdCustomerGroup = $this->getAssignedMemberIdsIndexedByIdCustomerGroup(
            $membershipQuery,
            $customerGroupIdColumn,
            $memberIdColumn,
            $memberIdsByIdCustomerGroup,
        );
        $memberIdsToAssignByIdCustomerGroup = [];

        foreach ($memberIdsByIdCustomerGroup as $idCustomerGroup => $memberIds) {
            $memberIdsToAssignByIdCustomerGroup[$idCustomerGroup] = array_diff(
                $memberIds,
                $assignedMemberIdsByIdCustomerGroup[$idCustomerGroup] ?? [],
            );
        }

        return $memberIdsToAssignByIdCustomerGroup;
    }

    /**
     * @param array<int, array<int>> $memberIdsByIdCustomerGroup
     *
     * @return array<int, array<int>>
     */
    protected function getAssignedMemberIdsIndexedByIdCustomerGroup(
        ModelCriteria $membershipQuery,
        string $customerGroupIdColumn,
        string $memberIdColumn,
        array $memberIdsByIdCustomerGroup
    ): array {
        if ($memberIdsByIdCustomerGroup === []) {
            return [];
        }

        $membershipConditionNames = [];

        foreach ($memberIdsByIdCustomerGroup as $idCustomerGroup => $memberIds) {
            $membershipConditionName = sprintf(static::CONDITION_NAME_MEMBERSHIPS, $idCustomerGroup);
            $customerGroupConditionName = sprintf(static::CONDITION_NAME_CUSTOMER_GROUP, $idCustomerGroup);
            $membersConditionName = sprintf(static::CONDITION_NAME_MEMBERS, $idCustomerGroup);

            $membershipQuery
                ->condition($customerGroupConditionName, sprintf('%s = ?', $customerGroupIdColumn), $idCustomerGroup)
                ->condition($membersConditionName, sprintf('%s IN ?', $memberIdColumn), $memberIds)
                ->combine([$customerGroupConditionName, $membersConditionName], Criteria::LOGICAL_AND, $membershipConditionName);

            $membershipConditionNames[] = $membershipConditionName;
        }

        /** @var array<array<string, int|string>> $memberships */
        $memberships = $membershipQuery
            ->where($membershipConditionNames, Criteria::LOGICAL_OR)
            ->select([$customerGroupIdColumn, $memberIdColumn])
            ->find()
            ->getData();

        $assignedMemberIdsByIdCustomerGroup = [];

        foreach ($memberships as $membership) {
            $assignedMemberIdsByIdCustomerGroup[(int)$membership[$customerGroupIdColumn]][] = (int)$membership[$memberIdColumn];
        }

        return $assignedMemberIdsByIdCustomerGroup;
    }
}
