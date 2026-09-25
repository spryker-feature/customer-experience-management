<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomerAccessBackendResource;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerAccess\Business\CustomerAccessFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerAccessResourceMapperInterface;

class CustomerAccessBackendProvider extends AbstractBackendProvider
{
    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected CustomerAccessFacadeInterface $customerAccessFacade,
        protected CustomerAccessResourceMapperInterface $customerAccessResourceMapper,
    ) {
    }

    protected function provideItem(): ?object
    {
        /** @var \Generated\Api\Backend\CustomerAccessBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerAccessResourceMapper->mapCustomerAccessTransferToResourceData(
                $this->customerAccessFacade->getAllContentTypes(),
            ),
            CustomerAccessBackendResource::class,
        );

        return $resource;
    }
}
