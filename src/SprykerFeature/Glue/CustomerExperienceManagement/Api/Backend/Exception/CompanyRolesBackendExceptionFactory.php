<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception;

use ArrayObject;
use Generated\Shared\Transfer\CompanyRoleResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyRolesBackendExceptionFactory
{
    /**
     * The codes that describe a missing entity rather than a rejected value. Everything else keeps
     * the generic 422. A list is used rather than a code-to-status map because PHP casts a
     * numeric-string array key to an integer, which would make such a map's declared key type a lie.
     *
     * @var list<string>
     */
    protected const array NOT_FOUND_RESPONSE_CODES = [
        CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_NOT_FOUND,
        CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
    ];

    /**
     * The domain every module's `data/translation/Api/<locale>.csv` is registered under.
     */
    protected const string VALIDATORS_DOMAIN = 'validators';

    public function __construct(
        protected CustomerExperienceManagementConfig $config,
        protected TranslatorInterface $translator,
    ) {
    }

    /**
     * @param list<string> $supportedFields
     */
    public function createUnsupportedFilterFieldException(string $field, array $supportedFields): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD,
            sprintf(
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_UNSUPPORTED_FILTER_FIELD,
                $field,
                implode(', ', $supportedFields),
            ),
        );
    }

    public function createNonScalarFilterValueException(string $field): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomerExperienceManagementConfig::RESPONSE_CODE_UNSUPPORTED_FILTER_FIELD,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_NON_SCALAR_FILTER_VALUE, $field),
        );
    }

    public function createCompanyRoleNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_NOT_FOUND,
            sprintf(CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_ROLE_NOT_FOUND, $uuid),
        );
    }

    public function createCompanyImmutableException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_COMPANY_IMMUTABLE,
            CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_ROLE_COMPANY_IMMUTABLE,
        );
    }

    public function createExceptionFromCompanyRoleResponse(
        CompanyRoleResponseTransfer $companyRoleResponseTransfer
    ): GlueApiException {
        return $this->createExceptionFromMessageTexts(
            $this->extractMessageTexts($companyRoleResponseTransfer->getMessages()),
        );
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ResponseMessageTransfer> $messages
     *
     * @return list<string>
     */
    protected function extractMessageTexts(ArrayObject $messages): array
    {
        $messageTexts = [];

        foreach ($messages as $responseMessageTransfer) {
            $text = $responseMessageTransfer->getText();

            if ($text !== null) {
                $messageTexts[] = $text;
            }
        }

        return $messageTexts;
    }

    /**
     * @param list<string> $messageTexts
     */
    protected function createExceptionFromMessageTexts(array $messageTexts): GlueApiException
    {
        if ($messageTexts === []) {
            return new GlueApiException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_VALIDATION,
                CustomerExperienceManagementConfig::RESPONSE_DETAILS_COMPANY_ROLE_VALIDATION,
            );
        }

        $errors = $this->mapMessageTextsToErrors($messageTexts);
        $firstError = $errors[0];

        $glueApiException = new GlueApiException(
            $firstError[RestErrorMessageTransfer::STATUS],
            $firstError[RestErrorMessageTransfer::CODE],
            $firstError[RestErrorMessageTransfer::DETAIL],
        );

        if (count($errors) > 1) {
            $glueApiException->setErrors($errors);
        }

        return $glueApiException;
    }

    /**
     * @param list<string> $messageTexts
     *
     * @return list<array{code: string, status: int, detail: string}>
     */
    protected function mapMessageTextsToErrors(array $messageTexts): array
    {
        $errors = [];

        foreach ($messageTexts as $messageText) {
            $responseCode = $this->resolveResponseCode($messageText);

            $errors[] = [
                RestErrorMessageTransfer::CODE => $responseCode,
                RestErrorMessageTransfer::STATUS => $this->resolveHttpStatus($responseCode),
                RestErrorMessageTransfer::DETAIL => $this->translateMessageText($messageText),
            ];
        }

        return $errors;
    }

    protected function translateMessageText(string $messageText): string
    {
        return $this->translator->trans($messageText, [], static::VALIDATORS_DOMAIN);
    }

    protected function resolveHttpStatus(string $responseCode): int
    {
        return in_array($responseCode, static::NOT_FOUND_RESPONSE_CODES, true)
            ? Response::HTTP_NOT_FOUND
            : Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    protected function resolveResponseCode(string $messageText): string
    {
        return $this->config->getResponseCodeByErrorMessage()[$messageText]
            ?? CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_VALIDATION;
    }
}
