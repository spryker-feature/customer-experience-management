<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Tabs;

use Generated\Shared\Transfer\TabItemTransfer;
use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\Gui\Communication\Tabs\AbstractTabs;

class AssignmentTableTabs extends AbstractTabs
{
    protected const string SOURCE_TAB_TEMPLATE = '@CustomerExperienceManagement/_partials/customer-group/source-table.twig';

    protected const string DESTINATION_TAB_TEMPLATE = '@Gui/Table/table.twig';

    public function __construct(
        protected string $sourceTabName,
        protected string $sourceTabTitle,
        protected string $destinationTabName,
        protected string $destinationTabTitle
    ) {
    }

    protected function build(TabsViewTransfer $tabsViewTransfer): TabsViewTransfer
    {
        return $tabsViewTransfer
            ->addTab($this->createTabItemTransfer($this->sourceTabName, $this->sourceTabTitle, static::SOURCE_TAB_TEMPLATE))
            ->addTab($this->createTabItemTransfer($this->destinationTabName, $this->destinationTabTitle, static::DESTINATION_TAB_TEMPLATE))
            ->setIsNavigable(false);
    }

    protected function createTabItemTransfer(string $name, string $title, string $template): TabItemTransfer
    {
        return (new TabItemTransfer())
            ->setName($name)
            ->setTitle($title)
            ->setTemplate($template);
    }
}
