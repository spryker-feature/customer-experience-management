<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompaniesBackendResource;
use Generated\Shared\Transfer\CompanyTransfer;

class CompanyResourceMapper implements CompanyResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCompanyTransferToResourceData(CompanyTransfer $companyTransfer): array
    {
        return $companyTransfer->toArray(false, true);
    }

    public function mapResourceToCompanyTransfer(
        CompaniesBackendResource $resource,
        CompanyTransfer $companyTransfer
    ): CompanyTransfer {
        if ($resource->name !== null) {
            $companyTransfer->setName($resource->name);
        }

        if ($resource->status !== null) {
            $companyTransfer->setStatus($resource->status);
        }

        if ($resource->isActive !== null) {
            $companyTransfer->setIsActive($resource->isActive);
        }

        return $companyTransfer;
    }
}
