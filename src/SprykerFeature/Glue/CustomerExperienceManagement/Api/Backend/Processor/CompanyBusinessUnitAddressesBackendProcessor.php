<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CompanyBusinessUnitAddressesBackendResource;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyUnitAddress\Business\CompanyUnitAddressFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyBusinessUnitAddressResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUnitAddressReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CompanyBusinessUnitAddressesBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    public function __construct(
        protected CompanyUnitAddressFacadeInterface $companyUnitAddressFacade,
        protected SerializerServiceInterface $serializer,
        protected CompanyUnitAddressReaderInterface $companyUnitAddressReader,
        protected CompanyBusinessUnitAddressResourceMapperInterface $companyBusinessUnitAddressResourceMapper,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        $companyUnitAddressTransfer = $this->companyBusinessUnitAddressResourceMapper->mapResourceToCompanyUnitAddressTransfer(
            $data,
            new CompanyUnitAddressTransfer(),
        );

        $companyUnitAddressResponseTransfer = $this->companyUnitAddressFacade->create($companyUnitAddressTransfer);

        if (!$companyUnitAddressResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyUnitAddressResponse(
                $companyUnitAddressResponseTransfer,
            );
        }

        return $this->buildResourceFromResponse($companyUnitAddressResponseTransfer->getCompanyUnitAddressTransferOrFail());
    }

    protected function processPatch(mixed $data): object
    {
        $companyUnitAddressTransfer = $this->companyBusinessUnitAddressResourceMapper->mapResourceToCompanyUnitAddressTransfer(
            $data,
            $this->companyUnitAddressReader->getCompanyUnitAddressByUuid($this->getUuidUriVariable()),
        );

        $companyUnitAddressResponseTransfer = $this->companyUnitAddressFacade->update($companyUnitAddressTransfer);

        if (!$companyUnitAddressResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyUnitAddressResponse(
                $companyUnitAddressResponseTransfer,
            );
        }

        return $this->buildResourceFromResponse($companyUnitAddressResponseTransfer->getCompanyUnitAddressTransferOrFail());
    }

    protected function buildResourceFromResponse(
        CompanyUnitAddressTransfer $companyUnitAddressTransfer
    ): CompanyBusinessUnitAddressesBackendResource {
        /** @var \Generated\Api\Backend\CompanyBusinessUnitAddressesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyBusinessUnitAddressResourceMapper->mapCompanyUnitAddressTransferToResourceData(
                $companyUnitAddressTransfer,
            ),
            CompanyBusinessUnitAddressesBackendResource::class,
        );

        return $resource;
    }
}
