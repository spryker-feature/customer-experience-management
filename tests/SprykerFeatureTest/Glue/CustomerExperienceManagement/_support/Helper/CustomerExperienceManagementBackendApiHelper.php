<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\SpyCustomerNoteEntityTransfer;

class CustomerExperienceManagementBackendApiHelper extends Module
{
    public const string RESOURCE_CUSTOMERS = 'customers';

    public const string RESOURCE_COMPANY_USERS = 'company-users';

    public const string RESOURCE_ADDRESSES = 'addresses';

    public const string RESOURCE_NOTES = 'notes';

    public const string RESOURCE_TYPE_NOTES = 'customer-notes';

    public const string OPERATION_SET_STATUS = 'set-status';

    public const string OPERATION_SET_DEFAULT = 'set-default';

    public const string RESOURCE_COMPANIES = 'companies';

    public const string RESOURCE_COMPANY_BUSINESS_UNITS = 'company-business-units';

    public const string RESOURCE_COMPANY_BUSINESS_UNIT_ADDRESSES = 'company-business-unit-addresses';

    public const string RESOURCE_CUSTOMER_GROUPS = 'customer-groups';

    public const string RESOURCE_CUSTOMER_GROUP_CUSTOMERS = 'customer-group-customers';

    public const string RESOURCE_CUSTOMER_ACCESS = 'customer-access';

    protected const string LISTED_COMPANY_NAME_PREFIX = 'CxmListedCompany';

    protected const string LISTED_COMPANY_NAME_FIRST = 'Aaa';

    protected const string LISTED_COMPANY_NAME_SECOND = 'Zzz';

    protected const string STORE_NAME = 'DE';

    protected const string COUNTRY_NAME = 'Germany';

    protected const string ATTRIBUTE_COMPANY_UUID = 'companyUuid';

    protected const string ATTRIBUTE_COMPANY_BUSINESS_UNIT_UUID = 'companyBusinessUnitUuid';

    protected const string ATTRIBUTE_COMPANY_ROLE_UUIDS = 'companyRoleUuids';

    public const string LISTED_LAST_NAME_PREFIX = 'CxmListed';

    public const string ISO2_CODE = 'DE';

    public const string ADDRESS1 = 'Julie-Wolfthorn-Strasse';

    public const string ADDRESS2 = '1';

    public const string CITY_FIRST_ADDRESS = 'Aachen';

    public const string CITY_SECOND_ADDRESS = 'Berlin';

    public const string ZIP_CODE_FIRST_ADDRESS = '52062';

    public const string ZIP_CODE_SECOND_ADDRESS = '10115';

    public function getIso2Code(): string
    {
        return static::ISO2_CODE;
    }

    public function getCountryName(): string
    {
        return static::COUNTRY_NAME;
    }

    public function getFirstAddressCity(): string
    {
        return static::CITY_FIRST_ADDRESS;
    }

    public function getSecondAddressCity(): string
    {
        return static::CITY_SECOND_ADDRESS;
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCompanyAttributes(array $override = []): array
    {
        return $override + [
            CompanyTransfer::NAME => uniqid('CxmCompany', false),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCompanyRequestBody(array $attributes, ?string $uuid = null): string
    {
        return $this->buildRequestBody(static::RESOURCE_COMPANIES, $attributes, $uuid);
    }

    /**
     * @return non-empty-string
     */
    public function getCompanyUrl(string $uuid): string
    {
        return sprintf('/%s/%s', static::RESOURCE_COMPANIES, $uuid);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCompanyCollectionUrl(array $query = []): string
    {
        return sprintf('/%s', static::RESOURCE_COMPANIES) . $this->formatQuery($query);
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCompanyBusinessUnitAttributes(array $override = []): array
    {
        return $override + [
            CompanyBusinessUnitTransfer::NAME => uniqid('CxmBusinessUnit', false),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCompanyBusinessUnitRequestBody(array $attributes, ?string $uuid = null): string
    {
        return $this->buildRequestBody(static::RESOURCE_COMPANY_BUSINESS_UNITS, $attributes, $uuid);
    }

    /**
     * @return non-empty-string
     */
    public function getCompanyBusinessUnitUrl(string $uuid): string
    {
        return sprintf('/%s/%s', static::RESOURCE_COMPANY_BUSINESS_UNITS, $uuid);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCompanyBusinessUnitCollectionUrl(array $query = []): string
    {
        return sprintf('/%s', static::RESOURCE_COMPANY_BUSINESS_UNITS) . $this->formatQuery($query);
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCompanyBusinessUnitAddressAttributes(array $override = []): array
    {
        return $override + [
            'iso2Code' => static::ISO2_CODE,
            'street' => uniqid('CxmStreet', false),
            'number' => '1',
            'city' => static::CITY_FIRST_ADDRESS,
            'zipCode' => static::ZIP_CODE_FIRST_ADDRESS,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCompanyBusinessUnitAddressRequestBody(array $attributes, ?string $uuid = null): string
    {
        return $this->buildRequestBody(static::RESOURCE_COMPANY_BUSINESS_UNIT_ADDRESSES, $attributes, $uuid);
    }

    /**
     * @return non-empty-string
     */
    public function getCompanyBusinessUnitAddressUrl(string $uuid): string
    {
        return sprintf('/%s/%s', static::RESOURCE_COMPANY_BUSINESS_UNIT_ADDRESSES, $uuid);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCompanyBusinessUnitAddressCollectionUrl(array $query = []): string
    {
        return sprintf('/%s', static::RESOURCE_COMPANY_BUSINESS_UNIT_ADDRESSES) . $this->formatQuery($query);
    }

    public function buildListedCompanyNameToken(): string
    {
        return uniqid(static::LISTED_COMPANY_NAME_PREFIX);
    }

    public function buildFirstListedCompanyName(string $token): string
    {
        return $token . static::LISTED_COMPANY_NAME_FIRST;
    }

    public function buildSecondListedCompanyName(string $token): string
    {
        return $token . static::LISTED_COMPANY_NAME_SECOND;
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCustomerAttributes(array $override = []): array
    {
        return $override + [
            CustomerTransfer::EMAIL => uniqid('cxm.backend.api.', true) . '@spryker.local',
            CustomerTransfer::SALUTATION => 'Mr',
            CustomerTransfer::FIRST_NAME => 'Created',
            CustomerTransfer::LAST_NAME => 'ViaBackendApi',
            CustomerTransfer::STORE_NAME => static::STORE_NAME,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCustomerRequestBody(array $attributes, ?string $customerReference = null): string
    {
        return $this->buildRequestBody(static::RESOURCE_CUSTOMERS, $attributes, $customerReference);
    }

    /**
     * @return non-empty-string
     */
    public function getCustomerUrl(string $customerReference): string
    {
        return sprintf('/%s/%s', static::RESOURCE_CUSTOMERS, $customerReference);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCustomerUrlWithQuery(string $customerReference, array $query = []): string
    {
        return $this->getCustomerUrl($customerReference) . $this->formatQuery($query);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCustomerCollectionUrl(array $query = []): string
    {
        return sprintf('/%s', static::RESOURCE_CUSTOMERS) . $this->formatQuery($query);
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCustomerAddressAttributes(array $override = []): array
    {
        return $override + [
            AddressTransfer::SALUTATION => 'Mr',
            AddressTransfer::FIRST_NAME => 'Created',
            AddressTransfer::LAST_NAME => 'ViaBackendApi',
            AddressTransfer::ADDRESS1 => static::ADDRESS1,
            AddressTransfer::ADDRESS2 => static::ADDRESS2,
            AddressTransfer::CITY => static::CITY_SECOND_ADDRESS,
            AddressTransfer::ZIP_CODE => static::ZIP_CODE_SECOND_ADDRESS,
            AddressTransfer::ISO2_CODE => static::ISO2_CODE,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCustomerAddressRequestBody(array $attributes, ?string $uuid = null): string
    {
        return $this->buildRequestBody(static::RESOURCE_ADDRESSES, $attributes, $uuid);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCustomerAddressCollectionUrl(string $customerReference, array $query = []): string
    {
        return sprintf(
            '/%s/%s/%s',
            static::RESOURCE_CUSTOMERS,
            $customerReference,
            static::RESOURCE_ADDRESSES,
        ) . $this->formatQuery($query);
    }

    /**
     * @return non-empty-string
     */
    public function getCustomerAddressUrl(string $customerReference, string $uuid): string
    {
        return sprintf(
            '/%s/%s/%s/%s',
            static::RESOURCE_CUSTOMERS,
            $customerReference,
            static::RESOURCE_ADDRESSES,
            $uuid,
        );
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCustomerNoteAttributes(array $override = []): array
    {
        return $override + [
            SpyCustomerNoteEntityTransfer::MESSAGE => 'Called the customer about invoice 4711.',
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCustomerNoteRequestBody(array $attributes): string
    {
        return $this->buildRequestBody(static::RESOURCE_TYPE_NOTES, $attributes);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCustomerNoteCollectionUrl(string $customerReference, array $query = []): string
    {
        return sprintf(
            '/%s/%s/%s',
            static::RESOURCE_CUSTOMERS,
            $customerReference,
            static::RESOURCE_NOTES,
        ) . $this->formatQuery($query);
    }

    /**
     * @return non-empty-string
     */
    public function getCustomerNoteUrl(string $customerReference, string $uuid): string
    {
        return sprintf(
            '/%s/%s/%s/%s',
            static::RESOURCE_CUSTOMERS,
            $customerReference,
            static::RESOURCE_NOTES,
            $uuid,
        );
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCompanyUserCollectionUrl(array $query = []): string
    {
        return sprintf('/%s', static::RESOURCE_COMPANY_USERS) . $this->formatQuery($query);
    }

    /**
     * @return non-empty-string
     */
    public function getCompanyUserUrl(string $uuid): string
    {
        return sprintf('/%s/%s', static::RESOURCE_COMPANY_USERS, $uuid);
    }

    /**
     * @return non-empty-string
     */
    public function getCompanyUserSetStatusUrl(string $uuid): string
    {
        return sprintf('/%s/%s/%s', static::RESOURCE_COMPANY_USERS, $uuid, static::OPERATION_SET_STATUS);
    }

    /**
     * @return non-empty-string
     */
    public function getCompanyUserSetDefaultUrl(string $uuid): string
    {
        return sprintf('/%s/%s/%s', static::RESOURCE_COMPANY_USERS, $uuid, static::OPERATION_SET_DEFAULT);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCompanyUserRequestBody(array $attributes): string
    {
        return $this->buildRequestBody(static::RESOURCE_COMPANY_USERS, $attributes);
    }

    public function getListedCompanyUserLastName(): string
    {
        return uniqid(static::LISTED_LAST_NAME_PREFIX);
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildValidCompanyUserAttributes(
        CompanyTransfer $companyTransfer,
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer,
        CompanyRoleTransfer $companyRoleTransfer,
        array $override = [],
    ): array {
        return $override + [
            static::ATTRIBUTE_COMPANY_UUID => $companyTransfer->getUuidOrFail(),
            static::ATTRIBUTE_COMPANY_BUSINESS_UNIT_UUID => $companyBusinessUnitTransfer->getUuidOrFail(),
            static::ATTRIBUTE_COMPANY_ROLE_UUIDS => [$companyRoleTransfer->getUuidOrFail()],
        ];
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    public function buildNewCustomerAttributes(array $override = []): array
    {
        return $override + [
            CustomerTransfer::EMAIL => uniqid('cxm.company.user.', true) . '@spryker.local',
            CustomerTransfer::SALUTATION => 'Mr',
            CustomerTransfer::FIRST_NAME => 'Created',
            CustomerTransfer::LAST_NAME => 'ViaBackendApi',
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function buildRequestBody(string $type, array $attributes, ?string $id = null): string
    {
        $data = [
            'type' => $type,
            'attributes' => $attributes,
        ];

        if ($id !== null) {
            $data['id'] = $id;
        }

        return (string)json_encode(['data' => $data]);
    }

    /**
     * @param array<string, mixed> $query
     */
    protected function formatQuery(array $query): string
    {
        if ($query === []) {
            return '';
        }

        return '?' . http_build_query($query);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCustomerGroupRequestBody(array $attributes, ?string $uuid = null): string
    {
        return $this->buildRequestBody(static::RESOURCE_CUSTOMER_GROUPS, $attributes, $uuid);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCustomerGroupCustomersRequestBody(array $attributes): string
    {
        return $this->buildRequestBody(static::RESOURCE_CUSTOMER_GROUP_CUSTOMERS, $attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function buildCustomerAccessRequestBody(array $attributes): string
    {
        return $this->buildRequestBody(static::RESOURCE_CUSTOMER_ACCESS, $attributes);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCustomerGroupCollectionUrl(array $query = []): string
    {
        return sprintf('/%s', static::RESOURCE_CUSTOMER_GROUPS) . $this->formatQuery($query);
    }

    public function getCustomerGroupUrl(string $uuid): string
    {
        return sprintf('/%s/%s', static::RESOURCE_CUSTOMER_GROUPS, $uuid);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function getCustomerGroupCustomerCollectionUrl(string $uuid, array $query = []): string
    {
        return sprintf(
            '/%s/%s/%s',
            static::RESOURCE_CUSTOMER_GROUPS,
            $uuid,
            static::RESOURCE_CUSTOMERS,
        ) . $this->formatQuery($query);
    }

    public function getCustomerGroupCustomerUrl(string $uuid, string $customerReference): string
    {
        return sprintf(
            '/%s/%s/%s/%s',
            static::RESOURCE_CUSTOMER_GROUPS,
            $uuid,
            static::RESOURCE_CUSTOMERS,
            $customerReference,
        );
    }

    public function getCustomerAccessUrl(): string
    {
        return sprintf('/%s', static::RESOURCE_CUSTOMER_ACCESS);
    }
}
