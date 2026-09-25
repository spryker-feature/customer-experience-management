<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CompanyRolesBackendResource;
use Generated\Shared\Transfer\CompanyRoleResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyRole\Business\CompanyRoleFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyRolesBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyRoleResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyRoleReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\PermissionReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CompanyRolesBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    public function __construct(
        protected CompanyRoleFacadeInterface $companyRoleFacade,
        protected SerializerServiceInterface $serializer,
        protected CompanyRoleReaderInterface $companyRoleReader,
        protected CompanyReaderInterface $companyReader,
        protected PermissionReaderInterface $permissionReader,
        protected CompanyRoleResourceMapperInterface $companyRoleResourceMapper,
        protected CompanyRolesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        /** @var \Generated\Api\Backend\CompanyRolesBackendResource $resource */
        $resource = $data;

        $companyTransfer = $this->companyReader->getCompanyByUuid((string)$resource->companyUuid);

        $companyRoleTransfer = $this->companyRoleResourceMapper
            ->mapResourceToCompanyRoleTransfer($resource, new CompanyRoleTransfer())
            ->setFkCompany($companyTransfer->getIdCompanyOrFail())
            ->setPermissionCollection(
                $this->permissionReader->resolvePermissionCollectionByKeys($resource->permissionKeys ?? []),
            );

        return $this->buildResourceFromResponse($this->companyRoleFacade->createCompanyRole($companyRoleTransfer));
    }

    protected function processPatch(mixed $data): object
    {
        /** @var \Generated\Api\Backend\CompanyRolesBackendResource $resource */
        $resource = $data;

        $companyRoleTransfer = $this->companyRoleReader->getCompanyRoleByUuid($this->getUuidUriVariable());

        $this->assertCompanyIsUnchanged($resource, $companyRoleTransfer);

        $companyRoleTransfer = $this->companyRoleResourceMapper
            ->mapResourceToCompanyRoleTransfer($resource, $companyRoleTransfer)
            ->setPermissionCollection(
                $resource->permissionKeys === null
                    ? $companyRoleTransfer->getPermissionCollection() ?? new PermissionCollectionTransfer()
                    : $this->permissionReader->resolvePermissionCollectionByKeys($resource->permissionKeys),
            );

        return $this->buildResourceFromResponse($this->companyRoleFacade->updateCompanyRole($companyRoleTransfer));
    }

    protected function processDelete(): null
    {
        $companyRoleTransfer = $this->companyRoleReader->getCompanyRoleByUuid($this->getUuidUriVariable());

        $companyRoleResponseTransfer = $this->companyRoleFacade->delete($companyRoleTransfer);

        if (!$companyRoleResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyRoleResponse($companyRoleResponseTransfer);
        }

        return null;
    }

    protected function assertCompanyIsUnchanged(
        CompanyRolesBackendResource $resource,
        CompanyRoleTransfer $companyRoleTransfer
    ): void {
        if ($resource->companyUuid === null) {
            return;
        }

        if ($resource->companyUuid === $companyRoleTransfer->getCompany()?->getUuid()) {
            return;
        }

        throw $this->exceptionFactory->createCompanyImmutableException();
    }

    protected function buildResourceFromResponse(
        CompanyRoleResponseTransfer $companyRoleResponseTransfer
    ): CompanyRolesBackendResource {
        if (!$companyRoleResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createExceptionFromCompanyRoleResponse($companyRoleResponseTransfer);
        }

        $companyRoleTransfer = $this->companyRoleReader->getCompanyRoleById(
            $companyRoleResponseTransfer->getCompanyRoleTransferOrFail()->getIdCompanyRoleOrFail(),
        );

        /** @var \Generated\Api\Backend\CompanyRolesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyRoleResourceMapper->mapCompanyRoleTransferToResourceData($companyRoleTransfer),
            CompanyRolesBackendResource::class,
        );

        return $resource;
    }
}
