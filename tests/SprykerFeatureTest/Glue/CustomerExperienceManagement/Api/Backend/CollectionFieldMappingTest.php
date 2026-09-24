<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Test\Unit;
use Spryker\Zed\CompanyBusinessUnit\CompanyBusinessUnitConfig;
use Spryker\Zed\CompanyUnitAddress\CompanyUnitAddressConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CollectionFieldMappingTest
 * Add your own group annotations below this line
 */
class CollectionFieldMappingTest extends Unit
{
    /**
     * The providers whitelist requests against these map keys and then index the same map, so an
     * empty map would silently reject every filter rather than reporting a misconfiguration.
     */
    public function testTheBusinessUnitModuleMapsAtLeastOneFieldPerCollection(): void
    {
        // Arrange
        $companyBusinessUnitConfig = new CompanyBusinessUnitConfig();

        // Assert
        $this->assertNotSame([], $companyBusinessUnitConfig->getCompanyBusinessUnitCollectionFilterableFieldMap());
        $this->assertNotSame([], $companyBusinessUnitConfig->getCompanyBusinessUnitCollectionSortableFieldMap());
    }

    public function testTheAddressModuleMapsAtLeastOneFieldPerCollection(): void
    {
        // Arrange
        $companyUnitAddressConfig = new CompanyUnitAddressConfig();

        // Assert
        $this->assertNotSame([], $companyUnitAddressConfig->getCompanyUnitAddressCollectionFilterableFieldMap());
        $this->assertNotSame([], $companyUnitAddressConfig->getCompanyUnitAddressCollectionSortableFieldMap());
    }

    /**
     * The constant decides which reader resolves a submitted uuid, so it must keep naming a filter
     * field the address module maps.
     */
    public function testTheCompanyUuidFilterFieldIsOneTheAddressModuleMaps(): void
    {
        // Arrange
        $filterableFieldMap = (new CompanyUnitAddressConfig())->getCompanyUnitAddressCollectionFilterableFieldMap();

        // Assert
        $this->assertArrayHasKey(
            CustomerExperienceManagementConfig::FILTER_FIELD_COMPANY_UNIT_ADDRESS_COMPANY_UUID,
            $filterableFieldMap,
        );
    }
}
