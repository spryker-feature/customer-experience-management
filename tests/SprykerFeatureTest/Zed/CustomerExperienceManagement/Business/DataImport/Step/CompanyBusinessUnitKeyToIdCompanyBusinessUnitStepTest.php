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
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CompanyBusinessUnitKeyToIdCompanyBusinessUnitStep;

/**
 * The key is unique only where a module declares the unique key, so these tests replace the lookup to return
 * what a database without it can return.
 *
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Business
 * @group DataImport
 * @group Step
 * @group CompanyBusinessUnitKeyToIdCompanyBusinessUnitStepTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitKeyToIdCompanyBusinessUnitStepTest extends Unit
{
    protected const int ID_FIRST_COMPANY_BUSINESS_UNIT = 3;

    protected const int ID_SECOND_COMPANY_BUSINESS_UNIT = 7;

    protected const string COMPANY_BUSINESS_UNIT_KEY = 'acme-berlin';

    protected const string EXPECTED_ERROR_MESSAGE = 'Business unit key "acme-berlin" matches more than one business unit.';

    public function testExecuteSetsTheIdOfTheOnlyBusinessUnitWithTheKey(): void
    {
        // Arrange
        $companyBusinessUnitKeyToIdCompanyBusinessUnitStepMock = $this->createCompanyBusinessUnitKeyToIdCompanyBusinessUnitStepMock();
        $companyBusinessUnitKeyToIdCompanyBusinessUnitStepMock->method('findCompanyBusinessUnitIdsByKey')
            ->willReturn([static::ID_FIRST_COMPANY_BUSINESS_UNIT]);
        $dataSet = $this->createDataSet();

        // Act
        $companyBusinessUnitKeyToIdCompanyBusinessUnitStepMock->execute($dataSet);

        // Assert
        $this->assertSame(static::ID_FIRST_COMPANY_BUSINESS_UNIT, $dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT]);
    }

    public function testExecuteRejectsAKeyThatMatchesSeveralBusinessUnits(): void
    {
        // Arrange
        $companyBusinessUnitKeyToIdCompanyBusinessUnitStepMock = $this->createCompanyBusinessUnitKeyToIdCompanyBusinessUnitStepMock();
        $companyBusinessUnitKeyToIdCompanyBusinessUnitStepMock->method('findCompanyBusinessUnitIdsByKey')
            ->willReturn([static::ID_FIRST_COMPANY_BUSINESS_UNIT, static::ID_SECOND_COMPANY_BUSINESS_UNIT]);

        // Expect
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage(static::EXPECTED_ERROR_MESSAGE);

        // Act
        $companyBusinessUnitKeyToIdCompanyBusinessUnitStepMock->execute($this->createDataSet());
    }

    protected function createCompanyBusinessUnitKeyToIdCompanyBusinessUnitStepMock(): CompanyBusinessUnitKeyToIdCompanyBusinessUnitStep&MockObject
    {
        return $this->getMockBuilder(CompanyBusinessUnitKeyToIdCompanyBusinessUnitStep::class)
            ->onlyMethods(['findCompanyBusinessUnitIdsByKey'])
            ->getMock();
    }

    protected function createDataSet(): DataSet
    {
        return new DataSet([CustomerGroupMembershipDataSetInterface::COLUMN_BUSINESS_UNIT_KEY => static::COMPANY_BUSINESS_UNIT_KEY]);
    }
}
