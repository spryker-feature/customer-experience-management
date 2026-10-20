<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Resolver;

use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Error\CustomerGroupMembershipsBackendErrorCollection;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyBusinessUnitReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementFacadeInterface;

class CustomerGroupMembershipIdResolver implements CustomerGroupMembershipIdResolverInterface
{
    public function __construct(
        protected CompanyReaderInterface $companyReader,
        protected CompanyBusinessUnitReaderInterface $companyBusinessUnitReader,
        protected CustomerExperienceManagementFacadeInterface $customerExperienceManagementFacade,
    ) {
    }

    /**
     * @param list<string> $companyUuids
     *
     * @return list<int>
     */
    public function getCompanyIdsToAssign(array $companyUuids): array
    {
        $companyIdsByUuid = $this->getCompanyIdsIndexedByUuid($companyUuids);
        $customerGroupMembershipsBackendErrorCollection = new CustomerGroupMembershipsBackendErrorCollection();

        foreach (array_diff($companyUuids, array_keys($companyIdsByUuid)) as $unknownCompanyUuid) {
            $customerGroupMembershipsBackendErrorCollection->addCompanyNotFound($unknownCompanyUuid);
        }

        $this->assertNoErrors($customerGroupMembershipsBackendErrorCollection);

        return array_values($companyIdsByUuid);
    }

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return list<int>
     */
    public function getCompanyBusinessUnitIdsToAssign(array $companyBusinessUnitUuids): array
    {
        $companyBusinessUnitIdsByUuid = $this->companyBusinessUnitReader->getCompanyBusinessUnitIdsIndexedByUuid($companyBusinessUnitUuids);
        $customerGroupMembershipsBackendErrorCollection = new CustomerGroupMembershipsBackendErrorCollection();

        foreach (array_diff($companyBusinessUnitUuids, array_keys($companyBusinessUnitIdsByUuid)) as $unknownCompanyBusinessUnitUuid) {
            $customerGroupMembershipsBackendErrorCollection->addCompanyBusinessUnitNotFound($unknownCompanyBusinessUnitUuid);
        }

        $this->assertNoErrors($customerGroupMembershipsBackendErrorCollection);

        return array_values($companyBusinessUnitIdsByUuid);
    }

    public function findMemberIdCompany(CustomerGroupTransfer $customerGroupTransfer, string $companyUuid): ?int
    {
        return $this->getMemberCompanyIdsIndexedByUuid($customerGroupTransfer, [$companyUuid])[$companyUuid] ?? null;
    }

    public function findMemberIdCompanyBusinessUnit(CustomerGroupTransfer $customerGroupTransfer, string $companyBusinessUnitUuid): ?int
    {
        return $this->getMemberCompanyBusinessUnitIdsIndexedByUuid($customerGroupTransfer, [$companyBusinessUnitUuid])[$companyBusinessUnitUuid] ?? null;
    }

    /**
     * @param list<string> $companyUuids
     *
     * @return array<string, int>
     */
    protected function getCompanyIdsIndexedByUuid(array $companyUuids): array
    {
        return array_map(
            fn (CompanyTransfer $companyTransfer): int => $companyTransfer->getIdCompanyOrFail(),
            $this->companyReader->getCompaniesIndexedByUuid($companyUuids),
        );
    }

    /**
     * @param list<string> $companyUuids
     *
     * @return array<string, int>
     */
    protected function getMemberCompanyIdsIndexedByUuid(CustomerGroupTransfer $customerGroupTransfer, array $companyUuids): array
    {
        $customerGroupCompanyCriteriaTransfer = $this->createCustomerGroupCompanyCriteria($customerGroupTransfer);
        $customerGroupCompanyCriteriaTransfer->getCustomerGroupCompanyConditionsOrFail()->setCompanyUuids($companyUuids);

        $memberCompanyIdsByUuid = [];

        foreach ($this->customerExperienceManagementFacade->getCustomerGroupCompanyCollection($customerGroupCompanyCriteriaTransfer)->getCustomerGroupCompanies() as $customerGroupCompanyTransfer) {
            $memberCompanyIdsByUuid[$customerGroupCompanyTransfer->getCompanyUuidOrFail()] = $customerGroupCompanyTransfer->getIdCompanyOrFail();
        }

        return $memberCompanyIdsByUuid;
    }

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return array<string, int>
     */
    protected function getMemberCompanyBusinessUnitIdsIndexedByUuid(
        CustomerGroupTransfer $customerGroupTransfer,
        array $companyBusinessUnitUuids
    ): array {
        $customerGroupCompanyBusinessUnitCriteriaTransfer = $this->createCustomerGroupCompanyBusinessUnitCriteria($customerGroupTransfer);
        $customerGroupCompanyBusinessUnitCriteriaTransfer->getCustomerGroupCompanyBusinessUnitConditionsOrFail()
            ->setCompanyBusinessUnitUuids($companyBusinessUnitUuids);

        $memberCompanyBusinessUnitIdsByUuid = [];
        $customerGroupCompanyBusinessUnitCollectionTransfer = $this->customerExperienceManagementFacade
            ->getCustomerGroupCompanyBusinessUnitCollection($customerGroupCompanyBusinessUnitCriteriaTransfer);

        foreach ($customerGroupCompanyBusinessUnitCollectionTransfer->getCustomerGroupCompanyBusinessUnits() as $customerGroupCompanyBusinessUnitTransfer) {
            $memberCompanyBusinessUnitIdsByUuid[$customerGroupCompanyBusinessUnitTransfer->getCompanyBusinessUnitUuidOrFail()]
                = $customerGroupCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        return $memberCompanyBusinessUnitIdsByUuid;
    }

    protected function createCustomerGroupCompanyCriteria(CustomerGroupTransfer $customerGroupTransfer): CustomerGroupCompanyCriteriaTransfer
    {
        return (new CustomerGroupCompanyCriteriaTransfer())->setCustomerGroupCompanyConditions(
            (new CustomerGroupCompanyConditionsTransfer())->addIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );
    }

    protected function createCustomerGroupCompanyBusinessUnitCriteria(
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupCompanyBusinessUnitCriteriaTransfer {
        return (new CustomerGroupCompanyBusinessUnitCriteriaTransfer())->setCustomerGroupCompanyBusinessUnitConditions(
            (new CustomerGroupCompanyBusinessUnitConditionsTransfer())->addIdCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail()),
        );
    }

    protected function assertNoErrors(CustomerGroupMembershipsBackendErrorCollection $customerGroupMembershipsBackendErrorCollection): void
    {
        if ($customerGroupMembershipsBackendErrorCollection->isEmpty()) {
            return;
        }

        throw $customerGroupMembershipsBackendErrorCollection->toGlueApiException();
    }
}
