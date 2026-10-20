<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication;

use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Spryker\Zed\Gui\Communication\Tabs\TabsInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\CustomerGroupAssignmentTableExpanderInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\CustomerGroupFormExpander;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\CustomerGroupFormExpanderInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\SelectableCustomerGroupAssignmentTableExpander;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\Constraint\CompanyAssignmentConstraint;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\Constraint\CompanyBusinessUnitAssignmentConstraint;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\DataTransformer\IdsCsvDataTransformer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCompanyAssignmentTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCompanyBusinessUnitAssignmentTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AssignedCompanyBusinessUnitTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AssignedCompanyTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AvailableCompanyBusinessUnitTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AvailableCompanyTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\ReadOnlyAssignedCompanyBusinessUnitTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\ReadOnlyAssignedCompanyTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Tabs\AssignmentTableTabs;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementDependencyProvider;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface getEntityManager()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface getRepository()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig getConfig()
 */
class CustomerExperienceManagementCommunicationFactory extends AbstractCommunicationFactory
{
    public function createAssignedCompanyTable(?int $idCustomerGroup): AbstractCompanyAssignmentTable
    {
        return new ReadOnlyAssignedCompanyTable($this->getCompanyPropelQuery(), $idCustomerGroup);
    }

    public function createSelectableAvailableCompanyTable(?int $idCustomerGroup, string $inputSelector = ''): AbstractCompanyAssignmentTable
    {
        return new AvailableCompanyTable($this->getCompanyPropelQuery(), $idCustomerGroup, [
            $this->createSelectableCustomerGroupAssignmentTableExpander(
                AvailableCompanyTable::DESTINATION_TABLE_ID,
                AvailableCompanyTable::DESTINATION_TAB_NAME,
                $inputSelector,
            ),
        ]);
    }

    public function createSelectableAssignedCompanyTable(?int $idCustomerGroup, string $inputSelector = ''): AbstractCompanyAssignmentTable
    {
        return new AssignedCompanyTable($this->getCompanyPropelQuery(), $idCustomerGroup, [
            $this->createSelectableCustomerGroupAssignmentTableExpander(
                AssignedCompanyTable::DESTINATION_TABLE_ID,
                AssignedCompanyTable::DESTINATION_TAB_NAME,
                $inputSelector,
            ),
        ]);
    }

    public function createAssignedCompanyBusinessUnitTable(?int $idCustomerGroup): AbstractCompanyBusinessUnitAssignmentTable
    {
        return new ReadOnlyAssignedCompanyBusinessUnitTable($this->getCompanyBusinessUnitPropelQuery(), $idCustomerGroup);
    }

    public function createSelectableAvailableCompanyBusinessUnitTable(
        ?int $idCustomerGroup,
        string $inputSelector = ''
    ): AbstractCompanyBusinessUnitAssignmentTable {
        return new AvailableCompanyBusinessUnitTable($this->getCompanyBusinessUnitPropelQuery(), $idCustomerGroup, [
            $this->createSelectableCustomerGroupAssignmentTableExpander(
                AvailableCompanyBusinessUnitTable::DESTINATION_TABLE_ID,
                AvailableCompanyBusinessUnitTable::DESTINATION_TAB_NAME,
                $inputSelector,
            ),
        ]);
    }

    public function createSelectableAssignedCompanyBusinessUnitTable(
        ?int $idCustomerGroup,
        string $inputSelector = ''
    ): AbstractCompanyBusinessUnitAssignmentTable {
        return new AssignedCompanyBusinessUnitTable($this->getCompanyBusinessUnitPropelQuery(), $idCustomerGroup, [
            $this->createSelectableCustomerGroupAssignmentTableExpander(
                AssignedCompanyBusinessUnitTable::DESTINATION_TABLE_ID,
                AssignedCompanyBusinessUnitTable::DESTINATION_TAB_NAME,
                $inputSelector,
            ),
        ]);
    }

    public function createSelectableCustomerGroupAssignmentTableExpander(
        string $destinationTableId,
        string $destinationTabName,
        string $inputSelector
    ): CustomerGroupAssignmentTableExpanderInterface {
        return new SelectableCustomerGroupAssignmentTableExpander($destinationTableId, $destinationTabName, $inputSelector);
    }

    public function createAvailableCompanyTableTabs(): TabsInterface
    {
        return new AssignmentTableTabs(
            AvailableCompanyTable::SOURCE_TAB_NAME,
            AvailableCompanyTable::SOURCE_TAB_TITLE,
            AvailableCompanyTable::DESTINATION_TAB_NAME,
            AvailableCompanyTable::DESTINATION_TAB_TITLE,
        );
    }

    public function createAssignedCompanyTableTabs(): TabsInterface
    {
        return new AssignmentTableTabs(
            AssignedCompanyTable::SOURCE_TAB_NAME,
            AssignedCompanyTable::SOURCE_TAB_TITLE,
            AssignedCompanyTable::DESTINATION_TAB_NAME,
            AssignedCompanyTable::DESTINATION_TAB_TITLE,
        );
    }

    public function createAvailableCompanyBusinessUnitTableTabs(): TabsInterface
    {
        return new AssignmentTableTabs(
            AvailableCompanyBusinessUnitTable::SOURCE_TAB_NAME,
            AvailableCompanyBusinessUnitTable::SOURCE_TAB_TITLE,
            AvailableCompanyBusinessUnitTable::DESTINATION_TAB_NAME,
            AvailableCompanyBusinessUnitTable::DESTINATION_TAB_TITLE,
        );
    }

    public function createAssignedCompanyBusinessUnitTableTabs(): TabsInterface
    {
        return new AssignmentTableTabs(
            AssignedCompanyBusinessUnitTable::SOURCE_TAB_NAME,
            AssignedCompanyBusinessUnitTable::SOURCE_TAB_TITLE,
            AssignedCompanyBusinessUnitTable::DESTINATION_TAB_NAME,
            AssignedCompanyBusinessUnitTable::DESTINATION_TAB_TITLE,
        );
    }

    /**
     * @return \Symfony\Component\Form\DataTransformerInterface<list<int>|null, string>
     */
    public function createIdsCsvDataTransformer(): DataTransformerInterface
    {
        return new IdsCsvDataTransformer($this->getConfig()->getCustomerGroupAssignmentIdsLimit());
    }

    public function createCompanyAssignmentConstraint(?int $idCustomerGroup): Constraint
    {
        return new CompanyAssignmentConstraint($this->getRepository(), $idCustomerGroup);
    }

    public function createCompanyBusinessUnitAssignmentConstraint(?int $idCustomerGroup): Constraint
    {
        return new CompanyBusinessUnitAssignmentConstraint($this->getRepository(), $idCustomerGroup);
    }

    public function createCustomerGroupFormExpander(): CustomerGroupFormExpanderInterface
    {
        return new CustomerGroupFormExpander();
    }

    public function getCompanyPropelQuery(): SpyCompanyQuery
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::PROPEL_QUERY_COMPANY);
    }

    public function getCompanyBusinessUnitPropelQuery(): SpyCompanyBusinessUnitQuery
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::PROPEL_QUERY_COMPANY_BUSINESS_UNIT);
    }
}
