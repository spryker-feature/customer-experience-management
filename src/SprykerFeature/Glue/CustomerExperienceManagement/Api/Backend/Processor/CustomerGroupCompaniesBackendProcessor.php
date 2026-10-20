<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

class CustomerGroupCompaniesBackendProcessor extends AbstractCustomerGroupMembershipsBackendProcessor
{
    protected const string URI_VARIABLE_COMPANY_UUID = 'companyUuid';

    protected function processPost(mixed $data): null
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCompaniesBackendResource $resource */
        $resource = $data;
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable());

        $this->customerGroupMembershipWriter->assignCompanies(
            $customerGroupTransfer,
            $this->customerGroupMembershipIdResolver->getCompanyIdsToAssign(array_values((array)$resource->companyUuids)),
        );

        return null;
    }

    protected function processPatch(mixed $data): null
    {
        /** @var \Generated\Api\Backend\CustomerGroupsCompaniesBackendResource $resource */
        $resource = $data;
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($this->getUuidUriVariable());

        $this->customerGroupMembershipWriter->replaceCompanies(
            $customerGroupTransfer,
            $this->customerGroupMembershipIdResolver->getCompanyIdsToAssign(array_values((array)$resource->companyUuids)),
        );

        return null;
    }

    protected function processDelete(): null
    {
        $uuid = $this->getUuidUriVariable();
        $companyUuid = (string)$this->getUriVariable(static::URI_VARIABLE_COMPANY_UUID);
        $customerGroupTransfer = $this->customerGroupReader->getCustomerGroupByUuid($uuid);
        $idCompany = $this->customerGroupMembershipIdResolver->findMemberIdCompany($customerGroupTransfer, $companyUuid);

        if ($idCompany === null) {
            throw $this->customerGroupsBackendExceptionFactory->createCompanyAssignmentNotFoundException($companyUuid, $uuid);
        }

        $this->customerGroupMembershipWriter->unassignCompanies($customerGroupTransfer, [$idCompany]);

        return null;
    }
}
