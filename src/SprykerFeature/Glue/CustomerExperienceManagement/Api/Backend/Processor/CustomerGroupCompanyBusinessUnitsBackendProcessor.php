<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

class CustomerGroupCompanyBusinessUnitsBackendProcessor extends AbstractCustomerGroupMembershipsBackendProcessor
{
    protected const string URI_VARIABLE_COMPANY_BUSINESS_UNIT_UUID = 'companyBusinessUnitUuid';

    protected function processPost(mixed $data): null
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCompanyBusinessUnitsBackendResource $resource */
        $resource = $data;
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable());

        $this->customerGroupMembershipWriter->assignCompanyBusinessUnits(
            $customerGroupTransfer,
            $this->customerGroupMembershipIdResolver->getCompanyBusinessUnitIdsToAssign(array_values((array)$resource->companyBusinessUnitUuids)),
        );

        return null;
    }

    protected function processPatch(mixed $data): null
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCompanyBusinessUnitsBackendResource $resource */
        $resource = $data;
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable());

        $this->customerGroupMembershipWriter->replaceCompanyBusinessUnits(
            $customerGroupTransfer,
            $this->customerGroupMembershipIdResolver->getCompanyBusinessUnitIdsToAssign(array_values((array)$resource->companyBusinessUnitUuids)),
        );

        return null;
    }

    protected function processDelete(): null
    {
        $uuid = $this->getUuidUriVariable();
        $companyBusinessUnitUuid = (string)$this->getUriVariable(static::URI_VARIABLE_COMPANY_BUSINESS_UNIT_UUID);
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($uuid);
        $idCompanyBusinessUnit = $this->customerGroupMembershipIdResolver->findMemberIdCompanyBusinessUnit(
            $customerGroupTransfer,
            $companyBusinessUnitUuid,
        );

        if ($idCompanyBusinessUnit === null) {
            throw $this->customerGroupsBackendExceptionFactory->createCompanyBusinessUnitAssignmentNotFoundException($companyBusinessUnitUuid, $uuid);
        }

        $this->customerGroupMembershipWriter->unassignCompanyBusinessUnits($customerGroupTransfer, [$idCompanyBusinessUnit]);

        return null;
    }
}
