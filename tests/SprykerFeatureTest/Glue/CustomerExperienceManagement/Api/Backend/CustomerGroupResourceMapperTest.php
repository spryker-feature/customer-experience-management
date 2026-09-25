<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerGroupResourceMapper;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CustomerGroupResourceMapperTest
 * Add your own group annotations below this line
 */
class CustomerGroupResourceMapperTest extends BackendApiTestCase
{
    protected const string UUID = '4b1e6a02-9f37-5c48-b6d1-2e8a70c9f4a5';

    protected const string NAME = 'Wholesale partners';

    protected const string DESCRIPTION = 'Customers eligible for wholesale pricing tiers.';

    protected const string CREATED_AT = '2026-09-10T08:15:00+00:00';

    protected const string CUSTOMER_REFERENCE = 'DE--1';

    protected CustomerExperienceManagementApiTester $tester;

    public function testMapsEveryReadableCustomerGroupField(): void
    {
        // Act
        $resourceData = (new CustomerGroupResourceMapper())->mapCustomerGroupTransferToResourceData(
            (new CustomerGroupTransfer())
                ->setIdCustomerGroup(11)
                ->setUuid(static::UUID)
                ->setName(static::NAME)
                ->setDescription(static::DESCRIPTION)
                ->setCreatedAt(static::CREATED_AT),
        );

        // Assert
        $this->assertSame(
            [
                'uuid' => static::UUID,
                'name' => static::NAME,
                'description' => static::DESCRIPTION,
                'createdAt' => static::CREATED_AT,
            ],
            $resourceData,
            'The internal idCustomerGroup must never reach the resource.',
        );
    }

    public function testMapsMemberIdentificationFieldsOnly(): void
    {
        // Act
        $resourceData = (new CustomerGroupResourceMapper())->mapCustomerTransferToMemberResourceData(
            (new CustomerTransfer())
                ->setIdCustomer(3)
                ->setCustomerReference(static::CUSTOMER_REFERENCE)
                ->setEmail('spencor.hopkin@acme.com')
                ->setFirstName('Spencor')
                ->setLastName('Hopkin'),
        );

        // Assert
        $this->assertSame(
            [
                'customerReference' => static::CUSTOMER_REFERENCE,
                'email' => 'spencor.hopkin@acme.com',
                'firstName' => 'Spencor',
                'lastName' => 'Hopkin',
            ],
            $resourceData,
        );
    }

    public function testDoesNotOverwriteUnsentPropertiesOnUpdate(): void
    {
        // Arrange
        $resource = new CustomerGroupsBackendResource();
        $resource->name = 'Renamed';

        $customerGroupTransfer = (new CustomerGroupTransfer())
            ->setName(static::NAME)
            ->setDescription(static::DESCRIPTION);

        // Act
        $customerGroupTransfer = (new CustomerGroupResourceMapper())
            ->mapResourceToCustomerGroupTransfer($resource, $customerGroupTransfer);

        // Assert
        $this->assertSame('Renamed', $customerGroupTransfer->getName());
        $this->assertSame(
            static::DESCRIPTION,
            $customerGroupTransfer->getDescription(),
            'An omitted property must keep the persisted value, not be nulled.',
        );
    }
}
