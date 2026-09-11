<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomersNotesBackendResource;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\CustomerNote\Business\CustomerNoteFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CustomerNotesBackendProcessor;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CustomerNotesBackendProcessorTest
 * Add your own group annotations below this line
 */
class CustomerNotesBackendProcessorTest extends BackendApiTestCase
{
    protected const string UNKNOWN_CUSTOMER_REFERENCE = 'unknown-customer-reference';

    protected const string UUID = 'b1f7c3d2-8a41-5c6e-9d70-2e5b8f0a4c31';

    protected const int ID_USER = 7;

    protected const string USER_FIRST_NAME = 'Admin';

    protected const string USER_LAST_NAME = 'Spryker';

    protected const string EXPECTED_AUTHOR = 'Admin Spryker';

    protected const string MESSAGE = 'Called the customer about invoice 4711.';

    protected const string CREATED_AT = '2026-08-31 10:06:00.000000';

    /**
     * @uses \Spryker\Glue\User\Api\Backend\EventSubscriber\UserIdentityRequestSubscriber::ATTRIBUTE_USER_TRANSFER
     */
    protected const string ATTRIBUTE_USER_TRANSFER = 'UserTransfer';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostAttachesTheNoteToTheCustomerFromTheUri(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedNoteEntityTransfer = null;

        $processor = $this->createProcessor($customerTransfer, $capturedNoteEntityTransfer);

        // Act
        $processor->process(
            $this->tester->getResource(CustomersNotesBackendResource::class, [
                SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
            ]),
            $this->tester->getPostOperation(CustomersNotesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithActingUser($this->createActingUser()),
        );

        // Assert
        $this->assertSame(static::MESSAGE, $capturedNoteEntityTransfer->getMessage());
        $this->assertSame(
            $customerTransfer->getIdCustomerOrFail(),
            $capturedNoteEntityTransfer->getFkCustomer(),
            'The note must be attached to the customer named in the URI.',
        );
    }

    public function testProcessPostAttributesTheNoteToTheUserFromTheAccessToken(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedNoteEntityTransfer = null;

        $processor = $this->createProcessor($customerTransfer, $capturedNoteEntityTransfer);

        // Act
        $processor->process(
            $this->tester->getResource(CustomersNotesBackendResource::class, [
                SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
            ]),
            $this->tester->getPostOperation(CustomersNotesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithActingUser($this->createActingUser()),
        );

        // Assert
        $this->assertSame(static::ID_USER, $capturedNoteEntityTransfer->getFkUser());
        $this->assertSame(static::EXPECTED_AUTHOR, $capturedNoteEntityTransfer->getUsername());
    }

    public function testProcessPostIgnoresAnAuthorSuppliedByTheClient(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedNoteEntityTransfer = null;

        $processor = $this->createProcessor($customerTransfer, $capturedNoteEntityTransfer);

        // Act
        $processor->process(
            $this->tester->getResource(CustomersNotesBackendResource::class, [
                SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
                SpyCustomerNoteEntityTransfer::USERNAME => 'Someone Else',
                SpyCustomerNoteEntityTransfer::UUID => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                SpyCustomerNoteEntityTransfer::CREATED_AT => '1999-01-01 00:00:00.000000',
            ]),
            $this->tester->getPostOperation(CustomersNotesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithActingUser($this->createActingUser()),
        );

        // Assert
        $this->assertSame(
            static::EXPECTED_AUTHOR,
            $capturedNoteEntityTransfer->getUsername(),
            'The author comes from the access token; a client-supplied username must be overwritten.',
        );
        $this->assertNull($capturedNoteEntityTransfer->getUuid(), 'The uuid is generated on insert, never accepted.');
        $this->assertNull($capturedNoteEntityTransfer->getCreatedAt());
    }

    public function testProcessPostReturnsTheStoredNoteWithItsGeneratedIdentifier(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedNoteEntityTransfer = null;

        $processor = $this->createProcessor($customerTransfer, $capturedNoteEntityTransfer);

        // Act
        $resource = $processor->process(
            $this->tester->getResource(CustomersNotesBackendResource::class, [
                SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
            ]),
            $this->tester->getPostOperation(CustomersNotesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            $this->createContextWithActingUser($this->createActingUser()),
        );

        // Assert
        $this->assertInstanceOf(CustomersNotesBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertSame(static::CREATED_AT, $resource->createdAt);
        $this->assertSame(static::EXPECTED_AUTHOR, $resource->username);
        $this->assertSame($customerTransfer->getCustomerReferenceOrFail(), $resource->customerReference);
    }

    public function testProcessPostThrowsNotFoundForAnUnknownCustomerWithoutWritingANote(): void
    {
        // Arrange
        $processor = $this->createProcessorWithFacades(
            ['findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false)],
            [
            'addNote' => function (): SpyCustomerNoteEntityTransfer {
                $this->fail('addNote() must not be called for an unknown customer reference.');
            }],
        );

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                $this->tester->getResource(CustomersNotesBackendResource::class, [
                    SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
                ]),
                $this->tester->getPostOperation(CustomersNotesBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE],
                $this->createContextWithActingUser($this->createActingUser()),
            ),
        );
    }

    /**
     * @dataProvider unresolvableActingUserDataProvider
     */
    public function testProcessPostRefusesToWriteAnUnattributedNote(mixed $actingUser): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $processor = $this->createProcessorWithFacades(
            ['findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(true)->setCustomerTransfer($customerTransfer)],
            [
            'addNote' => function (): SpyCustomerNoteEntityTransfer {
                $this->fail('A note must never be written without a resolved author.');
            }],
        );

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNAUTHORIZED,
            fn () => $processor->process(
                $this->tester->getResource(CustomersNotesBackendResource::class, [
                    SpyCustomerNoteEntityTransfer::MESSAGE => static::MESSAGE,
                ]),
                $this->tester->getPostOperation(CustomersNotesBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
                $this->createContextWithActingUser($actingUser),
            ),
        );
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function unresolvableActingUserDataProvider(): array
    {
        return [
            'no acting user on the request' => [null],
            'a value that is not a user transfer' => ['not-a-user'],
        ];
    }

    protected function createProcessor(
        CustomerTransfer $customerTransfer,
        ?SpyCustomerNoteEntityTransfer &$capturedNoteEntityTransfer
    ): CustomerNotesBackendProcessor {
        return $this->createProcessorWithFacades(
            ['findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(true)->setCustomerTransfer($customerTransfer)],
            ['addNote' => $this->captureInto($capturedNoteEntityTransfer)],
        );
    }

    /**
     * @param array<string, mixed> $customerFacadeMethods
     * @param array<string, mixed> $customerNoteFacadeMethods
     */
    protected function createProcessorWithFacades(
        array $customerFacadeMethods,
        array $customerNoteFacadeMethods
    ): CustomerNotesBackendProcessor {
        $this->tester->setService(
            CustomerFacadeInterface::class,
            $this->tester->createClientStub(CustomerFacadeInterface::class, $customerFacadeMethods),
        );
        $this->tester->setService(
            CustomerNoteFacadeInterface::class,
            $this->tester->createClientStub(CustomerNoteFacadeInterface::class, $customerNoteFacadeMethods),
        );

        return $this->tester->getProcessor(CustomerNotesBackendProcessor::class);
    }

    protected function captureInto(?SpyCustomerNoteEntityTransfer &$capturedNoteEntityTransfer): callable
    {
        return function (SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer) use (&$capturedNoteEntityTransfer): SpyCustomerNoteEntityTransfer {
            $capturedNoteEntityTransfer = $customerNoteEntityTransfer;

            return (clone $customerNoteEntityTransfer)
                ->setUuid(static::UUID)
                ->setCreatedAt(static::CREATED_AT)
                ->setUpdatedAt(static::CREATED_AT);
        };
    }

    protected function createActingUser(): UserTransfer
    {
        return (new UserTransfer())
            ->setIdUser(static::ID_USER)
            ->setFirstName(static::USER_FIRST_NAME)
            ->setLastName(static::USER_LAST_NAME);
    }

    /**
     * @return array<string, mixed>
     */
    protected function createContextWithActingUser(mixed $actingUser): array
    {
        $request = new Request();

        if ($actingUser !== null) {
            $request->attributes->set(static::ATTRIBUTE_USER_TRANSFER, $actingUser);
        }

        return $this->tester->getContext(['request' => $request])->toArray();
    }
}
