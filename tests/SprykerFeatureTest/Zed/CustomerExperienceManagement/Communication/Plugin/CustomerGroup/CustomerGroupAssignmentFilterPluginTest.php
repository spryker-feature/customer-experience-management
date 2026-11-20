<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupAssignmentFilterPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupAssignmentFilterPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CustomerCustomerGroupAssignmentFilterPlugin;
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
 * @group CustomerGroupAssignmentFilterPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupAssignmentFilterPluginTest extends Unit
{
    protected const int ID_NOT_EXISTING = 2147483647;

    protected CustomerExperienceManagementCommunicationTester $tester;

    public function testCustomerPluginReturnsTheLabelOfTheCustomerSuggestion(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();

        // Act
        $customerName = (new CustomerCustomerGroupAssignmentFilterPlugin())->findAssignedEntityName($customerTransfer->getIdCustomerOrFail());

        // Assert
        $this->assertSame(
            sprintf(
                '%s (%s %s, %s)',
                $customerTransfer->getEmail(),
                $customerTransfer->getFirstName(),
                $customerTransfer->getLastName(),
                $customerTransfer->getCustomerReference(),
            ),
            $customerName,
        );
    }

    public function testCompanyPluginReturnsTheLabelOfTheCompanySuggestion(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();

        // Act
        $companyName = (new CompanyCustomerGroupAssignmentFilterPlugin())->findAssignedEntityName($companyTransfer->getIdCompanyOrFail());

        // Assert
        $this->assertSame(
            sprintf('%s (id: %s)', $companyTransfer->getNameOrFail(), $companyTransfer->getIdCompanyOrFail()),
            $companyName,
        );
    }

    public function testCompanyBusinessUnitPluginReturnsTheLabelOfTheBusinessUnitSuggestionWithItsCompany(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompanyOrFail(),
        ]);

        // Act
        $companyBusinessUnitName = (new CompanyBusinessUnitCustomerGroupAssignmentFilterPlugin())
            ->findAssignedEntityName($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        // Assert
        $this->assertSame(
            sprintf(
                '%s (id: %s) | %s',
                $companyBusinessUnitTransfer->getNameOrFail(),
                $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                $companyTransfer->getNameOrFail(),
            ),
            $companyBusinessUnitName,
        );
    }

    /**
     * @dataProvider provideCustomerGroupAssignmentFilterPlugins
     */
    public function testFindAssignedEntityNameReturnsNullForAnUnknownId(CustomerGroupAssignmentFilterPluginInterface $customerGroupAssignmentFilterPlugin): void
    {
        // Act
        $name = $customerGroupAssignmentFilterPlugin->findAssignedEntityName(static::ID_NOT_EXISTING);

        // Assert
        $this->assertNull($name);
    }

    public function testCustomerPluginAddsTheIdToTheCustomerIds(): void
    {
        // Act
        $customerGroupConditionsTransfer = (new CustomerCustomerGroupAssignmentFilterPlugin())
            ->expandCustomerGroupConditions(static::ID_NOT_EXISTING, new CustomerGroupConditionsTransfer());

        // Assert
        $this->assertSame([static::ID_NOT_EXISTING], $customerGroupConditionsTransfer->getCustomerIds());
    }

    public function testCompanyPluginAddsTheIdToTheCompanyIds(): void
    {
        // Act
        $customerGroupConditionsTransfer = (new CompanyCustomerGroupAssignmentFilterPlugin())
            ->expandCustomerGroupConditions(static::ID_NOT_EXISTING, new CustomerGroupConditionsTransfer());

        // Assert
        $this->assertSame([static::ID_NOT_EXISTING], $customerGroupConditionsTransfer->getCompanyIds());
    }

    public function testCompanyBusinessUnitPluginAddsTheIdToTheCompanyBusinessUnitIds(): void
    {
        // Act
        $customerGroupConditionsTransfer = (new CompanyBusinessUnitCustomerGroupAssignmentFilterPlugin())
            ->expandCustomerGroupConditions(static::ID_NOT_EXISTING, new CustomerGroupConditionsTransfer());

        // Assert
        $this->assertSame([static::ID_NOT_EXISTING], $customerGroupConditionsTransfer->getCompanyBusinessUnitIds());
    }

    public function testCompanyBusinessUnitPluginReturnsTheBusinessUnitLabel(): void
    {
        // Act
        $label = (new CompanyBusinessUnitCustomerGroupAssignmentFilterPlugin())->getLabel();

        // Assert
        $this->assertSame('Business Unit', $label);
    }

    /**
     * @return array<string, array<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface>>
     */
    public static function provideCustomerGroupAssignmentFilterPlugins(): array
    {
        return [
            'customer' => [new CustomerCustomerGroupAssignmentFilterPlugin()],
            'company' => [new CompanyCustomerGroupAssignmentFilterPlugin()],
            'company business unit' => [new CompanyBusinessUnitCustomerGroupAssignmentFilterPlugin()],
        ];
    }
}
