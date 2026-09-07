<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Generated\Api\Backend\CustomersNotesBackendResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CustomerNote\Business\CustomerNoteFacadeInterface;
use Spryker\Zed\CustomerNote\CustomerNoteConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerNoteResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\ActingUserReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerReaderInterface;

class CustomerNotesBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected CustomerNoteFacadeInterface $customerNoteFacade,
        protected SerializerServiceInterface $serializer,
        protected CustomerReaderInterface $customerReader,
        protected ActingUserReaderInterface $actingUserReader,
        protected CustomerNoteResourceMapperInterface $customerNoteResourceMapper,
    ) {
    }

    protected function processPost(mixed $data): object
    {
        return $this->createNote($data);
    }

    protected function createNote(mixed $data): CustomersNotesBackendResource
    {
        $customerTransfer = $this->getCustomerByUriVariable();

        $customerNoteEntityTransfer = $this->buildCustomerNoteEntityTransfer($data)
            ->setFkCustomer($customerTransfer->getIdCustomerOrFail());

        $customerNoteEntityTransfer = $this->attributeToActingUser($customerNoteEntityTransfer);

        return $this->buildResource(
            $this->customerNoteFacade->addNote($customerNoteEntityTransfer),
            $customerTransfer,
        );
    }

    protected function attributeToActingUser(
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer
    ): SpyCustomerNoteEntityTransfer {
        $userTransfer = $this->actingUserReader->getActingUser($this->getRequest());

        return $customerNoteEntityTransfer
            ->setFkUser($userTransfer->getIdUserOrFail())
            ->setUsername($this->formatAuthorName($userTransfer));
    }

    protected function formatAuthorName(UserTransfer $userTransfer): string
    {
        return sprintf(
            CustomerNoteConfig::AUTHOR_NAME_FORMAT,
            $userTransfer->getFirstName(),
            $userTransfer->getLastName(),
        );
    }

    protected function buildCustomerNoteEntityTransfer(mixed $data): SpyCustomerNoteEntityTransfer
    {
        /** @var \Generated\Api\Backend\CustomersNotesBackendResource $data */
        return $this->customerNoteResourceMapper->mapResourceToCustomerNoteEntityTransfer(
            $data,
            new SpyCustomerNoteEntityTransfer(),
        );
    }

    protected function buildResource(
        SpyCustomerNoteEntityTransfer $customerNoteEntityTransfer,
        CustomerTransfer $customerTransfer
    ): CustomersNotesBackendResource {
        /** @var \Generated\Api\Backend\CustomersNotesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->customerNoteResourceMapper->mapCustomerNoteEntityTransferToResourceData(
                $customerNoteEntityTransfer,
                $customerTransfer,
            ),
            CustomersNotesBackendResource::class,
        );

        return $resource;
    }

    protected function getCustomerByUriVariable(): CustomerTransfer
    {
        return $this->customerReader->getCustomerByReference(
            (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE),
        );
    }
}
