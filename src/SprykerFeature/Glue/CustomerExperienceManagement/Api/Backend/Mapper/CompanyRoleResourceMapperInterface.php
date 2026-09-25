<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyRolesBackendResource;
use Generated\Shared\Transfer\CompanyRoleTransfer;

interface CompanyRoleResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCompanyRoleTransferToResourceData(CompanyRoleTransfer $companyRoleTransfer): array;

    public function mapResourceToCompanyRoleTransfer(
        CompanyRolesBackendResource $companyRolesBackendResource,
        CompanyRoleTransfer $companyRoleTransfer
    ): CompanyRoleTransfer;
}
