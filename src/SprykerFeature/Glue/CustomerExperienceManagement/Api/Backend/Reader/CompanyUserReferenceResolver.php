<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\CompanyRole\Business\CompanyRoleFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyUsersBackendExceptionFactory;

class CompanyUserReferenceResolver implements CompanyUserReferenceResolverInterface
{
    public function __construct(
        protected CompanyFacadeInterface $companyFacade,
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected CompanyRoleFacadeInterface $companyRoleFacade,
        protected CustomerReaderInterface $customerReader,
        protected CompanyUsersBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function resolveReferences(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer {
        return $this->resolveCompanyReferences(
            $companyUsersBackendResource,
            $this->resolveCustomer($companyUsersBackendResource, $companyUserTransfer),
        );
    }

    public function resolveCompanyReferences(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer {
        $companyUserTransfer = $this->resolveCompany($companyUsersBackendResource, $companyUserTransfer);
        $companyUserTransfer = $this->resolveCompanyBusinessUnit($companyUsersBackendResource, $companyUserTransfer);

        return $this->resolveCompanyRoles($companyUsersBackendResource, $companyUserTransfer);
    }

    protected function resolveCustomer(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer {
        $customerReference = $companyUsersBackendResource->customerReference;

        if ($customerReference === null || $customerReference === '') {
            return $companyUserTransfer;
        }

        $customerTransfer = $this->customerReader->getCustomerByReference($customerReference);

        return $companyUserTransfer
            ->setCustomer($customerTransfer)
            ->setFkCustomer($customerTransfer->getIdCustomerOrFail());
    }

    protected function resolveCompany(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer {
        $companyUuid = $companyUsersBackendResource->companyUuid;

        if ($companyUuid === null || $companyUuid === '') {
            return $companyUserTransfer;
        }

        $companyResponseTransfer = $this->companyFacade->findCompanyByUuid(
            (new CompanyTransfer())->setUuid($companyUuid),
        );

        if (!$companyResponseTransfer->getIsSuccessful() || $companyResponseTransfer->getCompanyTransfer() === null) {
            throw $this->exceptionFactory->createCompanyNotFoundException($companyUuid);
        }

        $companyTransfer = $companyResponseTransfer->getCompanyTransfer();

        return $companyUserTransfer
            ->setCompany($companyTransfer)
            ->setFkCompany($companyTransfer->getIdCompanyOrFail());
    }

    protected function resolveCompanyBusinessUnit(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer {
        $companyBusinessUnitUuid = $companyUsersBackendResource->companyBusinessUnitUuid;

        if ($companyBusinessUnitUuid === null || $companyBusinessUnitUuid === '') {
            return $companyUserTransfer;
        }

        $companyBusinessUnitResponseTransfer = $this->companyBusinessUnitFacade->findCompanyBusinessUnitByUuid(
            (new CompanyBusinessUnitTransfer())->setUuid($companyBusinessUnitUuid),
        );

        if (
            !$companyBusinessUnitResponseTransfer->getIsSuccessful()
            || $companyBusinessUnitResponseTransfer->getCompanyBusinessUnitTransfer() === null
        ) {
            throw $this->exceptionFactory->createCompanyBusinessUnitNotFoundException($companyBusinessUnitUuid);
        }

        $companyBusinessUnitTransfer = $companyBusinessUnitResponseTransfer->getCompanyBusinessUnitTransfer();

        return $companyUserTransfer
            ->setCompanyBusinessUnit($companyBusinessUnitTransfer)
            ->setFkCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());
    }

    protected function resolveCompanyRoles(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer {
        if ($companyUsersBackendResource->companyRoleUuids === []) {
            return $companyUserTransfer;
        }

        $companyRoleCollectionTransfer = new CompanyRoleCollectionTransfer();

        foreach ($companyUsersBackendResource->companyRoleUuids as $companyRoleUuid) {
            $companyRoleCollectionTransfer->addRole($this->getCompanyRoleByUuid((string)$companyRoleUuid));
        }

        return $companyUserTransfer->setCompanyRoleCollection($companyRoleCollectionTransfer);
    }

    protected function getCompanyRoleByUuid(string $companyRoleUuid): CompanyRoleTransfer
    {
        $companyRoleResponseTransfer = $this->companyRoleFacade->findCompanyRoleByUuid(
            (new CompanyRoleTransfer())->setUuid($companyRoleUuid),
        );

        if (
            !$companyRoleResponseTransfer->getIsSuccessful()
            || $companyRoleResponseTransfer->getCompanyRoleTransfer() === null
        ) {
            throw $this->exceptionFactory->createCompanyRoleNotFoundException($companyRoleUuid);
        }

        return $companyRoleResponseTransfer->getCompanyRoleTransfer();
    }
}
