<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Writer;

use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface;

class CustomerGroupCompanyRelationWriter implements CustomerGroupCompanyRelationWriterInterface
{
    use TransactionTrait;

    public function __construct(
        protected CustomerExperienceManagementEntityManagerInterface $customerExperienceManagementEntityManager,
        protected CustomerExperienceManagementConfig $customerExperienceManagementConfig
    ) {
    }

    public function saveCustomerGroupCompanyRelations(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer
    {
        $customerGroupToCompanyAssignmentTransfer = $customerGroupTransfer->getCompanyAssignment();

        if ($customerGroupToCompanyAssignmentTransfer === null) {
            return $customerGroupTransfer;
        }

        $idCustomerGroup = $customerGroupTransfer->getIdCustomerGroupOrFail();

        $this->getTransactionHandler()->handleTransaction(function () use ($idCustomerGroup, $customerGroupToCompanyAssignmentTransfer): void {
            $this->executeSaveCustomerGroupCompanyRelationsTransaction($idCustomerGroup, $customerGroupToCompanyAssignmentTransfer);
        });

        return $customerGroupTransfer;
    }

    protected function executeSaveCustomerGroupCompanyRelationsTransaction(
        int $idCustomerGroup,
        CustomerGroupToCompanyAssignmentTransfer $customerGroupToCompanyAssignmentTransfer
    ): void {
        $chunkSize = $this->customerExperienceManagementConfig->getCustomerGroupRelationChunkSize();
        $companyIdsToAssign = array_unique($customerGroupToCompanyAssignmentTransfer->getIdsCompanyToAssign());

        $this->deleteCustomerGroupCompanyRelations($idCustomerGroup, $customerGroupToCompanyAssignmentTransfer);

        foreach (array_chunk($companyIdsToAssign, $chunkSize) as $companyIdsChunk) {
            $this->customerExperienceManagementEntityManager->createCustomerGroupToCompanyRelations([$idCustomerGroup => $companyIdsChunk]);
        }
    }

    protected function deleteCustomerGroupCompanyRelations(
        int $idCustomerGroup,
        CustomerGroupToCompanyAssignmentTransfer $customerGroupToCompanyAssignmentTransfer
    ): void {
        if ($customerGroupToCompanyAssignmentTransfer->getIsDeAssignAll() === true) {
            $this->customerExperienceManagementEntityManager->deleteCustomerGroupToCompanyRelationsExcept(
                $idCustomerGroup,
                $customerGroupToCompanyAssignmentTransfer->getIdsCompanyToAssign(),
            );

            return;
        }

        $chunkSize = $this->customerExperienceManagementConfig->getCustomerGroupRelationChunkSize();

        foreach (array_chunk($customerGroupToCompanyAssignmentTransfer->getIdsCompanyToDeAssign(), $chunkSize) as $companyIdsChunk) {
            $this->customerExperienceManagementEntityManager->deleteCustomerGroupToCompanyRelations($idCustomerGroup, $companyIdsChunk);
        }
    }
}
