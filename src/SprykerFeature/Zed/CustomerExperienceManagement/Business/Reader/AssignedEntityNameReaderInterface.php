<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader;

interface AssignedEntityNameReaderInterface
{
    public function findCustomerName(int $idCustomer): ?string;

    public function findCompanyName(int $idCompany): ?string;

    public function findCompanyBusinessUnitName(int $idCompanyBusinessUnit): ?string;
}
