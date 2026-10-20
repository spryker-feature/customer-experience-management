<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupViewExpanderPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupViewExpanderPlugin;
use SprykerFeatureTest\Zed\CustomerExperienceManagement\CustomerExperienceManagementCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Plugin
 * @group CustomerGroup
 * @group CustomerGroupViewExpanderPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupViewExpanderPluginTest extends Unit
{
    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupViewExpanderPlugin::TEMPLATE_VARIABLE_COMPANY_TABLE
     */
    protected const string TEMPLATE_VARIABLE_COMPANY_TABLE = 'companyTable';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupViewExpanderPlugin::TEMPLATE_VARIABLE_COMPANY_BUSINESS_UNIT_TABLE
     */
    protected const string TEMPLATE_VARIABLE_COMPANY_BUSINESS_UNIT_TABLE = 'companyBusinessUnitTable';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyTableController::tableAction()
     */
    protected const string ASSIGNED_COMPANY_TABLE_URL_TEMPLATE = '/customer-experience-management/assigned-company-table/table?id-customer-group=%d';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller\AssignedCompanyBusinessUnitTableController::tableAction()
     */
    protected const string ASSIGNED_COMPANY_BUSINESS_UNIT_TABLE_URL_TEMPLATE = '/customer-experience-management/assigned-company-business-unit-table/table?id-customer-group=%d';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupFormTabExpanderPlugin::TAB_NAME
     */
    protected const string COMPANY_TAB_NAME = 'company-assignment';

    /**
     * @uses \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupFormTabExpanderPlugin::TAB_NAME
     */
    protected const string COMPANY_BUSINESS_UNIT_TAB_NAME = 'company-business-unit-assignment';

    protected CustomerExperienceManagementCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setTableRequest();
    }

    public function testCompanyPluginRendersTheReadOnlyTableOfTheCompaniesAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();

        // Act
        $templateData = (new CompanyCustomerGroupViewExpanderPlugin())->getTemplateData($customerGroupTransfer);

        // Assert
        $this->assertStringContainsString(
            sprintf(static::ASSIGNED_COMPANY_TABLE_URL_TEMPLATE, $customerGroupTransfer->getIdCustomerGroupOrFail()),
            $templateData[static::TEMPLATE_VARIABLE_COMPANY_TABLE],
        );
    }

    public function testCompanyBusinessUnitPluginRendersTheReadOnlyTableOfTheBusinessUnitsAssignedToTheCustomerGroup(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();

        // Act
        $templateData = (new CompanyBusinessUnitCustomerGroupViewExpanderPlugin())->getTemplateData($customerGroupTransfer);

        // Assert
        $this->assertStringContainsString(
            sprintf(static::ASSIGNED_COMPANY_BUSINESS_UNIT_TABLE_URL_TEMPLATE, $customerGroupTransfer->getIdCustomerGroupOrFail()),
            $templateData[static::TEMPLATE_VARIABLE_COMPANY_BUSINESS_UNIT_TABLE],
        );
    }

    public function testCompanyAndBusinessUnitPluginsRenderSeparateTemplates(): void
    {
        // Act
        $companyTemplatePath = (new CompanyCustomerGroupViewExpanderPlugin())->getTemplatePath();
        $companyBusinessUnitTemplatePath = (new CompanyBusinessUnitCustomerGroupViewExpanderPlugin())->getTemplatePath();

        // Assert
        $this->assertNotSame($companyTemplatePath, $companyBusinessUnitTemplatePath);
    }

    public function testPluginsNameTheirBlocksAfterTheirAddAndEditPageTabs(): void
    {
        // Act
        $companyBlockName = (new CompanyCustomerGroupViewExpanderPlugin())->getBlockName();
        $companyBusinessUnitBlockName = (new CompanyBusinessUnitCustomerGroupViewExpanderPlugin())->getBlockName();

        // Assert
        $this->assertSame(static::COMPANY_TAB_NAME, $companyBlockName);
        $this->assertSame(static::COMPANY_BUSINESS_UNIT_TAB_NAME, $companyBusinessUnitBlockName);
    }
}
