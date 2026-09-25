<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement;

use Spryker\Glue\Kernel\AbstractBundleConfig;
use Spryker\Zed\CompanyUnitAddress\CompanyUnitAddressConfig;

class CustomerExperienceManagementConfig extends AbstractBundleConfig
{
    /**
     * @uses \Spryker\Shared\Customer\Code\Messages::CUSTOMER_STORE_REQUIRED_FOR_PASSWORD_TOKEN
     */
    protected const string ERROR_MESSAGE_STORE_NAME_REQUIRED = 'customer.store.required_for_password_token';

    /**
     * @uses \Spryker\Zed\Store\Business\Validator\CustomerStoreValidator::ERROR_MESSAGE_STORE_UNKNOWN
     */
    protected const string ERROR_MESSAGE_STORE_UNKNOWN = 'store.validation.unknown_store';

    /**
     * @uses \Spryker\Zed\Locale\Business\Validator\CustomerLocaleValidator::ERROR_MESSAGE_LOCALE_UNKNOWN
     */
    protected const string ERROR_MESSAGE_LOCALE_UNKNOWN = 'locale.validation.unknown_locale';

    /**
     * @uses \Spryker\Zed\Country\Business\Validator\CustomerAddressValidator::ERROR_MESSAGE_COUNTRY_UNKNOWN
     */
    protected const string ERROR_MESSAGE_COUNTRY_UNKNOWN = 'country.validation.unknown_country';

    /**
     * @uses \Spryker\Zed\Country\Business\Validator\CustomerAddressValidator::ERROR_MESSAGE_REGION_UNKNOWN
     */
    protected const string ERROR_MESSAGE_REGION_UNKNOWN = 'country.validation.unknown_region';

    /**
     * @uses \Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitWriter\CompanyBusinessUnitWriter::ERROR_MESSAGE_HAS_RELATED_USERS
     */
    protected const string ERROR_MESSAGE_COMPANY_BUSINESS_UNIT_HAS_RELATED_USERS = 'company.company_business_unit.delete.error.has_users';

    /**
     * @uses \Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitWriter\CompanyBusinessUnitWriter::ERROR_MESSAGE_HIERARCHY_CYCLE_IN_BUSINESS_UNIT_UPDATE
     */
    protected const string ERROR_MESSAGE_COMPANY_BUSINESS_UNIT_HIERARCHY_CYCLE = 'message.business_unit.update.cycle_dependency_error';

    /**
     * @uses \Spryker\Zed\Country\Business\Validator\CustomerAddressValidator::ERROR_MESSAGE_REGION_NOT_IN_COUNTRY
     */
    protected const string ERROR_MESSAGE_REGION_NOT_IN_COUNTRY = 'country.validation.region_not_in_country';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidator::GLOSSARY_KEY_ERROR_NAME_TAKEN
     */
    public const string ERROR_MESSAGE_CUSTOMER_GROUP_NAME_TAKEN = 'message.customer_group.validation.name_taken';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidator::GLOSSARY_KEY_ERROR_CUSTOMER_NOT_FOUND
     */
    public const string ERROR_MESSAGE_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND = 'message.customer_group.validation.customer_not_found';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_NAME_REQUIRED = 'A company role name is required.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_NAME_TOO_LONG = 'The company role name must not exceed 255 characters.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_NAME_NOT_UNIQUE = 'A company role with this name already exists in this company.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_COMPANY_REQUIRED = 'A company is required for a company role.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_NOT_FOUND = 'The company role was not found.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_UNKNOWN_PERMISSION = 'At least one of the given permissions does not exist.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_COMPANY_IMMUTABLE = 'The company of an existing company role cannot be changed.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_DEFAULT_CANNOT_BE_CLEARED = 'The default flag cannot be cleared. Make another company role the default instead.';

    protected const string ERROR_MESSAGE_COMPANY_ROLE_DELETE_IS_DEFAULT = 'The default company role cannot be deleted.';

    /**
     * @uses \Spryker\Zed\CompanyRole\Business\Validator\CompanyRoleValidator::ERROR_DELETE_HAS_USERS
     */
    protected const string ERROR_MESSAGE_COMPANY_ROLE_DELETE_HAS_USERS = 'company.company_role.delete.error.has_users';

    public const string RESPONSE_CODE_CUSTOMER_NOT_FOUND = '1201';

    public const string RESPONSE_CODE_VALIDATION = '1202';

    public const string RESPONSE_CODE_INVALID_SORT_FIELD = '1203';

    public const string RESPONSE_CODE_STORE_NAME_REQUIRED = '1204';

    public const string RESPONSE_CODE_ADDRESS_NOT_FOUND = '1205';

    public const string RESPONSE_CODE_NOTE_NOT_FOUND = '1206';

    public const string RESPONSE_CODE_ACTING_USER_NOT_RESOLVED = '1207';

    public const string RESPONSE_CODE_UNKNOWN_STORE = '1208';

    public const string RESPONSE_CODE_UNKNOWN_LOCALE = '1209';

    public const string RESPONSE_CODE_UNKNOWN_COUNTRY = '1210';

    public const string RESPONSE_CODE_UNKNOWN_REGION = '1211';

    public const string RESPONSE_CODE_REGION_NOT_IN_COUNTRY = '1212';

    public const string RESPONSE_CODE_COMPANY_NOT_FOUND = '1213';

    public const string RESPONSE_CODE_COMPANY_VALIDATION = '1214';

    public const string FILTER_FIELD_COMPANY_UNIT_ADDRESS_COMPANY_UUID = CompanyUnitAddressConfig::FILTER_FIELD_COMPANY_UUID;

    public const string RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD = '1215';

    public const string RESPONSE_CODE_COMPANY_BUSINESS_UNIT_VALIDATION = '1222';

    public const string RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_NOT_FOUND = '1223';

    public const string RESPONSE_CODE_COMPANY_BUSINESS_UNIT_HAS_RELATED_USERS = '1224';

    public const string RESPONSE_CODE_COMPANY_BUSINESS_UNIT_HIERARCHY_CYCLE = '1225';

    public const string RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_COMPANY_MISMATCH = '1226';

    public const string RESPONSE_CODE_COMPANY_UNIT_ADDRESS_NOT_FOUND = '1227';

    public const string RESPONSE_CODE_COMPANY_UNIT_ADDRESS_VALIDATION = '1228';

    public const string RESPONSE_CODE_COMPANY_UNIT_ADDRESS_COMPANY_MISMATCH = '1229';

    public const string RESPONSE_CODE_COMPANY_USER_NOT_FOUND = '1216';

    public const string RESPONSE_CODE_COMPANY_BUSINESS_UNIT_NOT_FOUND = '1217';

    public const string RESPONSE_CODE_COMPANY_ROLE_NOT_FOUND = '1218';

    public const string RESPONSE_CODE_COMPANY_USER_STATUS_INVALID = '1219';

    public const string RESPONSE_CODE_COMPANY_USER_CUSTOMER_MISSING = '1220';

    public const string RESPONSE_CODE_COMPANY_USER_DEFAULT_INVALID = '1221';

    public const string RESPONSE_CODE_CUSTOMER_GROUP_NOT_FOUND = '1222';

    public const string RESPONSE_CODE_CUSTOMER_GROUP_NAME_TAKEN = '1223';

    public const string RESPONSE_CODE_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND = '1224';

    public const string RESPONSE_CODE_CUSTOMER_GROUP_ASSIGNMENT_NOT_FOUND = '1225';

    public const string RESPONSE_CODE_CUSTOMER_ACCESS_UNKNOWN_CONTENT_TYPE = '1226';

    public const string RESPONSE_CODE_CUSTOMER_ACCESS_DUPLICATE_CONTENT_TYPE = '1227';

    public const string RESPONSE_DETAILS_CUSTOMER_GROUP_NOT_FOUND = 'Customer group with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_CUSTOMER_GROUP_VALIDATION = 'The customer group request could not be processed.';

    public const string RESPONSE_DETAILS_CUSTOMER_GROUP_NAME_TAKEN = 'Customer group name "%s" is already taken. Names are compared case-insensitively.';

    public const string RESPONSE_DETAILS_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND = 'Customer with reference "%s" was not found.';

    public const string RESPONSE_DETAILS_CUSTOMER_GROUP_ASSIGNMENT_NOT_FOUND = 'Customer "%s" is not a member of customer group "%s".';

    public const string RESPONSE_DETAILS_CUSTOMER_ACCESS_UNKNOWN_CONTENT_TYPE = 'Content type "%s" is not configured for this project. Configured content types: %s.';

    public const string RESPONSE_DETAILS_CUSTOMER_ACCESS_DUPLICATE_CONTENT_TYPE = 'Content type "%s" was sent more than once.';

    public const string RESPONSE_CODE_COMPANY_ROLE_VALIDATION = '1230';

    public const string RESPONSE_CODE_COMPANY_ROLE_IS_DEFAULT = '1231';

    public const string RESPONSE_CODE_COMPANY_ROLE_HAS_COMPANY_USERS = '1232';

    public const string RESPONSE_CODE_UNKNOWN_PERMISSION = '1233';

    public const string RESPONSE_CODE_COMPANY_ROLE_DEFAULT_CANNOT_BE_CLEARED = '1234';

    public const string RESPONSE_CODE_COMPANY_ROLE_COMPANY_IMMUTABLE = '1235';

    public const string RESPONSE_DETAILS_CUSTOMER_NOT_FOUND = 'Customer with reference "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_USER_NOT_FOUND = 'Company user with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_BUSINESS_UNIT_NOT_FOUND = 'Company business unit with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_USER_STATUS_INVALID = 'The request body must contain an "isActive" boolean.';

    public const string RESPONSE_DETAILS_COMPANY_USER_DEFAULT_INVALID = 'The request body must contain an "isDefault" boolean.';

    public const string RESPONSE_DETAILS_COMPANY_USER_VALIDATION = 'The company user request could not be processed.';

    public const string RESPONSE_DETAILS_COMPANY_USER_CUSTOMER_MISSING = 'No customer was given. Send "customerReference" to use an existing customer, or a "customer" object with at least an email address to create one.';

    public const string RESPONSE_DETAILS_INVALID_SORT_FIELD = 'Sort field "%s" is not supported. Supported fields: %s.';

    public const string RESPONSE_DETAILS_STORE_NAME_REQUIRED = 'The "storeName" field is required when "sendPasswordToken" is enabled, as it provides the context for the email template.';

    public const string RESPONSE_DETAILS_VALIDATION = 'The customer request could not be processed.';

    public const string RESPONSE_DETAILS_ADDRESS_NOT_FOUND = 'Address with uuid "%s" was not found for customer "%s".';

    public const string RESPONSE_DETAILS_NOTE_NOT_FOUND = 'Note with uuid "%s" was not found for customer "%s".';

    public const string RESPONSE_DETAILS_ACTING_USER_NOT_RESOLVED = 'The Back Office user acting on this request could not be resolved from the access token.';

    public const string RESPONSE_DETAILS_UNKNOWN_STORE = 'Store "%s" does not exist. Available stores: %s.';

    public const string RESPONSE_DETAILS_UNKNOWN_LOCALE = 'Locale "%s" does not exist.';

    public const string RESPONSE_DETAILS_COMPANY_NOT_FOUND = 'Company with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_ROLE_NOT_FOUND = 'Company role with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_ROLE_VALIDATION = 'The company role request could not be processed.';

    public const string RESPONSE_DETAILS_COMPANY_ROLE_COMPANY_IMMUTABLE = 'The "companyUuid" field cannot be changed on an existing company role.';

    public const string RESPONSE_DETAILS_COMPANY_VALIDATION = 'The company request could not be processed.';

    public const string RESPONSE_DETAILS_UNSUPPORTED_FILTER_FIELD = 'Filter field "%s" is not supported. Supported fields: %s.';

    public const string RESPONSE_DETAILS_NON_SCALAR_FILTER_VALUE = 'Filter field "%s" expects a single value.';

    public const string RESPONSE_DETAILS_COMPANY_BUSINESS_UNIT_VALIDATION = 'The company business unit request could not be processed.';

    public const string RESPONSE_DETAILS_UNKNOWN_COUNTRY = 'Country "%s" is not available in this shop.';

    public const string RESPONSE_DETAILS_UNKNOWN_COMPANY_UNIT_ADDRESS_LABEL = 'labels => Label "%s" is not configured in the shop. Supported labels: %s.';

    public const string RESPONSE_DETAILS_PARENT_COMPANY_BUSINESS_UNIT_NOT_FOUND = 'Parent company business unit with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_PARENT_COMPANY_BUSINESS_UNIT_COMPANY_MISMATCH = 'Parent company business unit "%s" belongs to another company.';

    public const string RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_NOT_FOUND = 'Company business unit address with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_VALIDATION = 'The company business unit address request could not be processed.';

    public const string RESPONSE_DETAILS_COMPANY_UNIT_ADDRESS_COMPANY_MISMATCH = 'Company business unit address "%s" belongs to another company.';

    /**
     * Specification:
     * - Maps a domain validation glossary key to the API response code reported for it.
     * - A key absent from the map falls back to the generic validation code.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getResponseCodeByErrorMessage(): array
    {
        return [
            static::ERROR_MESSAGE_STORE_NAME_REQUIRED => static::RESPONSE_CODE_STORE_NAME_REQUIRED,
            static::ERROR_MESSAGE_STORE_UNKNOWN => static::RESPONSE_CODE_UNKNOWN_STORE,
            static::ERROR_MESSAGE_LOCALE_UNKNOWN => static::RESPONSE_CODE_UNKNOWN_LOCALE,
            static::ERROR_MESSAGE_COUNTRY_UNKNOWN => static::RESPONSE_CODE_UNKNOWN_COUNTRY,
            static::ERROR_MESSAGE_REGION_UNKNOWN => static::RESPONSE_CODE_UNKNOWN_REGION,
            static::ERROR_MESSAGE_REGION_NOT_IN_COUNTRY => static::RESPONSE_CODE_REGION_NOT_IN_COUNTRY,
            static::ERROR_MESSAGE_COMPANY_ROLE_NAME_REQUIRED => static::RESPONSE_CODE_COMPANY_ROLE_VALIDATION,
            static::ERROR_MESSAGE_COMPANY_ROLE_NAME_TOO_LONG => static::RESPONSE_CODE_COMPANY_ROLE_VALIDATION,
            static::ERROR_MESSAGE_COMPANY_ROLE_NAME_NOT_UNIQUE => static::RESPONSE_CODE_COMPANY_ROLE_VALIDATION,
            static::ERROR_MESSAGE_COMPANY_ROLE_COMPANY_REQUIRED => static::RESPONSE_CODE_COMPANY_ROLE_VALIDATION,
            static::ERROR_MESSAGE_COMPANY_ROLE_NOT_FOUND => static::RESPONSE_CODE_COMPANY_ROLE_NOT_FOUND,
            static::ERROR_MESSAGE_COMPANY_ROLE_UNKNOWN_PERMISSION => static::RESPONSE_CODE_UNKNOWN_PERMISSION,
            static::ERROR_MESSAGE_COMPANY_ROLE_COMPANY_IMMUTABLE => static::RESPONSE_CODE_COMPANY_ROLE_COMPANY_IMMUTABLE,
            static::ERROR_MESSAGE_COMPANY_ROLE_DEFAULT_CANNOT_BE_CLEARED => static::RESPONSE_CODE_COMPANY_ROLE_DEFAULT_CANNOT_BE_CLEARED,
            static::ERROR_MESSAGE_COMPANY_ROLE_DELETE_IS_DEFAULT => static::RESPONSE_CODE_COMPANY_ROLE_IS_DEFAULT,
            static::ERROR_MESSAGE_COMPANY_ROLE_DELETE_HAS_USERS => static::RESPONSE_CODE_COMPANY_ROLE_HAS_COMPANY_USERS,
            static::ERROR_MESSAGE_COMPANY_BUSINESS_UNIT_HAS_RELATED_USERS => static::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_HAS_RELATED_USERS,
            static::ERROR_MESSAGE_COMPANY_BUSINESS_UNIT_HIERARCHY_CYCLE => static::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_HIERARCHY_CYCLE,
            static::ERROR_MESSAGE_CUSTOMER_GROUP_NAME_TAKEN => static::RESPONSE_CODE_CUSTOMER_GROUP_NAME_TAKEN,
            static::ERROR_MESSAGE_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND => static::RESPONSE_CODE_CUSTOMER_GROUP_CUSTOMER_NOT_FOUND,
        ];
    }
}
