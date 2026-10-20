<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class AssignedCompanyTable extends AbstractCompanyAssignmentTable
{
    public const string SOURCE_TAB_NAME = 'assigned-companies';

    public const string SOURCE_TAB_TITLE = 'Assigned companies';

    public const string DESTINATION_TAB_NAME = 'companies-to-be-de-assigned';

    public const string DESTINATION_TAB_TITLE = 'Companies to be de-assigned';

    public const string DESTINATION_TABLE_ID = 'companies-to-be-de-assigned-table';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyTableController
     *
     * @var string
     */
    protected $baseUrl = '/customer-experience-management/assigned-company-table';

    /**
     * @var string
     */
    protected $tableIdentifier = 'assigned-company-table';

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

    protected function filterCompanyQuery(SpyCompanyQuery $companyQuery): SpyCompanyQuery
    {
        $companyQuery
            ->useSpyCustomerGroupToCompanyQuery()
                ->filterByFkCustomerGroup($this->idCustomerGroup)
            ->endUse();

        return $companyQuery;
    }
}
