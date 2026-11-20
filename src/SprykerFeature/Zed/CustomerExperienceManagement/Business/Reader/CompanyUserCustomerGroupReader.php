<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Resolver\CompanyBusinessUnitAncestorResolverInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface;

class CompanyUserCustomerGroupReader implements CompanyUserCustomerGroupReaderInterface
{
    public function __construct(
        protected CustomerExperienceManagementRepositoryInterface $customerExperienceManagementRepository,
        protected CompanyBusinessUnitAncestorResolverInterface $companyBusinessUnitAncestorResolver
    ) {
    }

    public function getCustomerGroupCollectionByCustomer(CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer
    {
        $customerGroupCollectionTransfer = new CustomerGroupCollectionTransfer();

        if ($customerTransfer->getIdCustomer() === null) {
            return $customerGroupCollectionTransfer;
        }

        foreach ($this->getCompanyUserCustomerGroupTransfers($customerTransfer) as $customerGroupTransfer) {
            $customerGroupCollectionTransfer->addGroup($customerGroupTransfer);
        }

        return $customerGroupCollectionTransfer;
    }

    protected function resolveCompanyUser(CustomerTransfer $customerTransfer): ?CompanyUserTransfer
    {
        $companyUserTransfer = $customerTransfer->getCompanyUserTransfer();

        if ($companyUserTransfer === null || $companyUserTransfer->getFkCompany() !== null) {
            return $companyUserTransfer;
        }

        $idCompanyUser = $companyUserTransfer->getIdCompanyUser();
        $idCustomer = $customerTransfer->getIdCustomer();

        if ($idCompanyUser === null || $idCustomer === null) {
            return null;
        }

        return $this->customerExperienceManagementRepository->findActiveCompanyUserByIdCompanyUserAndIdCustomer($idCompanyUser, $idCustomer);
    }

    /**
     * @return list<\Generated\Shared\Transfer\CustomerGroupTransfer>
     */
    protected function getCompanyUserCustomerGroupTransfers(CustomerTransfer $customerTransfer): array
    {
        $companyUserTransfer = $this->resolveCompanyUser($customerTransfer);

        if ($companyUserTransfer === null) {
            return [];
        }

        $idCompany = $companyUserTransfer->getFkCompanyOrFail();
        $idCompanyBusinessUnit = $companyUserTransfer->getFkCompanyBusinessUnit();
        $customerGroupTransfers = $this->customerExperienceManagementRepository->getCustomerGroupTransfersByIdCompany($idCompany);

        if ($idCompanyBusinessUnit === null) {
            return $customerGroupTransfers;
        }

        $companyBusinessUnitIdsWithAncestors = $this->companyBusinessUnitAncestorResolver->resolveCompanyBusinessUnitIdsWithAncestors(
            $idCompanyBusinessUnit,
            $this->customerExperienceManagementRepository->getParentCompanyBusinessUnitIdsByIdCompany($idCompany),
        );

        if ($companyBusinessUnitIdsWithAncestors === []) {
            return $customerGroupTransfers;
        }

        $customerGroupTransfersByIdCompanyBusinessUnit = $this->customerExperienceManagementRepository
            ->getCustomerGroupTransfersGroupedByIdCompanyBusinessUnit($companyBusinessUnitIdsWithAncestors);

        foreach ($companyBusinessUnitIdsWithAncestors as $idAncestorCompanyBusinessUnit) {
            array_push($customerGroupTransfers, ...$customerGroupTransfersByIdCompanyBusinessUnit[$idAncestorCompanyBusinessUnit]);
        }

        return $customerGroupTransfers;
    }
}
