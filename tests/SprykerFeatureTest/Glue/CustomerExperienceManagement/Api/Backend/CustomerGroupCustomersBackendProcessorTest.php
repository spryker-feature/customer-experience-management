<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomerGroupsCustomersBackendResource;
use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CustomerGroupCustomersBackendProcessor;
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
 * @group CustomerGroupCustomersBackendProcessorTest
 * Add your own group annotations below this line
 */
class CustomerGroupCustomersBackendProcessorTest extends BackendApiTestCase
{
    protected const string UUID = '4b1e6a02-9f37-5c48-b6d1-2e8a70c9f4a5';

    protected const int ID_CUSTOMER_GROUP = 11;

    protected const int ID_CUSTOMER = 7;

    protected const string CUSTOMER_REFERENCE = 'DE--1';

    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostAddsWithoutMarkingTheAssignmentAsAReplacement(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['updateCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $result = $processor->process(
            $this->tester->getResource(CustomerGroupsCustomersBackendResource::class, [
                'customerReferences' => [static::CUSTOMER_REFERENCE],
            ]),
            $this->tester->getPostOperation(CustomerGroupsCustomersBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);

        $customerGroupTransfer = $capturedRequest?->getCustomerGroups()->offsetGet(0);
        $this->assertTrue($customerGroupTransfer->getHasAssignmentChange());
        $this->assertNotTrue(
            $customerGroupTransfer->getIsAssignmentReplacement(),
            'POST on the customers sub-resource only adds; it must never clear the customers it was not told about.',
        );
        $this->assertSame([static::CUSTOMER_REFERENCE], $customerGroupTransfer->getCustomerReferences());
    }

    public function testProcessPatchReplacesTheWholeAssignment(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['updateCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $result = $processor->process(
            $this->tester->getResource(CustomerGroupsCustomersBackendResource::class, [
                'customerReferences' => [static::CUSTOMER_REFERENCE],
            ]),
            $this->tester->getPatchOperation(CustomerGroupsCustomersBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);

        $customerGroupTransfer = $capturedRequest?->getCustomerGroups()->offsetGet(0);
        $this->assertTrue($customerGroupTransfer->getHasAssignmentChange());
        $this->assertTrue(
            $customerGroupTransfer->getIsAssignmentReplacement(),
            'PATCH sets the whole member set, so members the payload omits must be removed.',
        );
        $this->assertSame([static::CUSTOMER_REFERENCE], $customerGroupTransfer->getCustomerReferences());
    }

    public function testProcessPatchWithAnEmptyListClearsTheGroup(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['updateCustomerGroupCollection' => $this->captureRequestInto($capturedRequest)],
        );

        // Act
        $result = $processor->process(
            $this->tester->getResource(CustomerGroupsCustomersBackendResource::class, [
                'customerReferences' => [],
            ]),
            $this->tester->getPatchOperation(CustomerGroupsCustomersBackendResource::class),
            [CustomerGroupTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);

        $customerGroupTransfer = $capturedRequest?->getCustomerGroups()->offsetGet(0);
        $this->assertTrue($customerGroupTransfer->getIsAssignmentReplacement());
        $this->assertSame([], $customerGroupTransfer->getCustomerReferences());
    }

    public function testProcessPostSurfacesAnUnknownCustomerReferenceAsUnprocessableEntity(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'updateCustomerGroupCollection' => (new CustomerGroupCollectionResponseTransfer())
                ->addError(
                    (new ErrorTransfer())
                        ->setMessage('message.customer_group.validation.customer_not_found')
                        ->setEntityIdentifier('DE--unknown'),
                ),
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->tester->getResource(CustomerGroupsCustomersBackendResource::class, [
                    'customerReferences' => ['DE--unknown'],
                ]),
                $this->tester->getPostOperation(CustomerGroupsCustomersBackendResource::class),
                [CustomerGroupTransfer::UUID => static::UUID],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProcessDeleteUnassignsOnlyTheAddressedCustomer(): void
    {
        // Arrange
        $capturedCustomerGroupTransfer = null;
        $processor = $this->createProcessor([
            'getCustomerCollectionByCustomerGroupCriteria' => (new CustomerCollectionTransfer())
                ->addCustomer(
                    (new CustomerTransfer())
                        ->setIdCustomer(static::ID_CUSTOMER)
                        ->setCustomerReference(static::CUSTOMER_REFERENCE),
                ),
            'removeCustomersFromGroup' => function (CustomerGroupTransfer $customerGroupTransfer) use (&$capturedCustomerGroupTransfer): void {
                $capturedCustomerGroupTransfer = $customerGroupTransfer;
            },
        ]);

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(CustomerGroupsCustomersBackendResource::class),
            [
                CustomerGroupTransfer::UUID => static::UUID,
                static::URI_VARIABLE_CUSTOMER_REFERENCE => static::CUSTOMER_REFERENCE,
            ],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);

        $customerGroupToCustomerAssignmentTransfer = $capturedCustomerGroupTransfer?->getCustomerAssignmentOrFail();
        $this->assertSame([static::ID_CUSTOMER], $customerGroupToCustomerAssignmentTransfer->getIdsCustomerToDeAssign());
        $this->assertSame(
            [],
            $customerGroupToCustomerAssignmentTransfer->getIdsCustomerToAssign(),
            'Removing a member must not assign anybody.',
        );
    }

    public function testProcessDeleteRespondsNotFoundWhenTheCustomerIsNotAMember(): void
    {
        // Arrange
        $isRemoveCalled = false;
        $processor = $this->createProcessor([
            'getCustomerCollectionByCustomerGroupCriteria' => new CustomerCollectionTransfer(),
            'removeCustomersFromGroup' => function () use (&$isRemoveCalled): void {
                $isRemoveCalled = true;
            },
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                null,
                $this->tester->getDeleteOperation(CustomerGroupsCustomersBackendResource::class),
                [
                    CustomerGroupTransfer::UUID => static::UUID,
                    static::URI_VARIABLE_CUSTOMER_REFERENCE => 'DE--not-a-member',
                ],
                $this->tester->getContext()->toArray(),
            ),
        );

        $this->assertFalse(
            $isRemoveCalled,
            'removeCustomersFromGroup() skips a non-member silently, so the lookup must gate the call.',
        );
    }

    protected function captureRequestInto(?CustomerGroupCollectionRequestTransfer &$capturedRequest = null): callable
    {
        return function (
            CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
        ) use (&$capturedRequest): CustomerGroupCollectionResponseTransfer {
            $capturedRequest = $customerGroupCollectionRequestTransfer;

            return new CustomerGroupCollectionResponseTransfer();
        };
    }

    /**
     * @param array<string, mixed> $customerGroupFacadeMethods
     */
    protected function createProcessor(array $customerGroupFacadeMethods): CustomerGroupCustomersBackendProcessor
    {
        $customerGroupFacadeMethods += [
            'getCustomerGroupCollection' => (new CustomerGroupCollectionTransfer())->addGroup(
                (new CustomerGroupTransfer())
                    ->setIdCustomerGroup(static::ID_CUSTOMER_GROUP)
                    ->setUuid(static::UUID)
                    ->setName('Wholesale'),
            ),
        ];

        $this->tester->setService(
            CustomerGroupFacadeInterface::class,
            $this->tester->createClientStub(CustomerGroupFacadeInterface::class, $customerGroupFacadeMethods),
        );

        return $this->tester->getProcessor(CustomerGroupCustomersBackendProcessor::class);
    }
}
