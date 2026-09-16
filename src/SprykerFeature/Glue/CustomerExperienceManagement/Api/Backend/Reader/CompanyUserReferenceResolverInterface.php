<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyUserTransfer;

interface CompanyUserReferenceResolverInterface
{
    public function resolveReferences(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer;

    public function resolveCompanyReferences(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CompanyUserTransfer $companyUserTransfer
    ): CompanyUserTransfer;
}
