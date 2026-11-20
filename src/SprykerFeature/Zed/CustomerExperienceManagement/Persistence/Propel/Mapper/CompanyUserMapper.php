<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Orm\Zed\CompanyUser\Persistence\Map\SpyCompanyUserTableMap;

class CompanyUserMapper
{
    /**
     * @param array<string, mixed> $companyUserRow
     */
    public function mapCompanyUserRowToCompanyUserTransfer(array $companyUserRow, CompanyUserTransfer $companyUserTransfer): CompanyUserTransfer
    {
        $idCompanyBusinessUnit = $companyUserRow[SpyCompanyUserTableMap::COL_FK_COMPANY_BUSINESS_UNIT];

        return $companyUserTransfer
            ->setIdCompanyUser((int)$companyUserRow[SpyCompanyUserTableMap::COL_ID_COMPANY_USER])
            ->setFkCustomer((int)$companyUserRow[SpyCompanyUserTableMap::COL_FK_CUSTOMER])
            ->setFkCompany((int)$companyUserRow[SpyCompanyUserTableMap::COL_FK_COMPANY])
            ->setFkCompanyBusinessUnit($idCompanyBusinessUnit === null ? null : (int)$idCompanyBusinessUnit);
    }
}
