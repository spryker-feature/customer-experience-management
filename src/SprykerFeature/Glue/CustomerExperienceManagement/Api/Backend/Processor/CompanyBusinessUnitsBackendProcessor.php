<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyBusinessUnitResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyBusinessUnitReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CompanyBusinessUnitsBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    public function __construct(
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected SerializerServiceInterface $serializer,
        protected CompanyBusinessUnitReaderInterface $companyBusinessUnitReader,
        protected CompanyBusinessUnitResourceMapperInterface $companyBusinessUnitResourceMapper,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        $companyBusinessUnitTransfer = $this->companyBusinessUnitResourceMapper->mapResourceToCompanyBusinessUnitTransfer(
            $data,
            new CompanyBusinessUnitTransfer(),
        );

        $companyBusinessUnitResponseTransfer = $this->companyBusinessUnitFacade->create($companyBusinessUnitTransfer);

        if (!$companyBusinessUnitResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyBusinessUnitResponse(
                $companyBusinessUnitResponseTransfer,
            );
        }

        return $this->buildResource(
            $this->companyBusinessUnitReader->getCompanyBusinessUnitByUuid(
                $companyBusinessUnitResponseTransfer->getCompanyBusinessUnitTransferOrFail()->getUuidOrFail(),
            ),
        );
    }

    protected function processPatch(mixed $data): object
    {
        $companyBusinessUnitTransfer = $this->companyBusinessUnitResourceMapper->mapResourceToCompanyBusinessUnitTransfer(
            $data,
            $this->companyBusinessUnitReader->getCompanyBusinessUnitByUuid($this->getUuidUriVariable()),
        );

        $companyBusinessUnitResponseTransfer = $this->companyBusinessUnitFacade->update($companyBusinessUnitTransfer);

        if (!$companyBusinessUnitResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyBusinessUnitResponse(
                $companyBusinessUnitResponseTransfer,
            );
        }

        return $this->buildResource(
            $this->companyBusinessUnitReader->getCompanyBusinessUnitByUuid(
                $companyBusinessUnitResponseTransfer->getCompanyBusinessUnitTransferOrFail()->getUuidOrFail(),
            ),
        );
    }

    protected function processDelete(): null
    {
        $companyBusinessUnitTransfer = $this->companyBusinessUnitReader->getCompanyBusinessUnitByUuid(
            $this->getUuidUriVariable(),
        );

        $companyBusinessUnitResponseTransfer = $this->companyBusinessUnitFacade->delete($companyBusinessUnitTransfer);

        if (!$companyBusinessUnitResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyBusinessUnitResponse(
                $companyBusinessUnitResponseTransfer,
            );
        }

        return null;
    }

    protected function buildResource(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): CompanyBusinessUnitsBackendResource {
        /** @var \Generated\Api\Backend\CompanyBusinessUnitsBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyBusinessUnitResourceMapper->mapCompanyBusinessUnitTransferToResourceData(
                $companyBusinessUnitTransfer,
            ),
            CompanyBusinessUnitsBackendResource::class,
        );

        return $resource;
    }
}
