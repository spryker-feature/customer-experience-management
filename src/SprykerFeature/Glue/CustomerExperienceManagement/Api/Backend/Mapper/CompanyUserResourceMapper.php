<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyUsersBackendResource;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

class CompanyUserResourceMapper implements CompanyUserResourceMapperInterface
{
    protected const string FIELD_UUID = 'uuid';

    protected const string FIELD_IS_ACTIVE = 'isActive';

    protected const string FIELD_IS_DEFAULT = 'isDefault';

    protected const string FIELD_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FIELD_COMPANY_UUID = 'companyUuid';

    protected const string FIELD_COMPANY_BUSINESS_UNIT_UUID = 'companyBusinessUnitUuid';

    protected const string FIELD_COMPANY_ROLE_UUIDS = 'companyRoleUuids';

    protected const string FIELD_CUSTOMER = 'customer';

    /**
     * @var array<int, string>
     */
    protected const array CUSTOMER_SUMMARY_FIELDS = [
        'email',
        'salutation',
        'firstName',
        'lastName',
        'gender',
        'dateOfBirth',
        'phone',
    ];

    /**
     * @return array<string, mixed>
     */
    public function mapCompanyUserTransferToResourceData(CompanyUserTransfer $companyUserTransfer): array
    {
        $customerTransfer = $companyUserTransfer->getCustomer();

        $companyRoleUuids = [];

        foreach ($companyUserTransfer->getCompanyRoleCollection()?->getRoles() ?? [] as $companyRoleTransfer) {
            $companyRoleUuids[] = (string)$companyRoleTransfer->getUuid();
        }

        return [
            static::FIELD_UUID => $companyUserTransfer->getUuid(),
            static::FIELD_IS_ACTIVE => $companyUserTransfer->getIsActive(),
            static::FIELD_IS_DEFAULT => $companyUserTransfer->getIsDefault(),
            static::FIELD_CUSTOMER_REFERENCE => $customerTransfer?->getCustomerReference(),
            static::FIELD_COMPANY_UUID => $companyUserTransfer->getCompany()?->getUuid(),
            static::FIELD_COMPANY_BUSINESS_UNIT_UUID => $companyUserTransfer->getCompanyBusinessUnit()?->getUuid(),
            static::FIELD_COMPANY_ROLE_UUIDS => $companyRoleUuids,
            static::FIELD_CUSTOMER => $customerTransfer === null
                ? []
                : array_intersect_key(
                    $customerTransfer->toArray(false, true),
                    array_flip(static::CUSTOMER_SUMMARY_FIELDS),
                ),
        ];
    }

    public function mapResourceToCustomerTransfer(
        CompanyUsersBackendResource $companyUsersBackendResource,
        CustomerTransfer $customerTransfer
    ): CustomerTransfer {
        $customer = $companyUsersBackendResource->customer;

        if ($customer === null) {
            return $customerTransfer;
        }

        return $customerTransfer
            ->setSalutation($customer->salutation ?? $customerTransfer->getSalutation())
            ->setFirstName($customer->firstName ?? $customerTransfer->getFirstName())
            ->setLastName($customer->lastName ?? $customerTransfer->getLastName())
            ->setGender($customer->gender ?? $customerTransfer->getGender())
            ->setDateOfBirth($customer->dateOfBirth ?? $customerTransfer->getDateOfBirth())
            ->setPhone($customer->phone ?? $customerTransfer->getPhone());
    }
}
