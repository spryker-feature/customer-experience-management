<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

class AvailableCompanyTable extends AbstractCompanyAssignmentTable
{
    public const string SOURCE_TAB_NAME = 'available-companies';

    public const string SOURCE_TAB_TITLE = 'Available companies';

    public const string DESTINATION_TAB_NAME = 'companies-to-be-assigned';

    public const string DESTINATION_TAB_TITLE = 'Companies to be assigned';

    public const string DESTINATION_TABLE_ID = 'companies-to-be-assigned-table';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AvailableCompanyTableController
     *
     * @var string
     */
    protected $baseUrl = '/customer-experience-management/available-company-table';

    /**
     * @var string
     */
    protected $tableIdentifier = 'available-company-table';

    protected function filterCompanyQuery(SpyCompanyQuery $companyQuery): SpyCompanyQuery
    {
        if ($this->idCustomerGroup === null) {
            return $companyQuery;
        }

        $companyQuery
            ->addJoin(
                [SpyCompanyTableMap::COL_ID_COMPANY, $this->idCustomerGroup],
                [SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY, SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP],
                Criteria::LEFT_JOIN,
            )
            ->addAnd(SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP, null, Criteria::ISNULL);

        return $companyQuery;
    }
}
