<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CustomerAccessBackendResource;
use Generated\Shared\Transfer\ContentTypeAccessTransfer;
use Generated\Shared\Transfer\CustomerAccessTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerAccess\Business\CustomerAccessFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerAccessBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerAccessResourceMapperInterface;

class CustomerAccessBackendProcessor extends AbstractBackendProcessor
{
    public function __construct(
        protected CustomerAccessFacadeInterface $customerAccessFacade,
        protected SerializerServiceInterface $serializer,
        protected CustomerAccessResourceMapperInterface $customerAccessResourceMapper,
        protected CustomerAccessBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPatch(mixed $data): object
    {
        /** @var \Generated\Api\Backend\CustomerAccessBackendResource $resource */
        $resource = $data;

        $isRestrictedByContentType = $this->applyRequestedChanges(
            $this->readCurrentState(),
            $resource,
        );

        $this->customerAccessFacade->updateUnauthenticatedCustomerAccess(
            $this->buildRestrictedCustomerAccessTransfer($isRestrictedByContentType),
        );

        /** @var \Generated\Api\Backend\CustomerAccessBackendResource $updatedResource */
        $updatedResource = $this->serializer->denormalize(
            $this->customerAccessResourceMapper->mapCustomerAccessTransferToResourceData(
                $this->customerAccessFacade->getAllContentTypes(),
            ),
            CustomerAccessBackendResource::class,
        );

        return $updatedResource;
    }

    /**
     * @return array<string, bool>
     */
    protected function readCurrentState(): array
    {
        $isRestrictedByContentType = [];

        foreach ($this->customerAccessFacade->getAllContentTypes()->getContentTypeAccess() as $contentTypeAccessTransfer) {
            $isRestrictedByContentType[(string)$contentTypeAccessTransfer->getContentType()]
                = (bool)$contentTypeAccessTransfer->getIsRestricted();
        }

        return $isRestrictedByContentType;
    }

    /**
     * @param array<string, bool> $isRestrictedByContentType
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<string, bool>
     */
    protected function applyRequestedChanges(
        array $isRestrictedByContentType,
        CustomerAccessBackendResource $resource
    ): array {
        $seenContentTypes = [];

        foreach ($resource->contentTypeAccess as $contentTypeAccess) {
            $contentType = (string)$contentTypeAccess->contentType;

            if (isset($seenContentTypes[$contentType])) {
                throw $this->exceptionFactory->createDuplicateContentTypeException($contentType);
            }

            if (!array_key_exists($contentType, $isRestrictedByContentType)) {
                throw $this->exceptionFactory->createUnknownContentTypeException(
                    $contentType,
                    array_keys($isRestrictedByContentType),
                );
            }

            $seenContentTypes[$contentType] = true;
            $isRestrictedByContentType[$contentType] = (bool)$contentTypeAccess->isRestricted;
        }

        return $isRestrictedByContentType;
    }

    /**
     * @param array<string, bool> $isRestrictedByContentType
     */
    protected function buildRestrictedCustomerAccessTransfer(array $isRestrictedByContentType): CustomerAccessTransfer
    {
        $customerAccessTransfer = new CustomerAccessTransfer();

        foreach ($isRestrictedByContentType as $contentType => $isRestricted) {
            if (!$isRestricted) {
                continue;
            }

            $customerAccessTransfer->addContentTypeAccess(
                (new ContentTypeAccessTransfer())
                    ->setContentType($contentType)
                    ->setIsRestricted(true),
            );
        }

        return $customerAccessTransfer;
    }
}
