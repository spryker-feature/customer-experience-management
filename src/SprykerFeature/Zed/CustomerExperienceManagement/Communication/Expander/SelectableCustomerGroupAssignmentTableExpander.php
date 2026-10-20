<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander;

use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class SelectableCustomerGroupAssignmentTableExpander implements CustomerGroupAssignmentTableExpanderInterface
{
    protected const string COL_SELECT_CHECKBOX = 'select-checkbox';

    protected const string TITLE_SELECT_CHECKBOX = 'Select';

    protected const string CHECKBOX_CLASS = 'js-customer-group-assignment-checkbox';

    protected const string CHECKBOX_TEMPLATE = '<input type="checkbox" class="%s" value="%d"/>';

    protected const string ATTRIBUTE_SELECTABLE = 'data-selectable';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AbstractAssignmentTableController::selectableTableAction()
     */
    protected const string URL_PATH_PREFIX_SELECTABLE = 'selectable-';

    public function __construct(
        protected string $destinationTableId,
        protected string $destinationTabName,
        protected string $inputSelector
    ) {
    }

    public function expandConfiguration(TableConfiguration $config, string $idColumn): TableConfiguration
    {
        $config->setHeader($config->getHeader() + [static::COL_SELECT_CHECKBOX => static::TITLE_SELECT_CHECKBOX]);
        $config->addRawColumn(static::COL_SELECT_CHECKBOX);
        $config->setTableAttributes($config->getTableAttributes() + [
            static::ATTRIBUTE_SELECTABLE => $this->getSelectableConfiguration($idColumn),
        ]);

        return $config;
    }

    public function expandUrlPath(string $urlPath): string
    {
        return sprintf('%s%s', static::URL_PATH_PREFIX_SELECTABLE, $urlPath);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function expandRow(array $row, string $idColumn): array
    {
        $row[static::COL_SELECT_CHECKBOX] = sprintf(static::CHECKBOX_TEMPLATE, static::CHECKBOX_CLASS, $row[$idColumn]);

        return $row;
    }

    /**
     * @return array<string, string>
     */
    protected function getSelectableConfiguration(string $idColumn): array
    {
        return [
            'moveToSelector' => sprintf('#%s', $this->destinationTableId),
            'inputSelector' => $this->inputSelector,
            'counterHolderSelector' => sprintf('a[href="#tab-content-%s"]', $this->destinationTabName),
            'checkboxSelector' => sprintf('.%s', static::CHECKBOX_CLASS),
            'colId' => $idColumn,
            'colSelection' => static::COL_SELECT_CHECKBOX,
        ];
    }
}
