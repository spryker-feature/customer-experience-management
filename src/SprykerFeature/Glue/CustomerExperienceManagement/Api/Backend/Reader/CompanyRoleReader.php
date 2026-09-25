<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyRoleTransfer;
use Spryker\Zed\CompanyRole\Business\CompanyRoleFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyRolesBackendExceptionFactory;

class CompanyRoleReader implements CompanyRoleReaderInterface
{
    public function __construct(
        protected CompanyRoleFacadeInterface $companyRoleFacade,
        protected CompanyRolesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getCompanyRoleByUuid(string $uuid): CompanyRoleTransfer
    {
        $companyRoleResponseTransfer = $this->companyRoleFacade->findCompanyRoleByUuid(
            (new CompanyRoleTransfer())->setUuid($uuid),
        );

        if (!$companyRoleResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createCompanyRoleNotFoundException($uuid);
        }

        return $companyRoleResponseTransfer->getCompanyRoleTransferOrFail();
    }

    public function getCompanyRoleById(int $idCompanyRole): CompanyRoleTransfer
    {
        $companyRoleTransfer = $this->companyRoleFacade->findCompanyRoleById(
            (new CompanyRoleTransfer())->setIdCompanyRole($idCompanyRole),
        );

        if ($companyRoleTransfer === null) {
            throw $this->exceptionFactory->createCompanyRoleNotFoundException((string)$idCompanyRole);
        }

        return $companyRoleTransfer;
    }
}
