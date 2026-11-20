<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Generated\Shared\Transfer\QueryJoinTransfer;
use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyBusinessUnitTableMap;
use Orm\Zed\CustomerExperienceManagement\Persistence\Map\SpyCustomerGroupToCompanyTableMap;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupQueryExpanderPluginInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupQueryExpanderPlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupQueryExpanderPlugin;

/**
 * Checks the joins the plugins add. The Pyz suite runs them against the database through the customer group facade.
 *
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Plugin
 * @group CustomerGroup
 * @group CustomerGroupQueryExpanderPluginTest
 * Add your own group annotations below this line
 */
class CustomerGroupQueryExpanderPluginTest extends Unit
{
    protected const int ID_FIRST = 11;

    protected const int ID_SECOND = 12;

    protected const string UUID_FIRST = '4b1e6a02-9f37-5c48-b6d1-2e8a70c9f4a5';

    protected const string UUID_SECOND = '9c2d1f04-3a6b-5e17-8d40-6b1f2a7c3e90';

    protected const string EXISTING_JOIN_COLUMN = 'spy_existing.fk_customer_group';

    public function testCompanyPluginAddsNoJoinWithoutCompanyConditions(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(new CompanyCustomerGroupQueryExpanderPlugin(), new CustomerGroupConditionsTransfer());

        // Assert
        $this->assertCount(0, $queryJoinCollectionTransfer->getQueryJoins());
    }

    public function testCompanyPluginAddsNoJoinWithoutConditionsTransfer(): void
    {
        // Act
        $queryJoinCollectionTransfer = (new CompanyCustomerGroupQueryExpanderPlugin())
            ->expand(new CustomerGroupCriteriaTransfer(), new QueryJoinCollectionTransfer());

        // Assert
        $this->assertCount(0, $queryJoinCollectionTransfer->getQueryJoins());
    }

    public function testCompanyPluginIgnoresBusinessUnitConditions(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())
                ->addIdCompanyBusinessUnit(static::ID_FIRST)
                ->addCompanyBusinessUnitUuid(static::UUID_FIRST),
        );

        // Assert
        $this->assertCount(0, $queryJoinCollectionTransfer->getQueryJoins());
    }

    public function testCompanyPluginJoinsTheCompanyMembershipsNarrowedToTheCompanyIds(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())->addIdCompany(static::ID_FIRST)->addIdCompany(static::ID_SECOND),
        );

        // Assert
        $this->assertCount(1, $queryJoinCollectionTransfer->getQueryJoins());
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(0),
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP,
            [SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY => [static::ID_FIRST, static::ID_SECOND]],
        );
    }

    public function testCompanyPluginJoinsTheCompaniesNarrowedToTheCompanyUuids(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())->addCompanyUuid(static::UUID_FIRST)->addCompanyUuid(static::UUID_SECOND),
        );

        // Assert
        $this->assertCount(2, $queryJoinCollectionTransfer->getQueryJoins());
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(0),
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP,
            [],
        );
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(1),
            SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY,
            SpyCompanyTableMap::COL_ID_COMPANY,
            [SpyCompanyTableMap::COL_UUID => [static::UUID_FIRST, static::UUID_SECOND]],
        );
    }

    public function testCompanyPluginNarrowsByTheCompanyIdsAndUuidsTogether(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())->addIdCompany(static::ID_FIRST)->addCompanyUuid(static::UUID_FIRST),
        );

        // Assert
        $this->assertCount(2, $queryJoinCollectionTransfer->getQueryJoins());
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(0),
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP,
            [SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY => [static::ID_FIRST]],
        );
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(1),
            SpyCustomerGroupToCompanyTableMap::COL_FK_COMPANY,
            SpyCompanyTableMap::COL_ID_COMPANY,
            [SpyCompanyTableMap::COL_UUID => [static::UUID_FIRST]],
        );
    }

    public function testBusinessUnitPluginAddsNoJoinWithoutBusinessUnitConditions(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyBusinessUnitCustomerGroupQueryExpanderPlugin(),
            new CustomerGroupConditionsTransfer(),
        );

        // Assert
        $this->assertCount(0, $queryJoinCollectionTransfer->getQueryJoins());
    }

    public function testBusinessUnitPluginIgnoresCompanyConditions(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyBusinessUnitCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())->addIdCompany(static::ID_FIRST)->addCompanyUuid(static::UUID_FIRST),
        );

        // Assert
        $this->assertCount(0, $queryJoinCollectionTransfer->getQueryJoins());
    }

    public function testBusinessUnitPluginJoinsTheBusinessUnitMembershipsNarrowedToTheBusinessUnitIds(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyBusinessUnitCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())->addIdCompanyBusinessUnit(static::ID_FIRST),
        );

        // Assert
        $this->assertCount(1, $queryJoinCollectionTransfer->getQueryJoins());
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(0),
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP,
            [SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT => [static::ID_FIRST]],
        );
    }

    public function testBusinessUnitPluginJoinsTheBusinessUnitsNarrowedToTheBusinessUnitUuids(): void
    {
        // Act
        $queryJoinCollectionTransfer = $this->expand(
            new CompanyBusinessUnitCustomerGroupQueryExpanderPlugin(),
            (new CustomerGroupConditionsTransfer())->addCompanyBusinessUnitUuid(static::UUID_FIRST),
        );

        // Assert
        $this->assertCount(2, $queryJoinCollectionTransfer->getQueryJoins());
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(0),
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP,
            [],
        );
        $this->assertInnerJoin(
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(1),
            SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_COMPANY_BUSINESS_UNIT,
            SpyCompanyBusinessUnitTableMap::COL_ID_COMPANY_BUSINESS_UNIT,
            [SpyCompanyBusinessUnitTableMap::COL_UUID => [static::UUID_FIRST]],
        );
    }

    public function testPluginsKeepTheJoinsAddedBefore(): void
    {
        // Arrange
        $existingQueryJoinTransfer = (new QueryJoinTransfer())->addLeft(static::EXISTING_JOIN_COLUMN);
        $queryJoinCollectionTransfer = (new QueryJoinCollectionTransfer())->addQueryJoin($existingQueryJoinTransfer);
        $customerGroupCriteriaTransfer = (new CustomerGroupCriteriaTransfer())->setCustomerGroupConditions(
            (new CustomerGroupConditionsTransfer())
                ->addIdCompany(static::ID_FIRST)
                ->addIdCompanyBusinessUnit(static::ID_SECOND),
        );

        // Act
        $queryJoinCollectionTransfer = (new CompanyCustomerGroupQueryExpanderPlugin())->expand($customerGroupCriteriaTransfer, $queryJoinCollectionTransfer);
        $queryJoinCollectionTransfer = (new CompanyBusinessUnitCustomerGroupQueryExpanderPlugin())->expand($customerGroupCriteriaTransfer, $queryJoinCollectionTransfer);

        // Assert
        $this->assertCount(3, $queryJoinCollectionTransfer->getQueryJoins());
        $this->assertSame($existingQueryJoinTransfer, $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(0));
        $this->assertSame(
            [SpyCustomerGroupToCompanyTableMap::COL_FK_CUSTOMER_GROUP],
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(1)->getRight(),
        );
        $this->assertSame(
            [SpyCustomerGroupToCompanyBusinessUnitTableMap::COL_FK_CUSTOMER_GROUP],
            $queryJoinCollectionTransfer->getQueryJoins()->offsetGet(2)->getRight(),
        );
    }

    protected function expand(
        CustomerGroupQueryExpanderPluginInterface $customerGroupQueryExpanderPlugin,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): QueryJoinCollectionTransfer {
        return $customerGroupQueryExpanderPlugin->expand(
            (new CustomerGroupCriteriaTransfer())->setCustomerGroupConditions($customerGroupConditionsTransfer),
            new QueryJoinCollectionTransfer(),
        );
    }

    /**
     * @param array<string, list<int|string>> $expectedInConditions
     */
    protected function assertInnerJoin(
        QueryJoinTransfer $queryJoinTransfer,
        string $expectedLeftColumn,
        string $expectedRightColumn,
        array $expectedInConditions
    ): void {
        $this->assertSame([$expectedLeftColumn], $queryJoinTransfer->getLeft());
        $this->assertSame([$expectedRightColumn], $queryJoinTransfer->getRight());
        $this->assertSame(Criteria::INNER_JOIN, $queryJoinTransfer->getJoinType());

        $inConditions = [];

        foreach ($queryJoinTransfer->getWhereConditions() as $queryWhereConditionTransfer) {
            $this->assertSame(Criteria::IN, $queryWhereConditionTransfer->getComparison());
            $inConditions[$queryWhereConditionTransfer->getColumnOrFail()] = $queryWhereConditionTransfer->getValues();
        }

        $this->assertSame($expectedInConditions, $inConditions);
    }
}
