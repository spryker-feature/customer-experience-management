<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomersNotesBackendResource;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;
use ReflectionClass;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerNoteResourceMapper;
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
 * @group CustomerNoteResourceMapperTest
 * Add your own group annotations below this line
 */
class CustomerNoteResourceMapperTest extends BackendApiTestCase
{
    protected const string UUID = 'b1f7c3d2-8a41-5c6e-9d70-2e5b8f0a4c31';

    protected const int ID_CUSTOMER_NOTE = 41;

    protected const int ID_USER = 7;

    protected const string MESSAGE = 'Called the customer about invoice 4711.';

    protected const string USERNAME = 'Admin Spryker';

    protected const string CREATED_AT = '2026-08-31 10:06:00.000000';

    protected CustomerExperienceManagementApiTester $tester;

    public function testMapCustomerNoteEntityTransferToResourceDataMapsEveryReadableField(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        // Act
        $resourceData = (new CustomerNoteResourceMapper())->mapCustomerNoteEntityTransferToResourceData(
            $this->createCustomerNoteEntityTransfer(),
            $customerTransfer,
        );

        // Assert
        $this->assertSame(static::UUID, $resourceData['uuid']);
        $this->assertSame($customerTransfer->getCustomerReferenceOrFail(), $resourceData['customerReference']);
        $this->assertSame(static::MESSAGE, $resourceData['message']);
        $this->assertSame(static::USERNAME, $resourceData['username']);
        $this->assertSame(static::CREATED_AT, $resourceData['createdAt']);
    }

    public function testMapCustomerNoteEntityTransferToResourceDataDropsTheUpdateTimestamp(): void
    {
        // Arrange
        $customerNoteEntityTransfer = $this->createCustomerNoteEntityTransfer()->setUpdatedAt(static::CREATED_AT);

        // Act
        $resourceData = (new CustomerNoteResourceMapper())->mapCustomerNoteEntityTransferToResourceData(
            $customerNoteEntityTransfer,
            $this->tester->haveCustomerTransfer(),
        );

        // Assert
        $this->assertArrayNotHasKey(
            SpyCustomerNoteEntityTransfer::UPDATED_AT,
            $resourceData,
            'A note is never modified, so its update timestamp only ever repeats createdAt.',
        );
    }

    public function testMapCustomerNoteEntityTransferToResourceDataNeverExposesTheSurrogateKeys(): void
    {
        // Act
        $resourceData = (new CustomerNoteResourceMapper())->mapCustomerNoteEntityTransferToResourceData(
            $this->createCustomerNoteEntityTransfer(),
            $this->tester->haveCustomerTransfer(),
        );

        // Assert
        foreach ([SpyCustomerNoteEntityTransfer::ID_CUSTOMER_NOTE, SpyCustomerNoteEntityTransfer::FK_CUSTOMER, SpyCustomerNoteEntityTransfer::FK_USER] as $internalField) {
            $this->assertArrayNotHasKey(
                $internalField,
                $resourceData,
                sprintf('The database key "%s" must never reach the API response.', $internalField),
            );
        }
    }

    public function testTheResourceDoesNotDeclareTheNoteDatabaseId(): void
    {
        // Assert
        $reflection = new ReflectionClass(CustomersNotesBackendResource::class);

        $this->assertFalse($reflection->hasProperty(SpyCustomerNoteEntityTransfer::ID_CUSTOMER_NOTE));
        $this->assertFalse($reflection->hasProperty(SpyCustomerNoteEntityTransfer::FK_USER));
    }

    public function testTheResourceDoesNotDeclareAnUpdateTimestamp(): void
    {
        // Assert
        $this->assertFalse(
            (new ReflectionClass(CustomersNotesBackendResource::class))
                ->hasProperty(SpyCustomerNoteEntityTransfer::UPDATED_AT),
            'The schema must not reintroduce a timestamp that can never differ from createdAt.',
        );
    }

    public function testMapResourceToCustomerNoteEntityTransferCopiesTheMessageOnly(): void
    {
        // Arrange: a payload that also carries every read-only attribute.
        $resource = $this->tester->getResource(CustomersNotesBackendResource::class, [
            SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
            SpyCustomerNoteEntityTransfer::USERNAME => 'Someone Else',
            SpyCustomerNoteEntityTransfer::UUID => static::UUID,
            SpyCustomerNoteEntityTransfer::CREATED_AT => static::CREATED_AT,
        ]);

        // Act
        $customerNoteEntityTransfer = (new CustomerNoteResourceMapper())->mapResourceToCustomerNoteEntityTransfer(
            $resource,
            new SpyCustomerNoteEntityTransfer(),
        );

        // Assert
        $this->assertSame(static::MESSAGE, $customerNoteEntityTransfer->getMessage());
        $this->assertNull(
            $customerNoteEntityTransfer->getUsername(),
            'The author is resolved from the access token, so a client-supplied username must be dropped.',
        );
        $this->assertNull($customerNoteEntityTransfer->getUuid());
        $this->assertNull($customerNoteEntityTransfer->getCreatedAt());
    }

    protected function createCustomerNoteEntityTransfer(): SpyCustomerNoteEntityTransfer
    {
        return (new SpyCustomerNoteEntityTransfer())
            ->setIdCustomerNote(static::ID_CUSTOMER_NOTE)
            ->setFkUser(static::ID_USER)
            ->setUuid(static::UUID)
            ->setMessage(static::MESSAGE)
            ->setUsername(static::USERNAME)
            ->setCreatedAt(static::CREATED_AT);
    }
}
