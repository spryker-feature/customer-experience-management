<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

abstract class AbstractCustomerGroupAssignmentTable extends AbstractTable
{
    public const string PARAM_ID_CUSTOMER_GROUP = 'id-customer-group';

    protected const string COL_NAME = 'name';

    protected const string COL_CUSTOMER_COUNT = 'customer_count';

    protected const string COL_ACTIONS = 'actions';

    protected const string TITLE_ID = 'ID';

    protected const string TITLE_COMPANY = 'Company';

    protected const string TITLE_CUSTOMER_COUNT = 'Customers';

    protected const string TITLE_ACTIONS = 'Actions';

    protected const string BUTTON_TITLE_EDIT = 'Edit';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyTableController::tableAction()
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyBusinessUnitTableController::tableAction()
     */
    protected const string URL_PATH_TABLE = 'table';

    /**
     * @param list<\SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander\CustomerGroupAssignmentTableExpanderInterface> $customerGroupAssignmentTableExpanders
     */
    public function __construct(
        protected ?int $idCustomerGroup,
        protected array $customerGroupAssignmentTableExpanders = []
    ) {
    }

    abstract protected function configureColumns(TableConfiguration $config): TableConfiguration;

    abstract protected function prepareQuery(): ModelCriteria;

    abstract protected function getIdColumn(): string;

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    abstract protected function formatRow(array $item): array;

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config = $this->configureColumns($config);
        $config->setUseSorting(true);
        $config->setUseCaseSensitiveSearch(false);
        $config->setUrl(sprintf('%s?%s=%s', $this->getUrlPath(), static::PARAM_ID_CUSTOMER_GROUP, $this->idCustomerGroup));

        foreach ($this->customerGroupAssignmentTableExpanders as $customerGroupAssignmentTableExpander) {
            $config = $customerGroupAssignmentTableExpander->expandConfiguration($config, $this->getIdColumn());
        }

        return $config;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $rows = [];

        foreach ($this->runQuery($this->prepareQuery(), $config) as $item) {
            $rows[] = $this->expandRow($this->formatRow($item));
        }

        return $rows;
    }

    protected function getUrlPath(): string
    {
        $urlPath = static::URL_PATH_TABLE;

        foreach ($this->customerGroupAssignmentTableExpanders as $customerGroupAssignmentTableExpander) {
            $urlPath = $customerGroupAssignmentTableExpander->expandUrlPath($urlPath);
        }

        return $urlPath;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    protected function expandRow(array $row): array
    {
        foreach ($this->customerGroupAssignmentTableExpanders as $customerGroupAssignmentTableExpander) {
            $row = $customerGroupAssignmentTableExpander->expandRow($row, $this->getIdColumn());
        }

        return $row;
    }

    protected function addActionsColumn(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader($config->getHeader() + [static::COL_ACTIONS => static::TITLE_ACTIONS]);
        $config->addRawColumn(static::COL_ACTIONS);

        return $config;
    }
}
