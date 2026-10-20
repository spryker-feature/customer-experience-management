<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Business\DataImport\Step;

use Codeception\Test\Unit;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSet;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupDataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CustomerGroupWriterStep;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Business
 * @group DataImport
 * @group Step
 * @group CustomerGroupWriterStepTest
 * Add your own group annotations below this line
 */
class CustomerGroupWriterStepTest extends Unit
{
    protected const string NAME_TITLE_CASE = 'Tier A';

    protected const string NAME_LOWER_CASE = 'tier a';

    protected const string NAME_UPPER_CASE = 'TIER A';

    protected const string DESCRIPTION = 'Imported customer group description';

    protected const string EXPECTED_ERROR_MESSAGE = 'Customer group name "TIER A" matches more than one customer group.';

    public function testExecuteRejectsANameThatMatchesSeveralCustomerGroupsOnlyIgnoringCase(): void
    {
        // Arrange
        $customerGroupWriterStepMock = $this->getMockBuilder(CustomerGroupWriterStep::class)
            ->onlyMethods(['findCustomerGroupEntityByExactName', 'findCustomerGroupEntitiesByNameIgnoringCase'])
            ->getMock();
        $customerGroupWriterStepMock->method('findCustomerGroupEntityByExactName')->willReturn(null);
        $customerGroupWriterStepMock->method('findCustomerGroupEntitiesByNameIgnoringCase')
            ->willReturn(new ObjectCollection([
                (new SpyCustomerGroup())->setName(static::NAME_TITLE_CASE),
                (new SpyCustomerGroup())->setName(static::NAME_LOWER_CASE),
            ]));

        // Expect
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage(static::EXPECTED_ERROR_MESSAGE);

        // Act
        $customerGroupWriterStepMock->execute(new DataSet([
            CustomerGroupDataSetInterface::COLUMN_NAME => static::NAME_UPPER_CASE,
            CustomerGroupDataSetInterface::COLUMN_DESCRIPTION => static::DESCRIPTION,
        ]));
    }
}
