<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSet;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CustomerGroupNameToIdCustomerGroupStep;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Business
 * @group DataImport
 * @group Step
 * @group CustomerGroupNameToIdCustomerGroupStepTest
 * Add your own group annotations below this line
 */
class CustomerGroupNameToIdCustomerGroupStepTest extends Unit
{
    protected const int ID_CUSTOMER_GROUP_TITLE_CASE = 5;

    protected const int ID_CUSTOMER_GROUP_LOWER_CASE = 9;

    protected const string NAME_TITLE_CASE = 'Tier A';

    protected const string NAME_LOWER_CASE = 'tier a';

    protected const string NAME_UPPER_CASE = 'TIER A';

    protected const string EXPECTED_ERROR_MESSAGE = 'Customer group name "TIER A" matches more than one customer group.';

    public function testExecuteUsesTheCustomerGroupWithExactlyThatNameWithoutComparingIgnoringCase(): void
    {
        // Arrange
        $customerGroupNameToIdCustomerGroupStepMock = $this->createCustomerGroupNameToIdCustomerGroupStepMock();
        $customerGroupNameToIdCustomerGroupStepMock->method('findIdCustomerGroupByExactName')
            ->willReturn(static::ID_CUSTOMER_GROUP_LOWER_CASE);
        $customerGroupNameToIdCustomerGroupStepMock->expects($this->never())->method('findCustomerGroupIdsByNameIgnoringCase');
        $dataSet = $this->createDataSet(static::NAME_LOWER_CASE);

        // Act
        $customerGroupNameToIdCustomerGroupStepMock->execute($dataSet);

        // Assert
        $this->assertSame(static::ID_CUSTOMER_GROUP_LOWER_CASE, $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER_GROUP]);
    }

    public function testExecuteUsesTheOnlyCustomerGroupWhoseNameMatchesIgnoringCase(): void
    {
        // Arrange
        $customerGroupNameToIdCustomerGroupStepMock = $this->createCustomerGroupNameToIdCustomerGroupStepMock();
        $customerGroupNameToIdCustomerGroupStepMock->method('findIdCustomerGroupByExactName')->willReturn(null);
        $customerGroupNameToIdCustomerGroupStepMock->method('findCustomerGroupIdsByNameIgnoringCase')
            ->willReturn([static::ID_CUSTOMER_GROUP_TITLE_CASE]);
        $dataSet = $this->createDataSet(static::NAME_UPPER_CASE);

        // Act
        $customerGroupNameToIdCustomerGroupStepMock->execute($dataSet);

        // Assert
        $this->assertSame(static::ID_CUSTOMER_GROUP_TITLE_CASE, $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER_GROUP]);
    }

    public function testExecuteRejectsANameThatMatchesSeveralCustomerGroupsOnlyIgnoringCase(): void
    {
        // Arrange
        $customerGroupNameToIdCustomerGroupStepMock = $this->createCustomerGroupNameToIdCustomerGroupStepMock();
        $customerGroupNameToIdCustomerGroupStepMock->method('findIdCustomerGroupByExactName')->willReturn(null);
        $customerGroupNameToIdCustomerGroupStepMock->method('findCustomerGroupIdsByNameIgnoringCase')
            ->willReturn([static::ID_CUSTOMER_GROUP_TITLE_CASE, static::ID_CUSTOMER_GROUP_LOWER_CASE]);

        // Expect
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage(static::EXPECTED_ERROR_MESSAGE);

        // Act
        $customerGroupNameToIdCustomerGroupStepMock->execute($this->createDataSet(static::NAME_UPPER_CASE));
    }

    protected function createCustomerGroupNameToIdCustomerGroupStepMock(): CustomerGroupNameToIdCustomerGroupStep&MockObject
    {
        return $this->getMockBuilder(CustomerGroupNameToIdCustomerGroupStep::class)
            ->onlyMethods(['findIdCustomerGroupByExactName', 'findCustomerGroupIdsByNameIgnoringCase'])
            ->getMock();
    }

    protected function createDataSet(string $customerGroupName): DataSet
    {
        return new DataSet([CustomerGroupMembershipDataSetInterface::COLUMN_CUSTOMER_GROUP_NAME => $customerGroupName]);
    }
}
