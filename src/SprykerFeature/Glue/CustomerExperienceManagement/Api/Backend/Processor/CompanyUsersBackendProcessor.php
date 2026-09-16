<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyUserResponseTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\BusinessOnBehalf\Business\BusinessOnBehalfFacadeInterface;
use Spryker\Zed\CompanyUser\Business\CompanyUserFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyUsersBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyUserResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUserReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyUserReferenceResolverInterface;

class CompanyUsersBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_UUID = 'uuid';

    /**
     * @uses \SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CompanyUsersBackendProvider
     */
    protected const string OPERATION_NAME_SET_STATUS = 'setCompanyUserStatus';

    protected const string OPERATION_NAME_SET_DEFAULT = 'setDefaultCompanyUser';

    protected const string PAYLOAD_FIELD_IS_ACTIVE = 'isActive';

    protected const string PAYLOAD_FIELD_IS_DEFAULT = 'isDefault';

    protected const string PAYLOAD_MEMBER_DATA = 'data';

    protected const string PAYLOAD_MEMBER_ATTRIBUTES = 'attributes';

    public function __construct(
        protected CompanyUserFacadeInterface $companyUserFacade,
        protected BusinessOnBehalfFacadeInterface $businessOnBehalfFacade,
        protected SerializerServiceInterface $serializer,
        protected CompanyUserReaderInterface $companyUserReader,
        protected CompanyUserReferenceResolverInterface $companyUserReferenceResolver,
        protected CompanyUserResourceMapperInterface $companyUserResourceMapper,
        protected CompanyUsersBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        $operationName = $this->getOperation()->getName();

        if ($operationName === static::OPERATION_NAME_SET_STATUS) {
            return $this->setStatus();
        }

        if ($operationName === static::OPERATION_NAME_SET_DEFAULT) {
            return $this->setDefault();
        }

        return $this->createCompanyUser($data);
    }

    protected function processPatch(mixed $data): object
    {
        return $this->updateCompanyUser($data);
    }

    protected function processDelete(): null
    {
        $companyUserTransfer = $this->companyUserReader->getCompanyUserByUuid($this->getUuidUriVariable());

        $this->assertSuccessful(
            $this->companyUserFacade->deleteCompanyUser(
                (new CompanyUserTransfer())->setIdCompanyUser($companyUserTransfer->getIdCompanyUserOrFail()),
            ),
        );

        return null;
    }

    protected function createCompanyUser(mixed $data): CompanyUsersBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyUsersBackendResource $companyUsersBackendResource */
        $companyUsersBackendResource = $data;

        $companyUserTransfer = $this->companyUserReferenceResolver->resolveReferences(
            $companyUsersBackendResource,
            new CompanyUserTransfer(),
        );

        if ($companyUserTransfer->getCustomer() === null) {
            $companyUserTransfer->setCustomer(
                $this->companyUserResourceMapper->mapResourceToCustomerTransfer(
                    $companyUsersBackendResource,
                    $this->buildCustomerForRegistration($companyUsersBackendResource),
                ),
            );
        }

        if ($companyUserTransfer->getCustomerOrFail()->getEmail() === null) {
            throw $this->exceptionFactory->createMissingCustomerException();
        }

        $companyUserResponseTransfer = $this->assertSuccessful(
            $this->companyUserFacade->create($companyUserTransfer),
        );

        return $this->buildResourceByUuid(
            (string)$companyUserResponseTransfer->getCompanyUserOrFail()->getUuid(),
        );
    }

    protected function updateCompanyUser(mixed $data): CompanyUsersBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyUsersBackendResource $companyUsersBackendResource */
        $companyUsersBackendResource = $data;

        $uuid = $this->getUuidUriVariable();
        $companyUserTransfer = $this->companyUserReader->getCompanyUserByUuid($uuid);
        $customerTransfer = $companyUserTransfer->getCustomerOrFail()->setIsEditedInBackoffice(true);

        $companyUserTransfer = $this->companyUserReferenceResolver
            ->resolveCompanyReferences($companyUsersBackendResource, $companyUserTransfer)
            ->setCustomer($customerTransfer)
            ->setFkCustomer($customerTransfer->getIdCustomerOrFail());

        $this->assertSuccessful($this->companyUserFacade->update($companyUserTransfer));

        return $this->buildResourceByUuid($uuid);
    }

    protected function setStatus(): CompanyUsersBackendResource
    {
        $uuid = $this->getUuidUriVariable();
        $companyUserTransfer = $this->companyUserReader->getCompanyUserByUuid($uuid);
        $isActive = $this->readBooleanPayload(
            static::PAYLOAD_FIELD_IS_ACTIVE,
            $this->exceptionFactory->createInvalidStatusPayloadException(),
        );

        if ($companyUserTransfer->getIsActive() === $isActive) {
            return $this->buildResourceByUuid($uuid);
        }

        $statusCompanyUserTransfer = (new CompanyUserTransfer())
            ->setIdCompanyUser($companyUserTransfer->getIdCompanyUserOrFail());

        $this->assertSuccessful(
            $isActive
                ? $this->companyUserFacade->enableCompanyUser($statusCompanyUserTransfer)
                : $this->companyUserFacade->disableCompanyUser($statusCompanyUserTransfer),
        );

        return $this->buildResourceByUuid($uuid);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function setDefault(): CompanyUsersBackendResource
    {
        $uuid = $this->getUuidUriVariable();
        $companyUserTransfer = $this->companyUserReader->getCompanyUserByUuid($uuid);
        $isDefault = $this->readBooleanPayload(
            static::PAYLOAD_FIELD_IS_DEFAULT,
            $this->exceptionFactory->createInvalidDefaultPayloadException(),
        );

        if ($companyUserTransfer->getIsDefault() === $isDefault) {
            return $this->buildResourceByUuid($uuid);
        }

        if (!$isDefault) {
            return $this->unsetDefault($companyUserTransfer, $uuid);
        }

        $companyUserResponseTransfer = $this->businessOnBehalfFacade->setDefaultCompanyUser($companyUserTransfer);

        if ($companyUserResponseTransfer->getCompanyUser() === null) {
            throw $this->exceptionFactory->createCompanyUserValidationException($companyUserResponseTransfer);
        }

        return $this->buildResourceByUuid($uuid);
    }

    protected function unsetDefault(
        CompanyUserTransfer $companyUserTransfer,
        string $uuid
    ): CompanyUsersBackendResource {
        $this->businessOnBehalfFacade->unsetDefaultCompanyUserByCustomer(
            $companyUserTransfer->getCustomerOrFail(),
        );

        return $this->buildResourceByUuid($uuid);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function readBooleanPayload(string $field, GlueApiException $glueApiException): bool
    {
        $payload = json_decode((string)$this->getRequest()->getContent(), true);

        if (!is_array($payload)) {
            throw $glueApiException;
        }

        $attributes = $payload[static::PAYLOAD_MEMBER_DATA][static::PAYLOAD_MEMBER_ATTRIBUTES] ?? $payload;

        if (!is_array($attributes) || !array_key_exists($field, $attributes)) {
            throw $glueApiException;
        }

        $value = $attributes[$field];

        if (!is_bool($value)) {
            throw $glueApiException;
        }

        return $value;
    }

    protected function buildCustomerForRegistration(
        CompanyUsersBackendResource $companyUsersBackendResource
    ): CustomerTransfer {
        return (new CustomerTransfer())
            ->setEmail($companyUsersBackendResource->customer?->email)
            ->setSendPasswordToken($companyUsersBackendResource->customer?->sendPasswordToken);
    }

    protected function buildResourceByUuid(string $uuid): CompanyUsersBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyUsersBackendResource $companyUsersBackendResource */
        $companyUsersBackendResource = $this->serializer->denormalize(
            $this->companyUserResourceMapper->mapCompanyUserTransferToResourceData(
                $this->companyUserReader->getCompanyUserByUuid($uuid),
            ),
            CompanyUsersBackendResource::class,
        );

        return $companyUsersBackendResource;
    }

    protected function assertSuccessful(CompanyUserResponseTransfer $companyUserResponseTransfer): CompanyUserResponseTransfer
    {
        if (!$companyUserResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createCompanyUserValidationException($companyUserResponseTransfer);
        }

        return $companyUserResponseTransfer;
    }

    protected function getUuidUriVariable(): string
    {
        return (string)($this->findUriVariable(static::URI_VARIABLE_UUID)
            ?? $this->getRequest()->attributes->get(static::URI_VARIABLE_UUID));
    }
}
