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
            ->setEmail($resource->email ?? $customerTransfer->getEmail())
            ->setSalutation($resource->salutation ?? $customerTransfer->getSalutation())
            ->setFirstName($resource->firstName ?? $customerTransfer->getFirstName())
            ->setLastName($resource->lastName ?? $customerTransfer->getLastName())
            ->setGender($resource->gender ?? $customerTransfer->getGender())
            ->setDateOfBirth($resource->dateOfBirth ?? $customerTransfer->getDateOfBirth())
            ->setPhone($resource->phone ?? $customerTransfer->getPhone())
            ->setCompany($resource->company ?? $customerTransfer->getCompany())
            ->setStoreName($resource->storeName ?? $customerTransfer->getStoreName())
            ->setSendPasswordToken($resource->sendPasswordToken ?? $customerTransfer->getSendPasswordToken())
            ->setSkipSendingRegistrationToken(
                $resource->skipSendingRegistrationToken ?? $customerTransfer->getSkipSendingRegistrationToken(),
            );

        if ($resource->localeName !== null && $resource->localeName !== '') {
            $customerTransfer->setLocale((new LocaleTransfer())->setLocaleName($resource->localeName));
        }

        return $customerTransfer;
    }
}
