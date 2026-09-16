<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\Post;
use Generated\Api\Backend\CompanyUsers\CompanyUsersCustomerBackendObject;
use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\CompanyUserResponseTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ResponseMessageTransfer;
use Spryker\Zed\BusinessOnBehalf\Business\BusinessOnBehalfFacadeInterface;
use Spryker\Zed\CompanyUser\Business\CompanyUserFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CompanyUsersBackendProcessor;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUserReader;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUserReferenceResolver;
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
 * @group CompanyUsersBackendProcessorTest
 * Add your own group annotations below this line
 */
class CompanyUsersBackendProcessorTest extends BackendApiTestCase
{
    protected const string UUID = '85fc827d-0d09-5d9b-a30f-a9beada10372';

    protected const int ID_COMPANY_USER = 42;

    protected const int ID_CUSTOMER = 7;

    protected const string CUSTOMER_EMAIL = 'spencor.hopkin@acme.com';

    protected const string OPERATION_SET_STATUS = 'setCompanyUserStatus';

    protected const string OPERATION_SET_DEFAULT = 'setDefaultCompanyUser';

    protected const string URI_TEMPLATE_SET_STATUS = '/company-users/{uuid}/set-status';

    protected const string URI_TEMPLATE_SET_DEFAULT = '/company-users/{uuid}/set-default';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostCreatesTheCompanyUserThroughTheFacade(): void
    {
        // Arrange
        $capturedCompanyUserTransfer = null;
        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'create' => function (CompanyUserTransfer $companyUserTransfer) use (&$capturedCompanyUserTransfer) {
                    $capturedCompanyUserTransfer = $companyUserTransfer;

                    return $this->createSuccessfulResponse();
                },
            ],
        );

        // Act
        $resource = $processor->process(
            $this->tester->getResource(CompanyUsersBackendResource::class, []),
            $this->tester->getPostOperation(CompanyUsersBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CompanyUsersBackendResource::class, $resource);
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertNotNull($capturedCompanyUserTransfer, 'The facade must receive the resolved company user.');
    }

    public function testProcessPostRejectsARequestThatIdentifiesNoCustomer(): void
    {
        // Arrange
        $processor = $this->createProcessor(resolvedCompanyUserTransfer: new CompanyUserTransfer());

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->tester->getResource(CompanyUsersBackendResource::class, []),
                $this->tester->getPostOperation(CompanyUsersBackendResource::class),
                [],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProcessPostLeavesAnAttachedCustomerUntouched(): void
    {
        // Arrange
        $capturedCompanyUserTransfer = null;

        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'create' => function (CompanyUserTransfer $companyUserTransfer) use (&$capturedCompanyUserTransfer) {
                    $capturedCompanyUserTransfer = $companyUserTransfer;

                    return $this->createSuccessfulResponse();
                },
            ],
        );

        // Act — the resolver attaches an existing customer, and the payload also carries details
        $processor->process(
            $this->tester->getResource(CompanyUsersBackendResource::class, [
                'customer' => $this->tester->getResource(CompanyUsersCustomerBackendObject::class, [
                    'firstName' => 'Overwritten',
                ]),
            ]),
            $this->tester->getPostOperation(CompanyUsersBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull(
            $capturedCompanyUserTransfer->getCustomerOrFail()->getFirstName(),
            'When a customer reference resolves, the customer object must not be written to them.',
        );
    }

    public function testProcessPostSurfacesFacadeRejectionAsUnprocessableEntity(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            companyUserFacadeMethods: ['create' => $this->createFailedResponse('Customer already attached to this business unit.')],
        );

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->tester->getResource(CompanyUsersBackendResource::class, []),
                $this->tester->getPostOperation(CompanyUsersBackendResource::class),
                [],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProcessPatchUpdatesTheExistingCompanyUserRatherThanCreatingAnother(): void
    {
        // Arrange
        $isCreateCalled = false;
        $capturedCompanyUserTransfer = null;

        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'create' => function () use (&$isCreateCalled): CompanyUserResponseTransfer {
                    $isCreateCalled = true;

                    return $this->createSuccessfulResponse();
                },
                'update' => function (CompanyUserTransfer $companyUserTransfer) use (&$capturedCompanyUserTransfer) {
                    $capturedCompanyUserTransfer = $companyUserTransfer;

                    return $this->createSuccessfulResponse();
                },
            ],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CompanyUsersBackendResource::class, []),
            $this->tester->getPatchOperation(CompanyUsersBackendResource::class),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertFalse($isCreateCalled, 'PATCH must update the company user, never create a second one.');
        $this->assertSame(
            static::ID_COMPANY_USER,
            $capturedCompanyUserTransfer->getIdCompanyUser(),
            'PATCH must update the company user addressed by the uri uuid.',
        );
    }

    public function testProcessPatchLeavesTheCustomerUntouched(): void
    {
        // Arrange
        $capturedCompanyUserTransfer = null;

        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'update' => function (CompanyUserTransfer $companyUserTransfer) use (&$capturedCompanyUserTransfer) {
                    $capturedCompanyUserTransfer = $companyUserTransfer;

                    return $this->createSuccessfulResponse();
                },
            ],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CompanyUsersBackendResource::class, [
                'customerReference' => 'DE--999',
                'customer' => $this->tester->getResource(CompanyUsersCustomerBackendObject::class, [
                    'firstName' => 'Overwritten',
                ]),
            ]),
            $this->tester->getPatchOperation(CompanyUsersBackendResource::class),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull(
            $capturedCompanyUserTransfer->getCustomerOrFail()->getFirstName(),
            'Customer details sent to PATCH must be ignored; customers are managed elsewhere.',
        );
        $this->assertSame(
            static::ID_CUSTOMER,
            $capturedCompanyUserTransfer->getFkCustomer(),
            'PATCH must never re-point the company user at another customer.',
        );
    }

    public function testProcessPatchDoesNotResolveTheCustomerReference(): void
    {
        // Arrange
        $isCustomerResolvingEntryPointCalled = false;

        $this->tester->setService(
            CompanyUserReader::class,
            $this->tester->createClientStub(CompanyUserReader::class, [
                'getCompanyUserByUuid' => $this->createCompanyUserTransfer(true),
            ]),
        );
        $this->tester->setService(
            CompanyUserReferenceResolver::class,
            $this->tester->createClientStub(CompanyUserReferenceResolver::class, [
                'resolveReferences' => function (
                    CompanyUsersBackendResource $companyUsersBackendResource,
                    CompanyUserTransfer $incomingCompanyUserTransfer
                ) use (&$isCustomerResolvingEntryPointCalled): CompanyUserTransfer {
                    $isCustomerResolvingEntryPointCalled = true;

                    return $incomingCompanyUserTransfer;
                },
                'resolveCompanyReferences' => fn (
                    CompanyUsersBackendResource $companyUsersBackendResource,
                    CompanyUserTransfer $incomingCompanyUserTransfer
                ): CompanyUserTransfer => $incomingCompanyUserTransfer,
            ]),
        );
        $this->tester->setService(
            CompanyUserFacadeInterface::class,
            $this->tester->createClientStub(CompanyUserFacadeInterface::class, [
                'update' => $this->createSuccessfulResponse(),
            ]),
        );
        $this->tester->setService(
            BusinessOnBehalfFacadeInterface::class,
            $this->tester->createClientStub(BusinessOnBehalfFacadeInterface::class, []),
        );

        $processor = $this->tester->getProcessor(CompanyUsersBackendProcessor::class);

        // Act
        $processor->process(
            $this->tester->getResource(CompanyUsersBackendResource::class, [
                'customerReference' => 'DE--does-not-exist',
            ]),
            $this->tester->getPatchOperation(CompanyUsersBackendResource::class),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertFalse($isCustomerResolvingEntryPointCalled);
    }

    public function testProcessDeleteRemovesTheCompanyUserWithoutAnonymizingTheCustomer(): void
    {
        // Arrange
        $isDeleteCompanyUserCalled = false;
        $isAnonymizingDeleteCalled = false;

        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'deleteCompanyUser' => function () use (&$isDeleteCompanyUserCalled): CompanyUserResponseTransfer {
                    $isDeleteCompanyUserCalled = true;

                    return $this->createSuccessfulResponse();
                },
                'delete' => function () use (&$isAnonymizingDeleteCalled): CompanyUserResponseTransfer {
                    $isAnonymizingDeleteCalled = true;

                    return $this->createSuccessfulResponse();
                },
            ],
        );

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(CompanyUsersBackendResource::class),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);
        $this->assertTrue($isDeleteCompanyUserCalled);
        $this->assertFalse(
            $isAnonymizingDeleteCalled,
            'DELETE must not reach delete(), which would anonymize the customer.',
        );
    }

    public function testSetStatusEnablesADisabledCompanyUser(): void
    {
        // Arrange
        $isEnableCalled = false;

        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'enableCompanyUser' => function () use (&$isEnableCalled): CompanyUserResponseTransfer {
                    $isEnableCalled = true;

                    return $this->createSuccessfulResponse();
                },
            ],
            companyUserTransfer: $this->createCompanyUserTransfer(false),
        );

        // Act
        $processor->process(
            null,
            $this->createSetStatusOperation(),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getAuthenticatedContextWithAttributes([CompanyUserTransfer::IS_ACTIVE => true]),
        );

        // Assert
        $this->assertTrue($isEnableCalled);
    }

    public function testSetStatusToTheCurrentStateIsANoOpInsteadOfAnError(): void
    {
        // Arrange
        $isEnableCalled = false;

        $processor = $this->createProcessor(
            companyUserFacadeMethods: [
                'enableCompanyUser' => function () use (&$isEnableCalled): CompanyUserResponseTransfer {
                    $isEnableCalled = true;

                    return $this->createFailedResponse('should never be reached');
                },
            ],
            companyUserTransfer: $this->createCompanyUserTransfer(true),
        );

        // Act
        $resource = $processor->process(
            null,
            $this->createSetStatusOperation(),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getAuthenticatedContextWithAttributes([CompanyUserTransfer::IS_ACTIVE => true]),
        );

        // Assert
        $this->assertInstanceOf(CompanyUsersBackendResource::class, $resource);
        $this->assertFalse(
            $isEnableCalled,
            'Enabling an already enabled company user must not reach the facade, which reports that as a failure.',
        );
    }

    /**
     * @dataProvider provideInvalidStatusPayloads
     */
    public function testSetStatusRejectsAnInvalidPayload(string $rawContent): void
    {
        // Arrange
        $processor = $this->createProcessor();

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_BAD_REQUEST,
            fn () => $processor->process(
                null,
                $this->createSetStatusOperation(),
                [CompanyUserTransfer::UUID => static::UUID],
                $this->tester->getAuthenticatedContextWithRawContent($rawContent),
            ),
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideInvalidStatusPayloads(): array
    {
        return [
            'missing isActive' => ['{"data":{"attributes":{"active":true}}}'],
            'isActive is a string' => ['{"data":{"attributes":{"isActive":"true"}}}'],
            'empty body' => [''],
            'not json' => ['not-json'],
        ];
    }

    public function testSetDefaultDelegatesToBusinessOnBehalfWithTheHydratedCustomer(): void
    {
        // Arrange
        $capturedCompanyUserTransfer = null;

        $processor = $this->createProcessor(
            businessOnBehalfFacadeMethods: [
                'setDefaultCompanyUser' => function (CompanyUserTransfer $companyUserTransfer) use (&$capturedCompanyUserTransfer) {
                    $capturedCompanyUserTransfer = $companyUserTransfer;

                    return (new CompanyUserResponseTransfer())->setCompanyUser($companyUserTransfer);
                },
            ],
        );

        // Act
        $processor->process(
            null,
            $this->createSetDefaultOperation(),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getAuthenticatedContextWithAttributes([CompanyUserTransfer::IS_DEFAULT => true]),
        );

        // Assert
        $this->assertNotNull(
            $capturedCompanyUserTransfer?->getCustomer(),
            'The entity manager behind setDefaultCompanyUser() requires the hydrated customer.',
        );
    }

    public function testSetDefaultFailsWhenNoCompanyUserComesBack(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            businessOnBehalfFacadeMethods: ['setDefaultCompanyUser' => new CompanyUserResponseTransfer()],
        );

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                null,
                $this->createSetDefaultOperation(),
                [CompanyUserTransfer::UUID => static::UUID],
                $this->tester->getAuthenticatedContextWithAttributes([CompanyUserTransfer::IS_DEFAULT => true]),
            ),
        );
    }

    public function testSetDefaultUnsetsThroughTheCustomerScopedUnset(): void
    {
        // Arrange
        $capturedCustomerTransfer = null;

        $processor = $this->createProcessor(
            businessOnBehalfFacadeMethods: [
                'unsetDefaultCompanyUserByCustomer' => function (CustomerTransfer $customerTransfer) use (&$capturedCustomerTransfer) {
                    $capturedCustomerTransfer = $customerTransfer;

                    return $customerTransfer;
                },
            ],
            companyUserTransfer: $this->createCompanyUserTransfer(true, true),
        );

        // Act
        $resource = $processor->process(
            null,
            $this->createSetDefaultOperation(),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getAuthenticatedContextWithAttributes([CompanyUserTransfer::IS_DEFAULT => false]),
        );

        // Assert
        $this->assertInstanceOf(CompanyUsersBackendResource::class, $resource);
        $this->assertSame(
            static::ID_CUSTOMER,
            $capturedCustomerTransfer?->getIdCustomer(),
            'The unset is scoped to the addressed company user\'s own customer.',
        );
    }

    public function testSetDefaultToTheCurrentStateIsANoOpInsteadOfAnError(): void
    {
        // Arrange
        $isUnsetCalled = false;

        $processor = $this->createProcessor(
            businessOnBehalfFacadeMethods: [
                'unsetDefaultCompanyUserByCustomer' => function (CustomerTransfer $customerTransfer) use (&$isUnsetCalled): CustomerTransfer {
                    $isUnsetCalled = true;

                    return $customerTransfer;
                },
            ],
        );

        // Act
        $resource = $processor->process(
            null,
            $this->createSetDefaultOperation(),
            [CompanyUserTransfer::UUID => static::UUID],
            $this->tester->getAuthenticatedContextWithAttributes([CompanyUserTransfer::IS_DEFAULT => false]),
        );

        // Assert
        $this->assertInstanceOf(CompanyUsersBackendResource::class, $resource);
        $this->assertFalse($isUnsetCalled);
    }

    /**
     * @dataProvider provideInvalidDefaultPayloads
     */
    public function testSetDefaultRejectsAnInvalidPayload(string $rawContent): void
    {
        // Arrange
        $processor = $this->createProcessor();

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_BAD_REQUEST,
            fn () => $processor->process(
                null,
                $this->createSetDefaultOperation(),
                [CompanyUserTransfer::UUID => static::UUID],
                $this->tester->getAuthenticatedContextWithRawContent($rawContent),
            ),
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideInvalidDefaultPayloads(): array
    {
        return [
            'missing isDefault' => ['{"data":{"attributes":{"default":true}}}'],
            'isDefault is a string' => ['{"data":{"attributes":{"isDefault":"true"}}}'],
            'empty body' => [''],
            'not json' => ['not json'],
        ];
    }

    /**
     * @param array<string, mixed> $companyUserFacadeMethods
     * @param array<string, mixed> $businessOnBehalfFacadeMethods
     */
    protected function createProcessor(
        array $companyUserFacadeMethods = [],
        array $businessOnBehalfFacadeMethods = [],
        ?CompanyUserTransfer $companyUserTransfer = null,
        ?CompanyUserTransfer $resolvedCompanyUserTransfer = null
    ): CompanyUsersBackendProcessor {
        $companyUserTransfer ??= $this->createCompanyUserTransfer(true);

        $this->tester->setService(
            CompanyUserFacadeInterface::class,
            $this->tester->createClientStub(CompanyUserFacadeInterface::class, $companyUserFacadeMethods),
        );
        $this->tester->setService(
            BusinessOnBehalfFacadeInterface::class,
            $this->tester->createClientStub(BusinessOnBehalfFacadeInterface::class, $businessOnBehalfFacadeMethods),
        );
        $this->tester->setService(
            CompanyUserReader::class,
            $this->tester->createClientStub(CompanyUserReader::class, [
                'getCompanyUserByUuid' => $companyUserTransfer,
                'getCompanyUserCollection' => (new CompanyUserCollectionTransfer())->addCompanyUser($companyUserTransfer),
            ]),
        );
        $this->tester->setService(
            CompanyUserReferenceResolver::class,
            $this->tester->createClientStub(CompanyUserReferenceResolver::class, [
                'resolveReferences' => fn (
                    CompanyUsersBackendResource $companyUsersBackendResource,
                    CompanyUserTransfer $incomingCompanyUserTransfer
                ): CompanyUserTransfer => $resolvedCompanyUserTransfer
                    ?? ($incomingCompanyUserTransfer->getCustomer() !== null
                        ? $incomingCompanyUserTransfer
                        : $incomingCompanyUserTransfer->setCustomer($companyUserTransfer->getCustomerOrFail())),
                'resolveCompanyReferences' => fn (
                    CompanyUsersBackendResource $companyUsersBackendResource,
                    CompanyUserTransfer $incomingCompanyUserTransfer
                ): CompanyUserTransfer => $incomingCompanyUserTransfer,
            ]),
        );

        return $this->tester->getProcessor(CompanyUsersBackendProcessor::class);
    }

    protected function createCompanyUserTransfer(bool $isActive, bool $isDefault = false): CompanyUserTransfer
    {
        return (new CompanyUserTransfer())
            ->setIdCompanyUser(static::ID_COMPANY_USER)
            ->setUuid(static::UUID)
            ->setIsActive($isActive)
            ->setIsDefault($isDefault)
            ->setFkCustomer(static::ID_CUSTOMER)
            ->setCustomer((new CustomerTransfer())->setIdCustomer(static::ID_CUSTOMER)->setEmail(static::CUSTOMER_EMAIL));
    }

    protected function createSuccessfulResponse(): CompanyUserResponseTransfer
    {
        return (new CompanyUserResponseTransfer())
            ->setIsSuccessful(true)
            ->setCompanyUser($this->createCompanyUserTransfer(true));
    }

    protected function createFailedResponse(string $message): CompanyUserResponseTransfer
    {
        return (new CompanyUserResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new ResponseMessageTransfer())->setText($message));
    }

    protected function createSetStatusOperation(): Post
    {
        return new Post(
            uriTemplate: static::URI_TEMPLATE_SET_STATUS,
            class: CompanyUsersBackendResource::class,
            name: static::OPERATION_SET_STATUS,
        );
    }

    protected function createSetDefaultOperation(): Post
    {
        return new Post(
            uriTemplate: static::URI_TEMPLATE_SET_DEFAULT,
            class: CompanyUsersBackendResource::class,
            name: static::OPERATION_SET_DEFAULT,
        );
    }
}
