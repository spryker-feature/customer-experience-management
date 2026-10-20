<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class ReadOnlyAssignedCompanyTable extends AssignedCompanyTable
{
    /**
     * @uses \Spryker\Zed\CompanyGui\Communication\Controller\EditCompanyController::indexAction()
     */
    protected const string URL_TEMPLATE_COMPANY_EDIT = '/company-gui/edit-company/index?id-company=%d';

    protected function configureColumns(TableConfiguration $config): TableConfiguration
    {
        return $this->addActionsColumn(parent::configureColumns($config));
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    protected function formatRow(array $item): array
    {
        return parent::formatRow($item) + [static::COL_ACTIONS => $this->buildLinks($item)];
    }

    /**
     * @param array<string, mixed> $item
     */
    protected function buildLinks(array $item): string
    {
        return $this->generateEditButton(
            sprintf(static::URL_TEMPLATE_COMPANY_EDIT, $item[static::COL_ID_COMPANY]),
            static::BUTTON_TITLE_EDIT,
        );
    }
}
