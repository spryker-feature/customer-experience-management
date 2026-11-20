<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Business\Resolver;

use Codeception\Test\Unit;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Resolver\CompanyBusinessUnitAncestorResolver;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Business
 * @group Resolver
 * @group CompanyBusinessUnitAncestorResolverTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitAncestorResolverTest extends Unit
{
    /**
     * @dataProvider resolveCompanyBusinessUnitIdsWithAncestorsDataProvider
     *
     * @param array<int, int|null> $parentCompanyBusinessUnitIds
     * @param list<int> $expectedCompanyBusinessUnitIds
     */
    public function testResolvesTheBusinessUnitAndItsAncestorsInOrder(
        int $idCompanyBusinessUnit,
        array $parentCompanyBusinessUnitIds,
        array $expectedCompanyBusinessUnitIds
    ): void {
        // Arrange
        $companyBusinessUnitAncestorResolver = new CompanyBusinessUnitAncestorResolver();

        // Act
        $companyBusinessUnitIds = $companyBusinessUnitAncestorResolver
            ->resolveCompanyBusinessUnitIdsWithAncestors($idCompanyBusinessUnit, $parentCompanyBusinessUnitIds);

        // Assert
        $this->assertSame($expectedCompanyBusinessUnitIds, $companyBusinessUnitIds);
    }

    /**
     * @return array<string, array{int, array<int, int|null>, list<int>}>
     */
    public function resolveCompanyBusinessUnitIdsWithAncestorsDataProvider(): array
    {
        return [
            'root unit' => [1, [1 => null, 2 => 1], [1]],
            'three levels, own unit first' => [3, [1 => null, 2 => 1, 3 => 2, 4 => 3], [3, 2, 1]],
            'sibling not included' => [2, [1 => null, 2 => 1, 3 => 1], [2, 1]],
            'parent outside the company map' => [2, [2 => 99, 3 => 2], [2]],
            'own unit outside the company map' => [7, [1 => null], []],
            'cycle' => [1, [1 => 2, 2 => 1], [1, 2]],
            'self reference' => [1, [1 => 1], [1]],
        ];
    }
}
