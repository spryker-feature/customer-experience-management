<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;

interface CompanyBusinessUnitReaderInterface
{
    public function getCompanyBusinessUnitByUuid(string $uuid): CompanyBusinessUnitTransfer;

    public function getParentCompanyBusinessUnitByUuid(string $uuid): CompanyBusinessUnitTransfer;

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return array<string, \Generated\Shared\Transfer\CompanyBusinessUnitTransfer>
     */
    public function getCompanyBusinessUnitsIndexedByUuid(array $companyBusinessUnitUuids): array;

    /**
     * @param list<string> $companyBusinessUnitUuids
     *
     * @return array<string, int>
     */
    public function getCompanyBusinessUnitIdsIndexedByUuid(array $companyBusinessUnitUuids): array;
}
