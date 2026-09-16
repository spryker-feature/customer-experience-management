<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement;

use Spryker\Glue\Kernel\AbstractBundleConfig;

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
     * @uses \Spryker\Zed\Country\Business\Validator\CustomerAddressValidator::ERROR_MESSAGE_REGION_NOT_IN_COUNTRY
     */
    protected const string ERROR_MESSAGE_REGION_NOT_IN_COUNTRY = 'country.validation.region_not_in_country';

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

    public const string RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD = '1215';

    public const string RESPONSE_CODE_COMPANY_USER_NOT_FOUND = '1216';

    public const string RESPONSE_CODE_COMPANY_BUSINESS_UNIT_NOT_FOUND = '1217';

    public const string RESPONSE_CODE_COMPANY_ROLE_NOT_FOUND = '1218';

    public const string RESPONSE_CODE_COMPANY_USER_STATUS_INVALID = '1219';

    public const string RESPONSE_CODE_COMPANY_USER_CUSTOMER_MISSING = '1220';

    public const string RESPONSE_CODE_COMPANY_USER_DEFAULT_INVALID = '1221';

    public const string RESPONSE_DETAILS_CUSTOMER_NOT_FOUND = 'Customer with reference "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_USER_NOT_FOUND = 'Company user with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_BUSINESS_UNIT_NOT_FOUND = 'Company business unit with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_COMPANY_ROLE_NOT_FOUND = 'Company role with uuid "%s" was not found.';

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

    public const string RESPONSE_DETAILS_COMPANY_VALIDATION = 'The company request could not be processed.';

    public const string RESPONSE_DETAILS_UNSUPPORTED_FILTER_FIELD = 'Filter field "%s" is not supported. Supported fields: %s.';

    public const string RESPONSE_DETAILS_NON_SCALAR_FILTER_VALUE = 'Filter field "%s" expects a single value.';

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
        ];
    }
}
