<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomerAccess\CustomerAccessContentTypeAccessesBackendObject;
use Generated\Api\Backend\CustomerAccessBackendResource;
use Generated\Shared\Transfer\ContentTypeAccessTransfer;
use Generated\Shared\Transfer\CustomerAccessTransfer;
use Spryker\Zed\CustomerAccess\Business\CustomerAccessFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CustomerAccessBackendProcessor;
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
 * @group CustomerAccessBackendProcessorTest
 * Add your own group annotations below this line
 */
class CustomerAccessBackendProcessorTest extends BackendApiTestCase
{
    protected const string CONTENT_TYPE_PRICE = 'price';

    protected const string CONTENT_TYPE_ADD_TO_CART = 'add_to_cart';

    protected const string CONTENT_TYPE_UNKNOWN = 'nope';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPatchPersistsOnlyTheRestrictedContentTypes(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            [static::CONTENT_TYPE_PRICE => false, static::CONTENT_TYPE_ADD_TO_CART => false],
            $capturedCustomerAccessTransfer,
        );

        // Act
        $processor->process(
            $this->createResource([static::CONTENT_TYPE_PRICE => true]),
            $this->tester->getPatchOperation(CustomerAccessBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame(
            [static::CONTENT_TYPE_PRICE],
            $this->extractContentTypes($capturedCustomerAccessTransfer),
            'The facade takes the restricted set; an unrestricted content type is expressed by absence.',
        );
    }

    public function testProcessPatchKeepsTheContentTypesTheRequestDidNotMention(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            [static::CONTENT_TYPE_PRICE => true, static::CONTENT_TYPE_ADD_TO_CART => false],
            $capturedCustomerAccessTransfer,
        );

        // Act
        $processor->process(
            $this->createResource([static::CONTENT_TYPE_ADD_TO_CART => true]),
            $this->tester->getPatchOperation(CustomerAccessBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [static::CONTENT_TYPE_PRICE, static::CONTENT_TYPE_ADD_TO_CART],
            $this->extractContentTypes($capturedCustomerAccessTransfer),
            'A partial payload must not reset the content types it leaves out.',
        );
    }

    public function testProcessPatchLiftsARestriction(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            [static::CONTENT_TYPE_PRICE => true, static::CONTENT_TYPE_ADD_TO_CART => true],
            $capturedCustomerAccessTransfer,
        );

        // Act
        $processor->process(
            $this->createResource([static::CONTENT_TYPE_PRICE => false]),
            $this->tester->getPatchOperation(CustomerAccessBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame(
            [static::CONTENT_TYPE_ADD_TO_CART],
            $this->extractContentTypes($capturedCustomerAccessTransfer),
        );
    }

    public function testProcessPatchReturnsTheStateReadBackFromTheFacade(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            [static::CONTENT_TYPE_PRICE => true, static::CONTENT_TYPE_ADD_TO_CART => false],
            $capturedCustomerAccessTransfer,
        );

        // Act
        $resource = $processor->process(
            $this->createResource([static::CONTENT_TYPE_PRICE => true]),
            $this->tester->getPatchOperation(CustomerAccessBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomerAccessBackendResource::class, $resource);
        $this->assertSame(
            [
                ['contentType' => static::CONTENT_TYPE_PRICE, 'isRestricted' => true],
                ['contentType' => static::CONTENT_TYPE_ADD_TO_CART, 'isRestricted' => false],
            ],
            $resource->contentTypeAccess,
        );
    }

    public function testProcessPatchRejectsAnUnknownContentType(): void
    {
        // Arrange
        $processor = $this->createProcessor([static::CONTENT_TYPE_PRICE => false], $capturedCustomerAccessTransfer);

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->createResource([static::CONTENT_TYPE_UNKNOWN => true]),
                $this->tester->getPatchOperation(CustomerAccessBackendResource::class),
                [],
                $this->tester->getContext()->toArray(),
            ),
        );

        $this->assertNull($capturedCustomerAccessTransfer, 'A rejected request must save nothing.');
    }

    public function testProcessPatchRejectsTheSameContentTypeTwice(): void
    {
        // Arrange
        $processor = $this->createProcessor([static::CONTENT_TYPE_PRICE => false], $capturedCustomerAccessTransfer);

        $resource = new CustomerAccessBackendResource();
        $resource->contentTypeAccess = [
            $this->createContentTypeAccessObject(static::CONTENT_TYPE_PRICE, true),
            $this->createContentTypeAccessObject(static::CONTENT_TYPE_PRICE, false),
        ];

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $resource,
                $this->tester->getPatchOperation(CustomerAccessBackendResource::class),
                [],
                $this->tester->getContext()->toArray(),
            ),
        );

        $this->assertNull($capturedCustomerAccessTransfer, 'A rejected request must save nothing.');
    }

    /**
     * @param array<string, bool> $currentIsRestrictedByContentType
     */
    protected function createProcessor(
        array $currentIsRestrictedByContentType,
        ?CustomerAccessTransfer &$capturedCustomerAccessTransfer = null
    ): CustomerAccessBackendProcessor {
        $capturedCustomerAccessTransfer = null;

        $this->tester->setService(
            CustomerAccessFacadeInterface::class,
            $this->tester->createClientStub(CustomerAccessFacadeInterface::class, [
                'getAllContentTypes' => $this->createCustomerAccessTransfer($currentIsRestrictedByContentType),
                'updateUnauthenticatedCustomerAccess' => function (
                    CustomerAccessTransfer $customerAccessTransfer
                ) use (&$capturedCustomerAccessTransfer): CustomerAccessTransfer {
                    $capturedCustomerAccessTransfer = $customerAccessTransfer;

                    return $customerAccessTransfer;
                },
            ]),
        );

        return $this->tester->getProcessor(CustomerAccessBackendProcessor::class);
    }

    /**
     * @param array<string, bool> $isRestrictedByContentType
     */
    protected function createResource(array $isRestrictedByContentType): CustomerAccessBackendResource
    {
        $resource = new CustomerAccessBackendResource();

        foreach ($isRestrictedByContentType as $contentType => $isRestricted) {
            $resource->contentTypeAccess[] = $this->createContentTypeAccessObject($contentType, $isRestricted);
        }

        return $resource;
    }

    protected function createContentTypeAccessObject(
        string $contentType,
        bool $isRestricted
    ): CustomerAccessContentTypeAccessesBackendObject {
        $contentTypeAccessObject = new CustomerAccessContentTypeAccessesBackendObject();
        $contentTypeAccessObject->contentType = $contentType;
        $contentTypeAccessObject->isRestricted = $isRestricted;

        return $contentTypeAccessObject;
    }

    /**
     * @param array<string, bool> $isRestrictedByContentType
     */
    protected function createCustomerAccessTransfer(array $isRestrictedByContentType): CustomerAccessTransfer
    {
        $customerAccessTransfer = new CustomerAccessTransfer();

        foreach ($isRestrictedByContentType as $contentType => $isRestricted) {
            $customerAccessTransfer->addContentTypeAccess(
                (new ContentTypeAccessTransfer())
                    ->setContentType($contentType)
                    ->setIsRestricted($isRestricted),
            );
        }

        return $customerAccessTransfer;
    }

    /**
     * @return array<int, string>
     */
    protected function extractContentTypes(?CustomerAccessTransfer $customerAccessTransfer): array
    {
        $contentTypes = [];

        foreach ($customerAccessTransfer?->getContentTypeAccess() ?? [] as $contentTypeAccessTransfer) {
            $contentTypes[] = (string)$contentTypeAccessTransfer->getContentType();
        }

        return $contentTypes;
    }
}
