<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitResponseTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyResponseTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\ResponseMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CompanyBusinessUnitsBackendProcessor;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyBusinessUnitsBackendProcessorTest
 * Add your own group annotations below this line
 */
class CompanyBusinessUnitsBackendProcessorTest extends BackendApiTestCase
{
    protected const string UUID = '4d1b3f9a-9d4c-5c1e-9f6b-2b5a7c8d9e01';

    protected const string COMPANY_UUID = '0818f408-cc84-575d-ad54-92118a0e4273';

    protected const string PARENT_UUID = 'b7c2e4d6-1a3f-5b8c-9d0e-4f6a8b2c1d3e';

    protected const string UNKNOWN_UUID = '11111111-2222-4333-8444-555555555555';

    protected const int ID_COMPANY = 41;

    protected const int ID_OTHER_COMPANY = 42;

    protected const int ID_PARENT_COMPANY_BUSINESS_UNIT = 7;

    protected const string NAME = 'Acme Procurement';

    protected const string ERROR_MESSAGE_CYCLE = 'message.business_unit.update.cycle_dependency_error';

    protected const string ERROR_MESSAGE_HAS_RELATED_USERS = 'company.company_business_unit.delete.error.has_users';

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostCreatesTheBusinessUnitForTheCompanyFromThePayload(): void
    {
        // Arrange
        $capturedCompanyBusinessUnitTransfer = null;
        $processor = $this->createProcessor([
            'create' => $this->captureInto($capturedCompanyBusinessUnitTransfer),
        ]);

        // Act
        $processor->process(
            $this->buildResource([
                CompanyBusinessUnitTransfer::NAME => static::NAME,
                'companyUuid' => static::COMPANY_UUID,
            ]),
            $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
            [],
            $this->buildRequestContext([
                CompanyBusinessUnitTransfer::NAME => static::NAME,
                'companyUuid' => static::COMPANY_UUID,
            ]),
        );

        // Assert
        $this->assertSame(static::NAME, $capturedCompanyBusinessUnitTransfer->getName());
        $this->assertSame(
            static::ID_COMPANY,
            $capturedCompanyBusinessUnitTransfer->getFkCompany(),
            'The company uuid from the payload must be resolved to the company it identifies.',
        );
    }

    public function testProcessPostReturnsTheReReadBusinessUnitSoItsRelationsArePopulated(): void
    {
        // Arrange
        $createdCompanyBusinessUnitTransfer = (new CompanyBusinessUnitTransfer())
            ->setUuid(static::UUID)
            ->setName(static::NAME)
            ->setFkCompany(static::ID_COMPANY);

        $processor = $this->createProcessor([
            'create' => fn (): CompanyBusinessUnitResponseTransfer => (new CompanyBusinessUnitResponseTransfer())
                ->setIsSuccessful(true)
                ->setCompanyBusinessUnitTransfer($createdCompanyBusinessUnitTransfer),
        ]);

        // Act
        $resource = $processor->process(
            $this->buildResource([
                CompanyBusinessUnitTransfer::NAME => static::NAME,
                'companyUuid' => static::COMPANY_UUID,
            ]),
            $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
            [],
            $this->buildRequestContext([
                CompanyBusinessUnitTransfer::NAME => static::NAME,
                'companyUuid' => static::COMPANY_UUID,
            ]),
        );

        // Assert
        $this->assertSame(static::UUID, $resource->uuid);
        $this->assertSame(static::COMPANY_UUID, $resource->companyUuid);
    }

    public function testProcessPostResolvesTheParentBusinessUnitFromItsUuid(): void
    {
        // Arrange
        $capturedCompanyBusinessUnitTransfer = null;
        $processor = $this->createProcessor([
            'create' => $this->captureInto($capturedCompanyBusinessUnitTransfer),
        ]);

        // Act
        $processor->process(
            $this->buildResource([
                CompanyBusinessUnitTransfer::NAME => static::NAME,
                'companyUuid' => static::COMPANY_UUID,
                'parentBusinessUnitUuid' => static::PARENT_UUID,
            ]),
            $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
            [],
            $this->buildRequestContext([
                CompanyBusinessUnitTransfer::NAME => static::NAME,
                'companyUuid' => static::COMPANY_UUID,
                'parentBusinessUnitUuid' => static::PARENT_UUID,
            ]),
        );

        // Assert
        $this->assertSame(
            static::ID_PARENT_COMPANY_BUSINESS_UNIT,
            $capturedCompanyBusinessUnitTransfer->getFkParentCompanyBusinessUnit(),
        );
    }

    public function testProcessPostRejectsAParentThatBelongsToAnotherCompany(): void
    {
        // Arrange
        $processor = $this->createProcessor(
            ['create' => $this->captureInto($unused)],
            static::ID_OTHER_COMPANY,
        );

        // Act & Assert
        $this->assertGlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_COMPANY_MISMATCH,
            fn () => $processor->process(
                $this->buildResource([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::COMPANY_UUID,
                    'parentBusinessUnitUuid' => static::PARENT_UUID,
                ]),
                $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
                [],
                $this->buildRequestContext([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::COMPANY_UUID,
                    'parentBusinessUnitUuid' => static::PARENT_UUID,
                ]),
            ),
        );
    }

    public function testProcessPostReportsAnUnknownCompanyAsNotFound(): void
    {
        // Arrange
        $processor = $this->createProcessor(['create' => $this->captureInto($unused)]);

        // Act & Assert
        $this->assertGlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
            fn () => $processor->process(
                $this->buildResource([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::UNKNOWN_UUID,
                ]),
                $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
                [],
                $this->buildRequestContext([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::UNKNOWN_UUID,
                ]),
            ),
        );
    }

    public function testProcessPostReportsAnUnknownParentSeparatelyFromAnUnknownBusinessUnit(): void
    {
        // Arrange
        $processor = $this->createProcessor(['create' => $this->captureInto($unused)]);

        // Act & Assert
        $this->assertGlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_PARENT_COMPANY_BUSINESS_UNIT_NOT_FOUND,
            fn () => $processor->process(
                $this->buildResource([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::COMPANY_UUID,
                    'parentBusinessUnitUuid' => static::UNKNOWN_UUID,
                ]),
                $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
                [],
                $this->buildRequestContext([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::COMPANY_UUID,
                    'parentBusinessUnitUuid' => static::UNKNOWN_UUID,
                ]),
            ),
        );
    }

    public function testProcessPostSurfacesARejectionFromTheFacadeRatherThanReturningAResource(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'create' => fn (): CompanyBusinessUnitResponseTransfer => (new CompanyBusinessUnitResponseTransfer())
                ->setIsSuccessful(false)
                ->addMessage((new ResponseMessageTransfer())->setText(static::ERROR_MESSAGE_CYCLE)),
        ]);

        // Act & Assert
        $this->assertGlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_HIERARCHY_CYCLE,
            fn () => $processor->process(
                $this->buildResource([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::COMPANY_UUID,
                ]),
                $this->tester->getPostOperation(CompanyBusinessUnitsBackendResource::class),
                [],
                $this->buildRequestContext([
                    CompanyBusinessUnitTransfer::NAME => static::NAME,
                    'companyUuid' => static::COMPANY_UUID,
                ]),
            ),
        );
    }

    public function testProcessDeleteDeletesTheBusinessUnitTheUriNames(): void
    {
        // Arrange
        $capturedCompanyBusinessUnitTransfer = null;
        $processor = $this->createProcessor([
            'delete' => function (
                CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
            ) use (&$capturedCompanyBusinessUnitTransfer): CompanyBusinessUnitResponseTransfer {
                $capturedCompanyBusinessUnitTransfer = $companyBusinessUnitTransfer;

                return (new CompanyBusinessUnitResponseTransfer())->setIsSuccessful(true);
            },
        ]);

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(CompanyBusinessUnitsBackendResource::class),
            [CompanyBusinessUnitTransfer::UUID => static::UUID],
        );

        // Assert
        $this->assertNull($result, 'A delete answers with no content.');
        $this->assertSame(static::UUID, $capturedCompanyBusinessUnitTransfer->getUuid());
    }

    public function testProcessDeleteReportsAnUnknownBusinessUnitAsNotFound(): void
    {
        // Arrange
        $processor = $this->createProcessor(['delete' => fn (): CompanyBusinessUnitResponseTransfer => new CompanyBusinessUnitResponseTransfer()]);

        // Act & Assert
        $this->assertGlueApiException(
            Response::HTTP_NOT_FOUND,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_NOT_FOUND,
            fn () => $processor->process(
                null,
                $this->tester->getDeleteOperation(CompanyBusinessUnitsBackendResource::class),
                [CompanyBusinessUnitTransfer::UUID => static::UNKNOWN_UUID],
            ),
        );
    }

    public function testProcessDeleteSurfacesTheRelatedUsersRejectionUnderItsOwnCode(): void
    {
        // Arrange
        $processor = $this->createProcessor([
            'delete' => fn (): CompanyBusinessUnitResponseTransfer => (new CompanyBusinessUnitResponseTransfer())
                ->setIsSuccessful(false)
                ->addMessage((new ResponseMessageTransfer())->setText(static::ERROR_MESSAGE_HAS_RELATED_USERS)),
        ]);

        // Act & Assert
        $this->assertGlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_BUSINESS_UNIT_HAS_RELATED_USERS,
            fn () => $processor->process(
                null,
                $this->tester->getDeleteOperation(CompanyBusinessUnitsBackendResource::class),
                [CompanyBusinessUnitTransfer::UUID => static::UUID],
            ),
        );
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    protected function buildRequestContext(array $attributes): array
    {
        $body = (string)json_encode(['data' => ['type' => 'company-business-units', 'attributes' => $attributes]]);

        return ['request' => Request::create('/company-business-units', Request::METHOD_POST, [], [], [], [], $body)];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function buildResource(array $attributes): CompanyBusinessUnitsBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyBusinessUnitsBackendResource $resource */
        $resource = $this->tester->getResource(CompanyBusinessUnitsBackendResource::class, $attributes);

        return $resource;
    }

    protected function captureInto(?CompanyBusinessUnitTransfer &$capturedCompanyBusinessUnitTransfer): callable
    {
        return function (
            CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
        ) use (&$capturedCompanyBusinessUnitTransfer): CompanyBusinessUnitResponseTransfer {
            $capturedCompanyBusinessUnitTransfer = $companyBusinessUnitTransfer;

            return (new CompanyBusinessUnitResponseTransfer())
                ->setIsSuccessful(true)
                ->setCompanyBusinessUnitTransfer((clone $companyBusinessUnitTransfer)->setUuid(static::UUID));
        };
    }

    protected function assertGlueApiException(
        int $expectedStatusCode,
        string $expectedErrorCode,
        callable $act
    ): void {
        try {
            $act();
        } catch (GlueApiException $glueApiException) {
            $this->assertSame($expectedStatusCode, $glueApiException->getStatusCode());
            $this->assertSame($expectedErrorCode, $glueApiException->getErrorCode());

            return;
        }

        $this->fail(sprintf('Expected a %s with error code %s, none was thrown.', GlueApiException::class, $expectedErrorCode));
    }

    /**
     * @param array<string, mixed> $companyBusinessUnitFacadeMethods
     */
    protected function createProcessor(
        array $companyBusinessUnitFacadeMethods,
        int $idCompanyOfParent = self::ID_COMPANY
    ): CompanyBusinessUnitsBackendProcessor {
        $this->tester->setService(
            CompanyFacadeInterface::class,
            $this->tester->createClientStub(CompanyFacadeInterface::class, [
                'findCompanyByUuid' => fn (CompanyTransfer $companyTransfer): CompanyResponseTransfer => $this
                    ->findCompany($companyTransfer),
            ]),
        );

        $this->tester->setService(
            CompanyBusinessUnitFacadeInterface::class,
            $this->tester->createClientStub(CompanyBusinessUnitFacadeInterface::class, $companyBusinessUnitFacadeMethods + [
                'getCompanyBusinessUnitCollection' => fn (
                    CompanyBusinessUnitCriteriaFilterTransfer $criteriaFilterTransfer
                ): CompanyBusinessUnitCollectionTransfer => $this->findCompanyBusinessUnits(
                    $criteriaFilterTransfer,
                    $idCompanyOfParent,
                ),
            ]),
        );

        return $this->tester->getProcessor(CompanyBusinessUnitsBackendProcessor::class);
    }

    protected function findCompany(CompanyTransfer $companyTransfer): CompanyResponseTransfer
    {
        if ($companyTransfer->getUuid() !== static::COMPANY_UUID) {
            return (new CompanyResponseTransfer())->setIsSuccessful(false);
        }

        return (new CompanyResponseTransfer())
            ->setIsSuccessful(true)
            ->setCompanyTransfer(
                (new CompanyTransfer())->setUuid(static::COMPANY_UUID)->setIdCompany(static::ID_COMPANY),
            );
    }

    protected function findCompanyBusinessUnits(
        CompanyBusinessUnitCriteriaFilterTransfer $criteriaFilterTransfer,
        int $idCompanyOfParent
    ): CompanyBusinessUnitCollectionTransfer {
        $companyBusinessUnitCollectionTransfer = new CompanyBusinessUnitCollectionTransfer();

        foreach ($criteriaFilterTransfer->getUuids() as $uuid) {
            if ($uuid === static::PARENT_UUID) {
                $companyBusinessUnitCollectionTransfer->addCompanyBusinessUnit(
                    (new CompanyBusinessUnitTransfer())
                        ->setUuid(static::PARENT_UUID)
                        ->setIdCompanyBusinessUnit(static::ID_PARENT_COMPANY_BUSINESS_UNIT)
                        ->setFkCompany($idCompanyOfParent),
                );
            }

            if ($uuid === static::UUID) {
                $companyBusinessUnitCollectionTransfer->addCompanyBusinessUnit(
                    (new CompanyBusinessUnitTransfer())
                        ->setUuid(static::UUID)
                        ->setName(static::NAME)
                        ->setFkCompany(static::ID_COMPANY)
                        ->setCompany((new CompanyTransfer())->setUuid(static::COMPANY_UUID)),
                );
            }
        }

        return $companyBusinessUnitCollectionTransfer;
    }
}
