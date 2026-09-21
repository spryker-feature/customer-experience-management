<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CustomersAddressesBackendResource;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

class CustomerAddressResourceMapper implements CustomerAddressResourceMapperInterface
{
    protected const string FIELD_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FIELD_COUNTRY = 'country';

    protected const string FIELD_IS_DEFAULT_BILLING = 'isDefaultBilling';

    protected const string FIELD_IS_DEFAULT_SHIPPING = 'isDefaultShipping';

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function mapAddressTransferToResourceData(
        AddressTransfer $addressTransfer,
        CustomerTransfer $customerTransfer
    ): array {
        $resourceData = $addressTransfer->toArray(false, true);

        $resourceData[static::FIELD_COUNTRY] = $addressTransfer->getCountry()?->getName();
        $resourceData[static::FIELD_CUSTOMER_REFERENCE] = $customerTransfer->getCustomerReference();

        $resourceData[static::FIELD_IS_DEFAULT_BILLING] = $this->isDefaultAddress(
            $customerTransfer->getDefaultBillingAddress(),
            $addressTransfer->getIdCustomerAddress(),
        );
        $resourceData[static::FIELD_IS_DEFAULT_SHIPPING] = $this->isDefaultAddress(
            $customerTransfer->getDefaultShippingAddress(),
            $addressTransfer->getIdCustomerAddress(),
        );

        return $resourceData;
    }

    public function mapResourceToAddressTransfer(
        CustomersAddressesBackendResource $resource,
        AddressTransfer $addressTransfer
    ): AddressTransfer {
        return $addressTransfer
            ->setSalutation($resource->salutation)
            ->setFirstName($resource->firstName)
            ->setLastName($resource->lastName)
            ->setAddress1($resource->address1)
            ->setAddress2($resource->address2)
            ->setAddress3($resource->address3)
            ->setCompany($resource->company)
            ->setCity($resource->city)
            ->setZipCode($resource->zipCode)
            ->setIso2Code($resource->iso2Code)
            ->setRegion($resource->region)
            ->setPhone($resource->phone)
            ->setComment($resource->comment)
            ->setIsDefaultBilling($resource->isDefaultBilling)
            ->setIsDefaultShipping($resource->isDefaultShipping);
    }

    protected function isDefaultAddress(string|int|null $defaultAddressId, ?int $idCustomerAddress): bool
    {
        if ($defaultAddressId === null || $idCustomerAddress === null) {
            return false;
        }

        return (int)$defaultAddressId === $idCustomerAddress;
    }
}
