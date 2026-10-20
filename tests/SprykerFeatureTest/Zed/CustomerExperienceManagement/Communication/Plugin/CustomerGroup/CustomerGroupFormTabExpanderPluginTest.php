<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\TabItemTransfer;
use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormTabExpanderPluginInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupFormTabExpanderPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupFormTabExpanderPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Plugin
 * @group CustomerGroup
 * @group CustomerGroupFormTabExpanderPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupFormTabExpanderPluginTest extends Unit
{
    protected const string TAB_NAME_EXISTING = 'existing-tab';

    /**
     * @dataProvider provideTabExpanderPlugins
     */
    public function testExpandAppendsTheAssignmentTabAfterTheExistingTabs(
        CustomerGroupFormTabExpanderPluginInterface $customerGroupFormTabExpanderPlugin,
        string $expectedTabName,
        string $expectedTabTitle,
        string $expectedTabTemplate
    ): void {
        // Arrange
        $tabsViewTransfer = (new TabsViewTransfer())->addTab((new TabItemTransfer())->setName(static::TAB_NAME_EXISTING));

        // Act
        $tabsViewTransfer = $customerGroupFormTabExpanderPlugin->expand($tabsViewTransfer);

        // Assert
        $this->assertCount(2, $tabsViewTransfer->getTabs());

        /** @var \Generated\Shared\Transfer\TabItemTransfer $tabItemTransfer */
        $tabItemTransfer = $tabsViewTransfer->getTabs()->offsetGet(1);
        $this->assertSame($expectedTabName, $tabItemTransfer->getName());
        $this->assertSame($expectedTabTitle, $tabItemTransfer->getTitle());
        $this->assertSame($expectedTabTemplate, $tabItemTransfer->getTemplate());
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function provideTabExpanderPlugins(): array
    {
        return [
            'companies' => [
                new CompanyCustomerGroupFormTabExpanderPlugin(),
                'company-assignment',
                'Companies',
                '@CustomerExperienceManagement/_partials/customer-group/company-assignment-tab.twig',
            ],
            'business units' => [
                new CompanyBusinessUnitCustomerGroupFormTabExpanderPlugin(),
                'company-business-unit-assignment',
                'Business Units',
                '@CustomerExperienceManagement/_partials/customer-group/company-business-unit-assignment-tab.twig',
            ],
        ];
    }
}
