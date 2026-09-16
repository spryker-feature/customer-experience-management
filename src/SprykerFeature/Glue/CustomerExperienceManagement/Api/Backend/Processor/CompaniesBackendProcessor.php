<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CompaniesBackendResource;
use Generated\Shared\Transfer\CompanyResponseTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompaniesBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CompaniesBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    public function __construct(
        protected CompanyFacadeInterface $companyFacade,
        protected SerializerServiceInterface $serializer,
        protected CompanyReaderInterface $companyReader,
        protected CompanyResourceMapperInterface $companyResourceMapper,
        protected CompaniesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        $companyTransfer = $this->companyResourceMapper->mapResourceToCompanyTransfer($data, new CompanyTransfer());

        return $this->buildResourceFromResponse($this->companyFacade->create($companyTransfer));
    }

    protected function processPatch(mixed $data): object
    {
        $companyTransfer = $this->companyResourceMapper->mapResourceToCompanyTransfer(
            $data,
            $this->companyReader->getCompanyByUuid($this->getUuidUriVariable()),
        );

        return $this->buildResourceFromResponse($this->companyFacade->update($companyTransfer));
    }

    protected function buildResourceFromResponse(
        CompanyResponseTransfer $companyResponseTransfer
    ): CompaniesBackendResource {
        if (!$companyResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyResponse($companyResponseTransfer);
        }

        /** @var \Generated\Api\Backend\CompaniesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyResourceMapper->mapCompanyTransferToResourceData(
                $companyResponseTransfer->getCompanyTransferOrFail(),
            ),
            CompaniesBackendResource::class,
        );

        return $resource;
    }
}
