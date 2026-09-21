<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomersAddressesBackendResource;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\Transfer\AddressCollectionTransfer;
use Generated\Shared\Transfer\AddressConditionsTransfer;
use Generated\Shared\Transfer\AddressCriteriaTransfer;
use Generated\Shared\Transfer\AddressResponseTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\CustomerErrorTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CustomerAddressesBackendProcessor;
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
 * @group CustomerAddressesBackendProcessorTest
 * Add your own group annotations below this line
 */
class CustomerAddressesBackendProcessorTest extends BackendApiTestCase
{
    protected const string UNKNOWN_CUSTOMER_REFERENCE = 'unknown-customer-reference';

    protected const string UUID = '5caa05f5-41f5-5e6c-a254-07d7887fb4e9';

    protected const int ID_CUSTOMER_ADDRESS = 41;

    protected const int ID_OTHER_CUSTOMER = 99;

    protected const string COUNTRY_NAME = 'Germany';

    protected const string CITY_STORED = 'Berlin';

    protected const string CITY_PATCHED = 'Hamburg';

    protected const string LAST_NAME_STORED = 'Hopkin';

    protected const string ZIP_CODE_STORED = '10115';

    protected const string COUNTRY_NAME_FROM_PAYLOAD = 'Atlantis';

    protected const string ISO_2_CODE = 'DE';

    protected const string ISO_2_CODE_NOT_INSTALLED = 'FR';

    protected const string ERROR_MESSAGE_COUNTRY_UNKNOWN = 'country.validation.unknown_country';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostCreatesTheAddressForTheCustomerFromTheUri(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            ['createAddress' => $this->captureInto($capturedAddressTransfer)],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::CITY => static::CITY_PATCHED,
                AddressTransfer::ZIP_CODE => static::ZIP_CODE_STORED,
                AddressTransfer::ISO2_CODE => 'DE',
            ]),
            $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert
        $this->assertSame(static::CITY_PATCHED, $capturedAddressTransfer->getCity());
        $this->assertSame(
            $customerTransfer->getIdCustomerOrFail(),
            $capturedAddressTransfer->getFkCustomer(),
            'The address must be attached to the customer named in the URI.',
        );
    }

    public function testProcessPostReturnsTheReReadAddressSoTheCountryIsPopulated(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $createdAddressTransfer = $this->createStoredAddressTransfer($customerTransfer)->setCountry(null);

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            ['createAddress' => $createdAddressTransfer],
        ));

        // Act
        $resource = $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::CITY => static::CITY_STORED,
            ]),
            $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert
        $this->assertInstanceOf(CustomersAddressesBackendResource::class, $resource);
        $this->assertSame(
            static::COUNTRY_NAME,
            $resource->country,
            'POST and GET must report the same country for the same address.',
        );
    }

    public function testProcessPostForwardsOnlyTheWritableAttributes(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            ['createAddress' => $this->captureInto($capturedAddressTransfer)],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::CITY => static::CITY_STORED,
                AddressTransfer::UUID => 'caller-supplied-uuid',
                'customerReference' => 'caller-supplied-reference',
                'createdAt' => '2020-01-01 00:00:00',
            ]),
            $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert
        $this->assertNull(
            $capturedAddressTransfer->getUuid(),
            'The uuid is server-generated and must not be settable through the payload.',
        );
        $this->assertNull(
            $capturedAddressTransfer->getCreatedAt(),
            'A read-only attribute must not be forwarded to the facade.',
        );
    }

    public function testProcessPostNeverWritesTheCountryFromThePayload(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            ['createAddress' => $this->captureInto($capturedAddressTransfer)],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::CITY => static::CITY_STORED,
                AddressTransfer::ISO2_CODE => static::ISO_2_CODE,
                'country' => static::COUNTRY_NAME_FROM_PAYLOAD,
            ]),
            $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
            [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
        );

        // Assert: the country name is derived from iso2Code on read, so a name in the payload is ignored.
        $this->assertNull($capturedAddressTransfer->getCountry());
    }

    public function testProcessPostThrowsNotFoundForUnknownCustomerWithoutCreatingAnAddress(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
            'createAddress' => function (): AddressTransfer {
                $this->fail('createAddress() must not be called for an unknown customer reference.');
            },
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                $this->tester->getResource(CustomersAddressesBackendResource::class, [
                    AddressTransfer::CITY => static::CITY_STORED,
                ]),
                $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE],
            ),
        );
    }

    /**
     * On PATCH the read stage hands the processor the provider-built resource with the payload
     * deserialized onto it, so the resource already carries the stored value of every attribute the
     * payload omitted. This test builds that object by hand; the kernel-level tests cover the merge.
     */
    public function testProcessPatchAppliesOnlySuppliedFieldsAndKeepsTheRest(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
            'updateAddressAndCustomerDefaultAddresses' => $this->captureIntoReturningCustomer(
                $capturedAddressTransfer,
                $customerTransfer,
            )],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::LAST_NAME => static::LAST_NAME_STORED,
                AddressTransfer::ZIP_CODE => static::ZIP_CODE_STORED,
                AddressTransfer::CITY => static::CITY_PATCHED,
            ]),
            $this->tester->getPatchOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
        );

        // Assert
        $this->assertSame(static::CITY_PATCHED, $capturedAddressTransfer->getCity(), 'The supplied field was not applied.');
        $this->assertSame(
            static::LAST_NAME_STORED,
            $capturedAddressTransfer->getLastName(),
            'An omitted field must not be blanked.',
        );
        $this->assertSame(
            static::ZIP_CODE_STORED,
            $capturedAddressTransfer->getZipCode(),
            'An omitted field must not be blanked.',
        );
    }

    public function testProcessPatchUsesTheDefaultAddressAwareUpdatePath(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
                'updateAddressAndCustomerDefaultAddresses' => $this->captureIntoReturningCustomer(
                    $capturedAddressTransfer,
                    $customerTransfer,
                ),
                'updateAddress' => function (): AddressTransfer {
                    $this->fail('The plain update path drops the default-address flags and must not be used.');
                },
            ],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::CITY => static::CITY_PATCHED,
            ]),
            $this->tester->getPatchOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
        );

        // Assert
        $this->assertNotNull($capturedAddressTransfer);
    }

    public function testProcessPatchForwardsTheDefaultAddressFlags(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
            'updateAddressAndCustomerDefaultAddresses' => $this->captureIntoReturningCustomer(
                $capturedAddressTransfer,
                $customerTransfer,
            )],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                AddressTransfer::IS_DEFAULT_BILLING => true,
                AddressTransfer::IS_DEFAULT_SHIPPING => true,
            ]),
            $this->tester->getPatchOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
        );

        // Assert
        $this->assertTrue($capturedAddressTransfer->getIsDefaultBilling());
        $this->assertTrue($capturedAddressTransfer->getIsDefaultShipping());
    }

    public function testProcessPatchThrowsNotFoundWhenTheAddressBelongsToAnotherCustomerWithoutUpdating(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $otherCustomerTransfer = $this->createOtherCustomerTransfer();

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($otherCustomerTransfer)],
            [
                'updateAddressAndCustomerDefaultAddresses' => function (): CustomerTransfer {
                    $this->fail('An address of another customer must not be updated.');
                },
            ],
        ));

        // Act
        try {
            $processor->process(
                $this->tester->getResource(CustomersAddressesBackendResource::class, [
                    AddressTransfer::CITY => static::CITY_PATCHED,
                ]),
                $this->tester->getPatchOperation(CustomersAddressesBackendResource::class),
                $this->createItemUriVariables($customerTransfer),
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_NOT_FOUND, $glueApiException->getStatusCode());
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_ADDRESS_NOT_FOUND,
                $glueApiException->getErrorCode(),
            );
        }
    }

    public function testProcessPatchKeepsTheStoredCountryWhenThePayloadCarriesACountryName(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $capturedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
            'updateAddressAndCustomerDefaultAddresses' => $this->captureIntoReturningCustomer(
                $capturedAddressTransfer,
                $customerTransfer,
            )],
        ));

        // Act
        $processor->process(
            $this->tester->getResource(CustomersAddressesBackendResource::class, [
                'country' => static::COUNTRY_NAME_FROM_PAYLOAD,
            ]),
            $this->tester->getPatchOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
        );

        // Assert
        $this->assertInstanceOf(
            CountryTransfer::class,
            $capturedAddressTransfer->getCountry(),
            'The stored CountryTransfer must survive a payload that carries a country name string.',
        );
        $this->assertSame(static::COUNTRY_NAME, $capturedAddressTransfer->getCountry()->getName());
    }

    public function testProcessDeleteDeletesTheResolvedAddressAndReturnsNull(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $deletedAddressTransfer = null;

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
            'deleteAddress' => function (AddressTransfer $addressTransfer) use (&$deletedAddressTransfer): AddressTransfer {
                $deletedAddressTransfer = $addressTransfer;

                return $addressTransfer;
            }],
        ));

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(CustomersAddressesBackendResource::class),
            $this->createItemUriVariables($customerTransfer),
        );

        // Assert
        $this->assertNull($result);
        $this->assertSame(
            static::ID_CUSTOMER_ADDRESS,
            $deletedAddressTransfer->getIdCustomerAddress(),
            'The address resolved from the uuid must be the one handed to the facade.',
        );
    }

    public function testProcessDeleteThrowsNotFoundWhenTheAddressBelongsToAnotherCustomerWithoutDeleting(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();
        $otherCustomerTransfer = $this->createOtherCustomerTransfer();

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($otherCustomerTransfer)],
            [
            'deleteAddress' => function (): AddressTransfer {
                $this->fail('An address of another customer must not be deleted.');
            }],
        ));

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                null,
                $this->tester->getDeleteOperation(CustomersAddressesBackendResource::class),
                $this->createItemUriVariables($customerTransfer),
            ),
        );
    }

    public function testProcessDeleteThrowsNotFoundForUnknownCustomerWithoutDeleting(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'findCustomerByReference' => (new CustomerResponseTransfer())->setHasCustomer(false),
            'deleteAddress' => function (): AddressTransfer {
                $this->fail('deleteAddress() must not be called for an unknown customer reference.');
            },
        ]);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_NOT_FOUND,
            fn () => $processor->process(
                null,
                $this->tester->getDeleteOperation(CustomersAddressesBackendResource::class),
                [
                    CustomerTransfer::CUSTOMER_REFERENCE => static::UNKNOWN_CUSTOMER_REFERENCE,
                    AddressTransfer::UUID => static::UUID,
                ],
            ),
        );
    }

    protected function createOtherCustomerTransfer(): CustomerTransfer
    {
        return $this->tester->haveCustomerTransfer([CustomerTransfer::ID_CUSTOMER => static::ID_OTHER_CUSTOMER]);
    }

    protected function createStoredAddressTransfer(CustomerTransfer $ownerTransfer): AddressTransfer
    {
        /** @var \Generated\Shared\Transfer\AddressTransfer $addressTransfer */
        $addressTransfer = (new AddressBuilder([
            AddressTransfer::UUID => static::UUID,
            AddressTransfer::ID_CUSTOMER_ADDRESS => static::ID_CUSTOMER_ADDRESS,
            AddressTransfer::FK_CUSTOMER => $ownerTransfer->getIdCustomerOrFail(),
            AddressTransfer::CITY => static::CITY_STORED,
            AddressTransfer::LAST_NAME => static::LAST_NAME_STORED,
            AddressTransfer::ZIP_CODE => static::ZIP_CODE_STORED,
        ]))->build();

        return $addressTransfer
            ->setRegion(null)
            ->setCountry((new CountryTransfer())->setName(static::COUNTRY_NAME));
    }

    /**
     * @return array<string, string>
     */
    protected function createItemUriVariables(CustomerTransfer $customerTransfer): array
    {
        return [
            CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail(),
            AddressTransfer::UUID => static::UUID,
        ];
    }

    protected function captureInto(?AddressTransfer &$capturedAddressTransfer): callable
    {
        return function (AddressTransfer $addressTransfer) use (&$capturedAddressTransfer): AddressTransfer {
            $capturedAddressTransfer = $addressTransfer;

            return (clone $addressTransfer)->setUuid(static::UUID);
        };
    }

    protected function captureIntoReturningCustomer(
        ?AddressTransfer &$capturedAddressTransfer,
        CustomerTransfer $customerTransfer
    ): callable {
        return function (AddressTransfer $addressTransfer) use (&$capturedAddressTransfer, $customerTransfer): CustomerTransfer {
            $capturedAddressTransfer = $addressTransfer;

            return $customerTransfer;
        };
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\AddressTransfer> $addressBook
     * @param array<string, mixed> $additionalMethods
     *
     * @return array<string, mixed>
     */
    protected function stubFacade(
        CustomerTransfer $customerTransfer,
        array $addressBook,
        array $additionalMethods = []
    ): array {
        return $additionalMethods + [
            'validateAddress' => fn (AddressTransfer $addressTransfer): AddressResponseTransfer => (new AddressResponseTransfer())->setAddress($addressTransfer)->setIsSuccess(true),
            'findCustomerByReference' => (new CustomerResponseTransfer())
                ->setHasCustomer(true)
                ->setCustomerTransfer($customerTransfer),
            'getAddressCollection' => fn (AddressCriteriaTransfer $addressCriteriaTransfer): AddressCollectionTransfer => $this->queryAddressBook($addressBook, $addressCriteriaTransfer),
        ];
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\AddressTransfer> $addressBook
     */
    protected function queryAddressBook(
        array $addressBook,
        AddressCriteriaTransfer $addressCriteriaTransfer
    ): AddressCollectionTransfer {
        $addressConditionsTransfer = $addressCriteriaTransfer->getAddressConditions();
        $addressCollectionTransfer = new AddressCollectionTransfer();

        foreach ($addressBook as $addressTransfer) {
            if ($this->matchesConditions($addressTransfer, $addressConditionsTransfer)) {
                $addressCollectionTransfer->addAddress($addressTransfer);
            }
        }

        return $addressCollectionTransfer;
    }

    protected function matchesConditions(
        AddressTransfer $addressTransfer,
        ?AddressConditionsTransfer $addressConditionsTransfer
    ): bool {
        if ($addressConditionsTransfer === null) {
            return true;
        }

        $uuids = $addressConditionsTransfer->getUuids();

        if ($uuids !== [] && !in_array($addressTransfer->getUuid(), $uuids, true)) {
            return false;
        }

        $customerIds = $addressConditionsTransfer->getCustomerIds();

        return $customerIds === [] || in_array($addressTransfer->getFkCustomer(), $customerIds, true);
    }

    public function testProcessPostReportsADomainValidationErrorUnderItsOwnCodeWithoutWriting(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
                'validateAddress' => fn (): AddressResponseTransfer => (new AddressResponseTransfer())
                    ->setIsSuccess(false)
                    ->addError((new CustomerErrorTransfer())->setMessage(static::ERROR_MESSAGE_COUNTRY_UNKNOWN)),
                'createAddress' => function (): AddressTransfer {
                    $this->fail('An address rejected by the validator stack must not be written.');
                },
            ],
        ));

        // Act
        try {
            $processor->process(
                $this->tester->getResource(CustomersAddressesBackendResource::class, [
                    AddressTransfer::ISO2_CODE => static::ISO_2_CODE_NOT_INSTALLED,
                ]),
                $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $glueApiException->getStatusCode());
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_UNKNOWN_COUNTRY,
                $glueApiException->getErrorCode(),
            );
        }
    }

    public function testProcessPostFallsBackToTheGenericCodeForAnUnmappedValidationError(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomerTransfer();

        $processor = $this->createProcessor($this->stubFacade(
            $customerTransfer,
            [$this->createStoredAddressTransfer($customerTransfer)],
            [
                'validateAddress' => fn (): AddressResponseTransfer => (new AddressResponseTransfer())
                    ->setIsSuccess(false)
                    ->addError((new CustomerErrorTransfer())->setMessage('some.project.rule')),
                'createAddress' => function (): AddressTransfer {
                    $this->fail('An address rejected by the validator stack must not be written.');
                },
            ],
        ));

        // Act
        try {
            $processor->process(
                $this->tester->getResource(CustomersAddressesBackendResource::class, [
                    AddressTransfer::ISO2_CODE => static::ISO_2_CODE,
                ]),
                $this->tester->getPostOperation(CustomersAddressesBackendResource::class),
                [CustomerTransfer::CUSTOMER_REFERENCE => $customerTransfer->getCustomerReferenceOrFail()],
            );
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_VALIDATION,
                $glueApiException->getErrorCode(),
            );
        }
    }

    /**
     * @param array<string, mixed> $customerFacadeMethods
     */
    protected function createProcessor(array $customerFacadeMethods): CustomerAddressesBackendProcessor
    {
        $this->tester->setService(
            CustomerFacadeInterface::class,
            $this->tester->createClientStub(CustomerFacadeInterface::class, $customerFacadeMethods),
        );

        return $this->tester->getProcessor(CustomerAddressesBackendProcessor::class);
    }
}
