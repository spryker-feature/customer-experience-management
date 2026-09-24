<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;

interface CompanyBusinessUnitResourceMapperInterface
{
    /**
     * Only the properties present on the resource are applied, so an update leaves the rest of the
     * business unit untouched.
     *
     * `companyUuid` and `parentBusinessUnitUuid` are resolved to their ids here, which means this
     * method reads and can therefore fail: it raises a not-found for either uuid, and rejects a
     * parent that belongs to another company.
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When a uuid matches nothing, or the
     *   parent belongs to another company.
     */
    public function mapResourceToCompanyBusinessUnitTransfer(
        CompanyBusinessUnitsBackendResource $resource,
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): CompanyBusinessUnitTransfer;

    /**
     * @return array<string, mixed>
     */
    public function mapCompanyBusinessUnitTransferToResourceData(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): array;
}
