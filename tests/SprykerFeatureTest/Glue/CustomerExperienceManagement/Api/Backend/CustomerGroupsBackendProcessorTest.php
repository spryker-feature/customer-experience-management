<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomerGroupsBackendResource;
use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CustomerGroupsBackendProcessor;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CustomerGroupsBackendProcessorTest
 * Add your own group annotations below this line
 */
class CustomerGroupsBackendProcessorTest extends BackendApiTestCase
{
    protected const string UUID = '4b1e6a02-9f37-5c48-b6d1-2e8a70c9f4a5';

    protected const int ID_CUSTOMER_GROUP = 11;

    protected const string GROUP_NAME = 'Wholesale';

    protected const string CUSTOMER_REFERENCE = 'DE--1';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostCreatesTheGroupThroughTheCollectionFacadeMethod(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['createCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $resource = $processor->process(
            $this->tester->getResource(CustomerGroupsBackendResource::class, ['name' => static::GROUP_NAME]),
            $this->tester->getPostOperation(CustomerGroupsBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomerGroupsBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertTrue(
            $capturedRequest?->getIsTransactional(),
            'A single-resource request must be all-or-nothing.',
        );
        $this->assertSame(static::GROUP_NAME, $capturedRequest?->getCustomerGroups()->offsetGet(0)->getName());
    }

    public function testProcessPostWithoutCustomerReferencesLeavesTheAssignmentAlone(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['createCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CustomerGroupsBackendResource::class, ['name' => static::GROUP_NAME]),
            $this->tester->getPostOperation(CustomerGroupsBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertFalse(
            $capturedRequest?->getCustomerGroups()->offsetGet(0)->getHasAssignmentChange(),
            'An omitted customerReferences must not be read as an empty assignment.',
        );
    }

    public function testProcessPostFlagsAnAssignmentChangeWhenCustomerReferencesAreSent(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['createCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CustomerGroupsBackendResource::class, [
                'name' => static::GROUP_NAME,
                'customerReferences' => [static::CUSTOMER_REFERENCE],
            ]),
            $this->tester->getPostOperation(CustomerGroupsBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $customerGroupTransfer = $capturedRequest?->getCustomerGroups()->offsetGet(0);
        $this->assertTrue($customerGroupTransfer->getHasAssignmentChange());
        $this->assertSame([static::CUSTOMER_REFERENCE], $customerGroupTransfer->getCustomerReferences());
    }

    public function testProcessPostSurfacesAValidationErrorAsUnprocessableEntity(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'createCustomerGroupCollection' => (new CustomerGroupCollectionResponseTransfer())
                ->addError(
                    (new ErrorTransfer())
                        ->setMessage('message.customer_group.validation.name_taken')
                        ->setEntityIdentifier(static::GROUP_NAME),
                ),
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->tester->getResource(CustomerGroupsBackendResource::class, ['name' => static::GROUP_NAME]),
                $this->tester->getPostOperation(CustomerGroupsBackendResource::class),
                [],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProcessPatchUpdatesTheGroupAddressedByTheUriUuid(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['updateCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $resource = $processor->process(
            $this->tester->getResource(CustomerGroupsBackendResource::class, ['name' => 'Renamed']),
            $this->tester->getPatchOperation(CustomerGroupsBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomerGroupsBackendResource::class, $resource);
        $this->assertSame(
            static::ID_CUSTOMER_GROUP,
            $capturedRequest?->getCustomerGroups()->offsetGet(0)->getIdCustomerGroup(),
            'PATCH must target the group the uri uuid resolves to.',
        );
        $this->assertSame('Renamed', $capturedRequest?->getCustomerGroups()->offsetGet(0)->getName());
    }

    public function testProcessPatchIgnoresCustomerReferences(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['updateCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CustomerGroupsBackendResource::class, [
                'name' => static::GROUP_NAME,
                'customerReferences' => [static::CUSTOMER_REFERENCE],
            ]),
            $this->tester->getPatchOperation(CustomerGroupsBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $customerGroupTransfer = $capturedRequest?->getCustomerGroups()->offsetGet(0);
        $this->assertNotTrue(
            $customerGroupTransfer->getHasAssignmentChange(),
            'Membership is managed through the customers sub-resource, so a rename must never touch it.',
        );
        $this->assertNotTrue($customerGroupTransfer->getIsAssignmentReplacement());
        $this->assertSame(static::GROUP_NAME, $customerGroupTransfer->getName());
    }

    public function testProcessPatchRespondsNotFoundForAnUnknownUuid(): void
    {
        // Arrange
        $this->tester->setService(
            CustomerGroupFacadeInterface::class,
            $this->tester->createClientStub(CustomerGroupFacadeInterface::class, [
                'getCustomerGroupCollection' => new CustomerGroupCollectionTransfer(),
            ]),
        );
        $processor = $this->tester->getProcessor(CustomerGroupsBackendProcessor::class);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                $this->tester->getResource(CustomerGroupsBackendResource::class, ['name' => 'Renamed']),
                $this->tester->getPatchOperation(CustomerGroupsBackendResource::class),
                [CustomerGroupTransfer::UUID => static::UUID],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProcessDeleteRemovesTheGroupResolvedFromTheUuid(): void
    {
        // Arrange
        $capturedCustomerGroupTransfer = null;
        $processor = $this->createProcessor([
            'delete' => function (CustomerGroupTransfer $customerGroupTransfer) use (&$capturedCustomerGroupTransfer): void {
                $capturedCustomerGroupTransfer = $customerGroupTransfer;
            },
        ]);

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(CustomerGroupsBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);
        $this->assertSame(static::ID_CUSTOMER_GROUP, $capturedCustomerGroupTransfer?->getIdCustomerGroup());
    }

    protected function captureRequestInto(?CustomerGroupCollectionRequestTransfer &$capturedRequest = null): callable
    {
        return function (
            CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
        ) use (&$capturedRequest): CustomerGroupCollectionResponseTransfer {
            $capturedRequest = $customerGroupCollectionRequestTransfer;

            return (new CustomerGroupCollectionResponseTransfer())
                ->addCustomerGroup($this->createCustomerGroupTransfer());
        };
    }

    /**
     * @param array<string, mixed> $customerGroupFacadeMethods
     */
    protected function createProcessor(array $customerGroupFacadeMethods): CustomerGroupsBackendProcessor
    {
        $customerGroupFacadeMethods += [
            'getCustomerGroupCollection' => (new CustomerGroupCollectionTransfer())
                ->addGroup($this->createCustomerGroupTransfer()),
        ];

        $this->tester->setService(
            CustomerGroupFacadeInterface::class,
            $this->tester->createClientStub(CustomerGroupFacadeInterface::class, $customerGroupFacadeMethods),
        );

        return $this->tester->getProcessor(CustomerGroupsBackendProcessor::class);
    }

    protected function createCustomerGroupTransfer(): CustomerGroupTransfer
    {
        return (new CustomerGroupTransfer())
            ->setIdCustomerGroup(static::ID_CUSTOMER_GROUP)
            ->setUuid(static::UUID)
            ->setName(static::GROUP_NAME);
    }
}
