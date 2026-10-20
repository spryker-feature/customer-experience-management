<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Writer;

use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerGroupsBackendExceptionFactory;

class CustomerGroupMembershipWriter implements CustomerGroupMembershipWriterInterface
{
    public function __construct(
        protected CustomerGroupFacadeInterface $customerGroupFacade,
        protected CustomerGroupsBackendExceptionFactory $customerGroupsBackendExceptionFactory,
    ) {
    }

    /**
     * @param list<int> $companyIds
     */
    public function assignCompanies(CustomerGroupTransfer $customerGroupTransfer, array $companyIds): void
    {
        if ($companyIds === []) {
            return;
        }

        $this->saveCustomerGroup(
            $customerGroupTransfer->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->setIdsCompanyToAssign($companyIds)),
        );
    }

    /**
     * @param list<int> $companyIds
     */
    public function unassignCompanies(CustomerGroupTransfer $customerGroupTransfer, array $companyIds): void
    {
        if ($companyIds === []) {
            return;
        }

        $this->saveCustomerGroup(
            $customerGroupTransfer->setCompanyAssignment((new CustomerGroupToCompanyAssignmentTransfer())->setIdsCompanyToDeAssign($companyIds)),
        );
    }

    /**
     * @param list<int> $companyIds
     */
    public function replaceCompanies(CustomerGroupTransfer $customerGroupTransfer, array $companyIds): void
    {
        $this->saveCustomerGroup(
            $customerGroupTransfer->setCompanyAssignment(
                (new CustomerGroupToCompanyAssignmentTransfer())
                    ->setIsDeAssignAll(true)
                    ->setIdsCompanyToAssign($companyIds),
            ),
        );
    }

    /**
     * @param list<int> $companyBusinessUnitIds
     */
    public function assignCompanyBusinessUnits(CustomerGroupTransfer $customerGroupTransfer, array $companyBusinessUnitIds): void
    {
        if ($companyBusinessUnitIds === []) {
            return;
        }

        $this->saveCustomerGroup(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())->setIdsCompanyBusinessUnitToAssign($companyBusinessUnitIds),
            ),
        );
    }

    /**
     * @param list<int> $companyBusinessUnitIds
     */
    public function unassignCompanyBusinessUnits(CustomerGroupTransfer $customerGroupTransfer, array $companyBusinessUnitIds): void
    {
        if ($companyBusinessUnitIds === []) {
            return;
        }

        $this->saveCustomerGroup(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())->setIdsCompanyBusinessUnitToDeAssign($companyBusinessUnitIds),
            ),
        );
    }

    /**
     * @param list<int> $companyBusinessUnitIds
     */
    public function replaceCompanyBusinessUnits(CustomerGroupTransfer $customerGroupTransfer, array $companyBusinessUnitIds): void
    {
        $this->saveCustomerGroup(
            $customerGroupTransfer->setCompanyBusinessUnitAssignment(
                (new CustomerGroupToCompanyBusinessUnitAssignmentTransfer())
                    ->setIsDeAssignAll(true)
                    ->setIdsCompanyBusinessUnitToAssign($companyBusinessUnitIds),
            ),
        );
    }

    protected function saveCustomerGroup(CustomerGroupTransfer $customerGroupTransfer): void
    {
        $customerGroupCollectionResponseTransfer = $this->customerGroupFacade->updateCustomerGroupCollection(
            (new CustomerGroupCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCustomerGroup($customerGroupTransfer),
        );

        if ($customerGroupCollectionResponseTransfer->getErrors()->count() === 0) {
            return;
        }

        throw $this->customerGroupsBackendExceptionFactory->createValidationException($customerGroupCollectionResponseTransfer);
    }
}
