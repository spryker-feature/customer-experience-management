<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CustomerAccessBackendResource;
use Generated\Shared\Transfer\ContentTypeAccessTransfer;
use Generated\Shared\Transfer\CustomerAccessTransfer;
use Spryker\Zed\CustomerAccess\Business\CustomerAccessFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CustomerAccessBackendProvider;
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
 * @group CustomerAccessBackendProviderTest
 * Add your own group annotations below this line
 */
class CustomerAccessBackendProviderTest extends BackendApiTestCase
{
    protected const string CONTENT_TYPE_PRICE = 'price';

    protected const string CONTENT_TYPE_ADD_TO_CART = 'add_to_cart';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideItemReturnsEveryContentTypeWithItsFlag(): void
    {
        // Arrange
        $provider = $this->createProvider(
            $this->createCustomerAccessTransfer([
                static::CONTENT_TYPE_PRICE => true,
                static::CONTENT_TYPE_ADD_TO_CART => false,
            ]),
        );

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomerAccessBackendResource::class),
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
            'Every configured content type is reported on read, restricted or not.',
        );
    }

    public function testProvideItemReportsAnEmptyListWhenNoContentTypeIsConfigured(): void
    {
        // Arrange
        $provider = $this->createProvider(new CustomerAccessTransfer());

        // Act
        $resource = $provider->provide(
            $this->tester->getGetOperation(CustomerAccessBackendResource::class),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertInstanceOf(CustomerAccessBackendResource::class, $resource);
        $this->assertSame([], $resource->contentTypeAccess);
    }

    protected function createProvider(CustomerAccessTransfer $customerAccessTransfer): CustomerAccessBackendProvider
    {
        $this->tester->setService(
            CustomerAccessFacadeInterface::class,
            $this->tester->createClientStub(CustomerAccessFacadeInterface::class, [
                'getAllContentTypes' => $customerAccessTransfer,
            ]),
        );

        return $this->tester->getProvider(CustomerAccessBackendProvider::class);
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
}
