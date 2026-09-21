<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CustomersBackendResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\LocaleTransfer;

class CustomerResourceMapper implements CustomerResourceMapperInterface
{
    protected const string FIELD_LOCALE_NAME = 'localeName';

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function mapCustomerTransferToResourceData(CustomerTransfer $customerTransfer): array
    {
        $resourceData = $customerTransfer->toArray(false, true);

        $resourceData[static::FIELD_LOCALE_NAME] = $customerTransfer->getLocale()?->getLocaleName();

        return $resourceData;
    }

    public function mapResourceToCustomerTransfer(
        CustomersBackendResource $resource,
        CustomerTransfer $customerTransfer
    ): CustomerTransfer {
        $customerTransfer
            ->setEmail($resource->email)
            ->setSalutation($resource->salutation)
            ->setFirstName($resource->firstName)
            ->setLastName($resource->lastName)
            ->setGender($resource->gender)
            ->setDateOfBirth($resource->dateOfBirth)
            ->setPhone($resource->phone)
            ->setCompany($resource->company)
            ->setStoreName($resource->storeName)
            ->setSendPasswordToken($resource->sendPasswordToken)
            ->setSkipSendingRegistrationToken($this->resolveSkipSendingRegistrationToken($resource, $customerTransfer));

        if ($resource->localeName !== null && $resource->localeName !== '') {
            $customerTransfer->setLocale((new LocaleTransfer())->setLocaleName($resource->localeName));
        }

        return $customerTransfer;
    }

    protected function resolveSkipSendingRegistrationToken(
        CustomersBackendResource $resource,
        CustomerTransfer $customerTransfer
    ): ?bool {
        if ($resource->sendRegistrationToken === null) {
            return $customerTransfer->getSkipSendingRegistrationToken();
        }

        return !$resource->sendRegistrationToken;
    }
}
