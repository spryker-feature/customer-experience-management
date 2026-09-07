<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomersBackendResource;
use Generated\Shared\Transfer\CustomerErrorTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use ReflectionClass;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Shared\Customer\Code\Messages;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerResourceMapper;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CustomersBackendProcessor;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
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
 * @group CustomersBackendProcessorTest
 * Add your own group annotations below this line
 */
class CustomersBackendProcessorTest extends BackendApiTestCase
{
    protected const string UNKNOWN_CUSTOMER_REFERENCE = 'unknown-customer-reference';

    protected const string GLOSSARY_KEY_EMAIL_ALREADY_USED = 'customer.email.already.used';

    protected const string RESOURCE_PROPERTY_PASSWORD = 'password';

    protected const string STORE_NAME = 'DE';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostRegistersCustomerAndReturnsResource(): void
    {
        // Arrange
        $capturedCustomerTransfer = null;
        $processor = $this->createProcessor(
            [
                'registerCustomer' => function (CustomerTransfer $customerTransfer) use (&$capturedCustomerTransfer): CustomerResponseTransfer {
                    $capturedCustomerTransfer = $customerTransfer;

                    return (new CustomerResponseTransfer())->setIsSuccess(true)->setCustomerTransfer($customerTransfer);
                },
            ],
        );

        // Act
        $resource = $processor->process(
            $this->tester->getResource(CustomersBackendResource::class, [
                CustomerTransfer::EMAIL => 'new.customer@example.com',
                CustomerTransfer::FIRST_NAME => 'New',
                CustomerTransfer::LAST_NAME => 'Customer',
                CustomerTransfer::SALUTATION => 'Mr',
            ]),
            $this->tester->getPostOperation(CustomersBackendResource::class),
        );

        // Assert
        $this->assertInstanceOf(CustomersBackendResource::class, $resource);
        $this->assertSame('new.customer@example.com', $capturedCustomerTransfer->getEmail());
        $this->assertSame('New', $capturedCustomerTransfer->getFirstName());
        $this->assertSame(
            'new.customer@example.com',
            $resource->email,
            'The saved customer must be serialized back onto the response resource.',
        );
    }

    public function testProcessPostRejectsAPasswordTokenRequestWithoutAStore(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'registerCustomer' => function (): CustomerResponseTransfer {
                $this->fail('The store must be rejected at the endpoint, before the facade is reached.');
            },
        ]);

        // Act
        try {
            $processor->process(
                $this->tester->getResource(CustomersBackendResource::class, [
                    CustomerTransfer::EMAIL => 'token@example.com',
                    CustomerTransfer::SEND_PASSWORD_TOKEN => true,
                ]),
                $this->tester->getPostOperation(CustomersBackendResource::class),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $glueApiException->getStatusCode());
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_STORE_NAME_REQUIRED,
                $glueApiException->getErrorCode(),
            );
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_STORE_NAME_REQUIRED,
                $glueApiException->getMessage(),
            );
        }
    }

    public function testProcessPostAcceptsAPasswordTokenRequestThatNamesAStore(): void
    {
        // Arrange
        $capturedStoreName = null;
        $capturedSendPasswordToken = null;
        $processor = $this->createProcessor([
            'registerCustomer' => function (CustomerTransfer $customerTransfer) use (
                &$capturedStoreName,
                &$capturedSendPasswordToken,
            ): CustomerResponseTransfer {
                $capturedStoreName = $customerTransfer->getStoreName();
                $capturedSendPasswordToken = $customerTransfer->getSendPasswordToken();

                return (new CustomerResponseTransfer())->setIsSuccess(true)->setCustomerTransfer($customerTransfer);
            },
        ]);

        // Act
        $processor->process(
            $this->tester->getResource(CustomersBackendResource::class, [
                CustomerTransfer::EMAIL => 'token@example.com',
                CustomerTransfer::SEND_PASSWORD_TOKEN => true,
                CustomerTransfer::STORE_NAME => static::STORE_NAME,
            ]),
            $this->tester->getPostOperation(CustomersBackendResource::class),
        );

        // Assert
        $this->assertSame(static::STORE_NAME, $capturedStoreName);
        $this->assertTrue($capturedSendPasswordToken);
    }

    public function testProcessPostSurfacesFacadeErrorsAsUnprocessableEntity(): void
    {
        // Arrange
        $customerResponseTransfer = (new CustomerResponseTransfer())
            ->setIsSuccess(false)
            ->addError((new CustomerErrorTransfer())->setMessage(static::GLOSSARY_KEY_EMAIL_ALREADY_USED));

        $processor = $this->createProcessor(['registerCustomer' => $customerResponseTransfer]);

        // Act
        try {
            $processor->process(
                $this->tester->getResource(CustomersBackendResource::class, [
                    CustomerTransfer::EMAIL => 'taken@example.com',
                ]),
                $this->tester->getPostOperation(CustomersBackendResource::class),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $glueApiException->getStatusCode());
            $this->assertSame(static::GLOSSARY_KEY_EMAIL_ALREADY_USED, $glueApiException->getMessage());
        }
    }

    /**
     * @dataProvider domainErrorToResponseCodeDataProvider
     */
    public function testProcessPostMapsADomainValidationErrorToItsDocumentedResponseCode(
        string $glossaryKey,
        string $expectedResponseCode
    ): void {
        // Arrange
        $processor = $this->createProcessor([
            'registerCustomer' => (new CustomerResponseTransfer())
                ->setIsSuccess(false)
                ->addError((new CustomerErrorTransfer())->setMessage($glossaryKey)),
        ]);

        // Act
        try {
            $processor->process(
                $this->tester->getResource(CustomersBackendResource::class, [
                    CustomerTransfer::EMAIL => 'token@example.com',
                ]),
                $this->tester->getPostOperation(CustomersBackendResource::class),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $glueApiException->getStatusCode());
            $this->assertSame($expectedResponseCode, $glueApiException->getErrorCode());
            $this->assertSame($glossaryKey, $glueApiException->getMessage());
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function domainErrorToResponseCodeDataProvider(): array
    {
        return [
            'store required for a password token' => [
                Messages::CUSTOMER_STORE_REQUIRED_FOR_PASSWORD_TOKEN,
                CustomerExperienceManagementConfig::RESPONSE_CODE_STORE_NAME_REQUIRED,
            ],
            'store does not exist' => [
                'store.validation.unknown_store',
                CustomerExperienceManagementConfig::RESPONSE_CODE_UNKNOWN_STORE,
            ],
            'locale does not exist' => [
                'locale.validation.unknown_locale',
                CustomerExperienceManagementConfig::RESPONSE_CODE_UNKNOWN_LOCALE,
            ],
            'anything else keeps the generic validation code' => [
                'customer.email.already.used',
                CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION,
            ],
        ];
    }

    public function testTheResourceCannotCarryAPassword(): void
    {
        // Arrange
        $resourceReflection = new ReflectionClass(CustomersBackendResource::class);
        $mapperReflection = new ReflectionClass(CustomerResourceMapper::class);

        // Act
        $mapperSource = (string)file_get_contents((string)$mapperReflection->getFileName());

        // Assert
        $this->assertFalse(
            $resourceReflection->hasProperty(static::RESOURCE_PROPERTY_PASSWORD),
            'The customers resource must not expose a password attribute.',
        );
        $this->assertStringNotContainsString(
            'setPassword(',
            $mapperSource,
            'The mapper must never write a password onto the customer transfer.',
        );
    }

    public function testProcessPostAppliesOnlyTheWritableAttributes(): void
    {
        // Arrange
        $capturedCustomerTransfer = null;
        $processor = $this->createProcessor([
            'registerCustomer' => function (CustomerTransfer $customerTransfer) use (&$capturedCustomerTransfer): CustomerResponseTransfer {
                $capturedCustomerTransfer = $customerTransfer;

                return (new CustomerResponseTransfer())->setIsSuccess(true)->setCustomerTransfer($customerTransfer);
            },
        ]);

        // Act
        $processor->process(
            $this->tester->getResource(CustomersBackendResource::class, [
                CustomerTransfer::EMAIL => 'no.password@example.com',
                CustomerTransfer::CUSTOMER_REFERENCE => 'caller-supplied-reference',
                CustomerTransfer::ANONYMIZED_AT => '2026-01-01 00:00:00',
            ]),
            $this->tester->getPostOperation(CustomersBackendResource::class),
        );

        // Assert
        $this->assertSame('no.password@example.com', $capturedCustomerTransfer->getEmail());
        $this->assertNull(
            $capturedCustomerTransfer->getCustomerReference(),
            'A read-only attribute must not be forwarded to the facade.',
        );
        $this->assertNull(
            $capturedCustomerTransfer->getAnonymizedAt(),
            'A read-only attribute must not be forwarded to the facade.',
        );
    }

    public function testProcessPatchAppliesOnlySuppliedFieldsAndKeepsTheRest(): void
    {
        // Arrange
        $existingCustomerTransfer = $this->tester->haveCustomerTransfer();

        $expectedLastName = $existingCustomerTransfer->getLastNameOrFail();
        $expectedEmail = $existingCustomerTransfer->getEmailOrFail();

        $capturedCustomerTransfer = null;
        $processor = $this->createProcessor(
            [
                'findCustomerByReference' => (new CustomerResponseTransfer())
                    ->setHasCustomer(true)
                    ->setCustomerTransfer($existingCustomerTransfer),
                'updateCustomer' => function (CustomerTransfer $customerTransfer) use (&$capturedCustomerTransfer): CustomerResponseTransfer {
                    $capturedCustomerTransfer = $customerTransfer;

                    return (new CustomerResponseTransfer())->setIsSuccess(true)->setCustomerTransfer($customerTransfer);
                },
            ],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CustomersBackendResource::class, [CustomerTransfer::FIRST_NAME => 'Patched']),
            $this->tester->getPatchOperation(CustomersBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $existingCustomerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert
        $this->assertSame('Patched', $capturedCustomerTransfer->getFirstName(), 'The supplied field was not applied.');
        $this->assertSame(
            $expectedLastName,
            $capturedCustomerTransfer->getLastName(),
            'An omitted field must not be blanked.',
        );
        $this->assertSame(
            $expectedEmail,
            $capturedCustomerTransfer->getEmail(),
            'An omitted field must not be blanked.',
        );
    }

    public function testProcessPatchMarksTheCustomerAsEditedInBackoffice(): void
    {
        // Arrange
        $existingCustomerTransfer = $this->tester->haveCustomerTransfer();

        $capturedCustomerTransfer = null;
        $processor = $this->createProcessor(
            [
                'findCustomerByReference' => (new CustomerResponseTransfer())
                    ->setHasCustomer(true)
                    ->setCustomerTransfer($existingCustomerTransfer),
                'updateCustomer' => function (CustomerTransfer $customerTransfer) use (&$capturedCustomerTransfer): CustomerResponseTransfer {
                    $capturedCustomerTransfer = $customerTransfer;

                    return (new CustomerResponseTransfer())->setIsSuccess(true)->setCustomerTransfer($customerTransfer);
                },
            ],
        );

        // Act
        $processor->process(
            $this->tester->getResource(CustomersBackendResource::class, [CustomerTransfer::FIRST_NAME => 'Patched']),
            $this->tester->getPatchOperation(CustomersBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $existingCustomerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert
        $this->assertTrue(
            $capturedCustomerTransfer->getIsEditedInBackoffice(),
            'PATCH must mirror the Back Office edit flow so the pre-update plugin stack is skipped.',
        );
    }

    public function testProcessPatchThrowsNotFoundForUnknownReference(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false)],
        );

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                $this->tester->getResource(CustomersBackendResource::class, [CustomerTransfer::FIRST_NAME => 'Patched']),
                $this->tester->getPatchOperation(CustomersBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE],
            ),
        );
    }

    public function testProcessDeleteAnonymizesTheCustomerAndReturnsNull(): void
    {
        // Arrange
        $existingCustomerTransfer = $this->tester->haveCustomerTransfer();

        $anonymizedCustomerTransfer = null;
        $processor = $this->createProcessor([
            'findCustomerByReference' => (new CustomerResponseTransfer())
                ->setHasCustomer(true)
                ->setCustomerTransfer($existingCustomerTransfer),
            'anonymizeCustomer' => function (CustomerTransfer $customerTransfer) use (&$anonymizedCustomerTransfer): void {
                $anonymizedCustomerTransfer = $customerTransfer;
            },
        ]);

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(CustomersBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $existingCustomerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert
        $this->assertNull($result);
        $this->assertSame(
            $existingCustomerTransfer->getCustomerReferenceOrFail(),
            $anonymizedCustomerTransfer->getCustomerReference(),
        );
        $this->assertSame(
            $existingCustomerTransfer->getIdCustomerOrFail(),
            $anonymizedCustomerTransfer->getIdCustomer(),
            'The facade identifies the customer by id, so the resolved id must be forwarded.',
        );
    }

    public function testProcessDeleteThrowsNotFoundForUnknownReferenceInsteadOfDelegatingToTheFacade(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
            'anonymizeCustomer' => function (): void {
                $this->fail('anonymizeCustomer() must not be called for an unknown customer reference.');
            },
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                null,
                $this->tester->getDeleteOperation(CustomersBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE],
            ),
        );
    }

    /**
     * @param array<string, mixed> $customerFacadeMethods
     */
    protected function createProcessor(array $customerFacadeMethods): CustomersBackendProcessor
    {
        $this->tester->setService(
            CustomerFacadeInterface::class,
            $this->tester->createClientStub(CustomerFacadeInterface::class, $customerFacadeMethods),
        );

        return $this->tester->getProcessor(CustomersBackendProcessor::class);
    }
}
