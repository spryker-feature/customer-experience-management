<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyBusinessUnitAddressesBackendResource;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;

interface CompanyBusinessUnitAddressResourceMapperInterface
{
    public function mapResourceToCompanyUnitAddressTransfer(
        CompanyBusinessUnitAddressesBackendResource $resource,
        CompanyUnitAddressTransfer $companyUnitAddressTransfer
    ): CompanyUnitAddressTransfer;

    /**
     * @return array<string, mixed>
     */
    public function mapCompanyUnitAddressTransferToResourceData(
        CompanyUnitAddressTransfer $companyUnitAddressTransfer
    ): array;
}
