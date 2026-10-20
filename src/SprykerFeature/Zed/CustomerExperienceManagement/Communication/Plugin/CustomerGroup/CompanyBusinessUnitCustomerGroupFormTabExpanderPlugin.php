<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\TabItemTransfer;
use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormTabExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CompanyBusinessUnitCustomerGroupFormTabExpanderPlugin extends AbstractPlugin implements CustomerGroupFormTabExpanderPluginInterface
{
    public const string TAB_NAME = 'company-business-unit-assignment';

    protected const string TAB_TITLE = 'Business Units';

    protected const string TAB_TEMPLATE = '@CustomerExperienceManagement/_partials/customer-group/company-business-unit-assignment-tab.twig';

    /**
     * {@inheritDoc}
     * - Adds the "Business Units" tab, which assigns business units to the customer group and removes them.
     * - The tab is named `TAB_NAME`, so `CustomerGroupConfig::getFormTabOrder()` can position it.
     * - Requires {@link \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupFormExpanderPlugin} to be registered, as the tab renders its form fields.
     *
     * @api
     */
    public function expand(TabsViewTransfer $tabsViewTransfer): TabsViewTransfer
    {
        return $tabsViewTransfer->addTab(
            (new TabItemTransfer())
                ->setName(static::TAB_NAME)
                ->setTitle(static::TAB_TITLE)
                ->setTemplate(static::TAB_TEMPLATE),
        );
    }
}
