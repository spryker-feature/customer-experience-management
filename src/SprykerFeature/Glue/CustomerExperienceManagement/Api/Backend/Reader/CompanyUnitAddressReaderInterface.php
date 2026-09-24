<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyUnitAddressCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;

interface CompanyUnitAddressReaderInterface
{
    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When no address matches the uuid.
     */
    public function getCompanyUnitAddressByUuid(string $uuid): CompanyUnitAddressTransfer;

    /**
     * @param array<int, string> $uuids
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When the list holds an address that cannot be assigned to the company.
     */
    public function getCompanyUnitAddressesByUuids(
        array $uuids,
        ?int $idCompany
    ): CompanyUnitAddressCollectionTransfer;
}
