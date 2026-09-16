<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CompanyUserResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCompanyUserTransferToResourceData(CompanyUserTransfer $companyUserTransfer): array;

    public function mapResourceToCustomerTransfer(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CustomerTransfer $customerTransfer
    ): CustomerTransfer;
}
