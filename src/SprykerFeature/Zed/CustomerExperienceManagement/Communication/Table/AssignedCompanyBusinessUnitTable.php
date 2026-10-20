<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class AssignedCompanyBusinessUnitTable extends AbstractCompanyBusinessUnitAssignmentTable
{
    public const string SOURCE_TAB_NAME = 'assigned-company-business-units';

    public const string SOURCE_TAB_TITLE = 'Assigned business units';

    public const string DESTINATION_TAB_NAME = 'company-business-units-to-be-de-assigned';

    public const string DESTINATION_TAB_TITLE = 'Business units to be de-assigned';

    public const string DESTINATION_TABLE_ID = 'company-business-units-to-be-de-assigned-table';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyBusinessUnitTableController
     *
     * @var string
     */
    protected $baseUrl = '/customer-experience-management/assigned-company-business-unit-table';

    /**
     * @var string
     */
    protected $tableIdentifier = 'assigned-company-business-unit-table';

    /**
     * @return list<array<string, mixed>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        if ($this->idCustomerGroup === null) {
            return [];
        }

        return parent::prepareData($config);
    }

    protected function filterCompanyBusinessUnitQuery(SpyCompanyBusinessUnitQuery $companyBusinessUnitQuery): SpyCompanyBusinessUnitQuery
    {
        $companyBusinessUnitQuery
            ->useSpyCustomerGroupToCompanyBusinessUnitQuery()
                ->filterByFkCustomerGroup($this->idCustomerGroup)
            ->endUse();

        return $companyBusinessUnitQuery;
    }
}
