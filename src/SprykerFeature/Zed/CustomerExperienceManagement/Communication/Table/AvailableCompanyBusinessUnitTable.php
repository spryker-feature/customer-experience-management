<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyBusinessUnitTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

class AvailableCompanyBusinessUnitTable extends AbstractCompanyBusinessUnitAssignmentTable
{
    public const string SOURCE_TAB_NAME = 'available-company-business-units';

    public const string SOURCE_TAB_TITLE = 'Available business units';

    public const string DESTINATION_TAB_NAME = 'company-business-units-to-be-assigned';

    public const string DESTINATION_TAB_TITLE = 'Business units to be assigned';

    public const string DESTINATION_TABLE_ID = 'company-business-units-to-be-assigned-table';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AvailableCompanyBusinessUnitTableController
     *
     * @var string
     */
    protected $baseUrl = '/customer-experience-management/available-company-business-unit-table';

    /**
     * @var string
     */
    protected $tableIdentifier = 'available-company-business-unit-table';

    protected function filterCompanyBusinessUnitQuery(SpyCompanyBusinessUnitQuery $companyBusinessUnitQuery): SpyCompanyBusinessUnitQuery
    {
        if ($this->idCustomerGroup === null) {
            return $companyBusinessUnitQuery;
        }

        $companyBusinessUnitQuery
            ->addJoin(
                [SpyCompanyBusinessUnitTableMap::COL_ID_COMPANY_BUSINESS_UNIT, $this->idCustomerGroup],
                [
                    SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
                    SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP,
                ],
                Criteria::LEFT_JOIN,
            )
            ->addAnd(SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP, null, Criteria::ISNULL);

        return $companyBusinessUnitQuery;
    }
}
