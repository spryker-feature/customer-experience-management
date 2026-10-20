<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Writer;

use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface;

class CustomerGroupCompanyBusinessUnitRelationWriter implements CustomerGroupCompanyBusinessUnitRelationWriterInterface
{
    use TransactionTrait;

    public function __construct(
        protected CustomerExperienceManagementEntityManagerInterface $customerExperienceManagementEntityManager,
        protected CustomerExperienceManagementConfig $customerExperienceManagementConfig
    ) {
    }

    public function saveCustomerGroupCompanyBusinessUnitRelations(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer
    {
        $customerGroupToCompanyBusinessUnitAssignmentTransfer = $customerGroupTransfer->getCompanyBusinessUnitAssignment();

        if ($customerGroupToCompanyBusinessUnitAssignmentTransfer === null) {
            return $customerGroupTransfer;
        }

        $idCustomerGroup = $customerGroupTransfer->getIdCustomerGroupOrFail();

        $this->getTransactionHandler()->handleTransaction(function () use ($idCustomerGroup, $customerGroupToCompanyBusinessUnitAssignmentTransfer): void {
            $this->executeSaveCustomerGroupCompanyBusinessUnitRelationsTransaction($idCustomerGroup, $customerGroupToCompanyBusinessUnitAssignmentTransfer);
        });

        return $customerGroupTransfer;
    }

    protected function executeSaveCustomerGroupCompanyBusinessUnitRelationsTransaction(
        int $idCustomerGroup,
        CustomerGroupToCompanyBusinessUnitAssignmentTransfer $customerGroupToCompanyBusinessUnitAssignmentTransfer
    ): void {
        $chunkSize = $this->customerExperienceManagementConfig->getCustomerGroupRelationChunkSize();
        $companyBusinessUnitIdsToAssign = array_unique($customerGroupToCompanyBusinessUnitAssignmentTransfer->getIdsCompanyBusinessUnitToAssign());

        $this->deleteCustomerGroupCompanyBusinessUnitRelations($idCustomerGroup, $customerGroupToCompanyBusinessUnitAssignmentTransfer);

        foreach (array_chunk($companyBusinessUnitIdsToAssign, $chunkSize) as $companyBusinessUnitIdsChunk) {
            $this->customerExperienceManagementEntityManager->createCustomerGroupToCompanyBusinessUnitRelations([$idCustomerGroup => $companyBusinessUnitIdsChunk]);
        }
    }

    protected function deleteCustomerGroupCompanyBusinessUnitRelations(
        int $idCustomerGroup,
        CustomerGroupToCompanyBusinessUnitAssignmentTransfer $customerGroupToCompanyBusinessUnitAssignmentTransfer
    ): void {
        if ($customerGroupToCompanyBusinessUnitAssignmentTransfer->getIsDeAssignAll() === true) {
            $this->customerExperienceManagementEntityManager->deleteCustomerGroupToCompanyBusinessUnitRelationsExcept(
                $idCustomerGroup,
                $customerGroupToCompanyBusinessUnitAssignmentTransfer->getIdsCompanyBusinessUnitToAssign(),
            );

            return;
        }

        $chunkSize = $this->customerExperienceManagementConfig->getCustomerGroupRelationChunkSize();
        $companyBusinessUnitIdsToDeAssign = $customerGroupToCompanyBusinessUnitAssignmentTransfer->getIdsCompanyBusinessUnitToDeAssign();

        foreach (array_chunk($companyBusinessUnitIdsToDeAssign, $chunkSize) as $companyBusinessUnitIdsChunk) {
            $this->customerExperienceManagementEntityManager->deleteCustomerGroupToCompanyBusinessUnitRelations(
                $idCustomerGroup,
                $companyBusinessUnitIdsChunk,
            );
        }
    }
}
