<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ArrayObject;
use Generated\Api\Backend\CompanyRolesBackendResource;
use Generated\Shared\Transfer\CompanyResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Generated\Shared\Transfer\PermissionTransfer;
use Generated\Shared\Transfer\ResponseMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyRole\Business\CompanyRoleFacadeInterface;
use Spryker\Zed\Permission\Business\PermissionFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor\CompanyRolesBackendProcessor;
use SprykerFeature\Glue\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyRolesBackendProcessorTest
 * Add your own group annotations below this line
 */
class CompanyRolesBackendProcessorTest extends BackendApiTestCase
{
    /**
     * @var string
     */
    protected const ROLE_UUID = '50c647a4-d27f-5d82-a587-1d0b7cc6b58d';

    /**
     * @var string
     */
    protected const COMPANY_UUID = 'a0e4d1c8-6b47-5f19-9c2d-3f8b1e7a5d40';

    /**
     * @var string
     */
    protected const OTHER_COMPANY_UUID = 'b1f7c3d2-8a41-5c6e-9d70-2e5b8f0a4c31';

    /**
     * @var string
     */
    protected const PERMISSION_KEY = 'ApproveQuotePermissionPlugin';

    /**
     * @var int
     */
    protected const ID_COMPANY_ROLE = 7;

    /**
     * @var int
     */
    protected const ID_COMPANY = 41;

    /**
     * @var int
     */
    protected const ID_PERMISSION = 3;

    protected CustomerExperienceManagementApiTester $tester;

    public function testProcessPostCreatesTheRoleAgainstTheCompanyNamedByUuid(): void
    {
        // Arrange
        $capturedCompanyRoleTransfer = null;
        $processor = $this->createProcessor(capturedCompanyRole: $capturedCompanyRoleTransfer);

        // Act
        $processor->process(
            $this->createResource(['name' => 'Approver', 'companyUuid' => static::COMPANY_UUID]),
            $this->tester->getPostOperation(),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $companyRoleTransfer = $this->getCaptured($capturedCompanyRoleTransfer);
        $this->assertSame('Approver', $companyRoleTransfer->getName());
        $this->assertSame(static::ID_COMPANY, $companyRoleTransfer->getFkCompany());
    }

    public function testProcessPostResolvesPermissionKeysIntoThePermissionsBeingAssigned(): void
    {
        // Arrange
        $capturedCompanyRoleTransfer = null;
        $processor = $this->createProcessor(capturedCompanyRole: $capturedCompanyRoleTransfer);

        // Act
        $processor->process(
            $this->createResource([
                'name' => 'Approver',
                'companyUuid' => static::COMPANY_UUID,
                'permissionKeys' => [static::PERMISSION_KEY],
            ]),
            $this->tester->getPostOperation(),
            [],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $permissions = $this->getCapturedPermissions($capturedCompanyRoleTransfer);
        $this->assertCount(1, $permissions);
        $this->assertSame(static::ID_PERMISSION, $permissions[0]->getIdPermission());
    }

    /**
     * The facade replaces the whole permission set on every write, so an update that does not
     * mention permissions has to resend the stored ones — otherwise renaming a role would silently
     * strip every permission it holds.
     */
    public function testProcessPatchWithoutPermissionKeysKeepsTheStoredPermissions(): void
    {
        // Arrange
        $capturedCompanyRoleTransfer = null;
        $processor = $this->createProcessor(capturedCompanyRole: $capturedCompanyRoleTransfer);

        // Act
        $processor->process(
            $this->createResource(['name' => 'Renamed']),
            $this->tester->getPatchOperation(),
            ['uuid' => static::ROLE_UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $permissions = $this->getCapturedPermissions($capturedCompanyRoleTransfer);
        $this->assertCount(1, $permissions);
        $this->assertSame(static::ID_PERMISSION, $permissions[0]->getIdPermission());
    }

    public function testProcessPatchWithAnEmptyPermissionKeysDetachesEveryPermission(): void
    {
        // Arrange
        $capturedCompanyRoleTransfer = null;
        $processor = $this->createProcessor(capturedCompanyRole: $capturedCompanyRoleTransfer);

        // Act
        $processor->process(
            $this->createResource(['permissionKeys' => []]),
            $this->tester->getPatchOperation(),
            ['uuid' => static::ROLE_UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertCount(0, $this->getCapturedPermissions($capturedCompanyRoleTransfer));
    }

    /**
     * A role never moves between companies, so a payload naming a different one is refused rather
     * than quietly ignored.
     */
    public function testProcessPatchRejectsAChangeOfCompany(): void
    {
        // Arrange
        $processor = $this->createProcessor();

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->createResource(['companyUuid' => static::OTHER_COMPANY_UUID]),
                $this->tester->getPatchOperation(),
                ['uuid' => static::ROLE_UUID],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    public function testProcessPatchAcceptsTheCompanyItAlreadyBelongsTo(): void
    {
        // Arrange
        $capturedCompanyRoleTransfer = null;
        $processor = $this->createProcessor(capturedCompanyRole: $capturedCompanyRoleTransfer);

        // Act
        $processor->process(
            $this->createResource(['name' => 'Renamed', 'companyUuid' => static::COMPANY_UUID]),
            $this->tester->getPatchOperation(),
            ['uuid' => static::ROLE_UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertSame('Renamed', $this->getCaptured($capturedCompanyRoleTransfer)->getName());
    }

    public function testProcessDeleteReturnsNullOnSuccess(): void
    {
        // Arrange
        $processor = $this->createProcessor();

        // Act
        $result = $processor->process(
            null,
            $this->tester->getDeleteOperation(),
            ['uuid' => static::ROLE_UUID],
            $this->tester->getContext()->toArray(),
        );

        // Assert
        $this->assertNull($result);
    }

    /**
     * The facade refuses to delete a company's default role; the processor must surface that as the
     * dedicated error code rather than the generic validation one.
     */
    public function testProcessDeleteSurfacesTheDefaultRoleRefusalAsItsOwnErrorCode(): void
    {
        // Arrange
        $processor = $this->createProcessor(deleteResponse: (new CompanyRoleResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage(
                (new ResponseMessageTransfer())->setText('The default company role cannot be deleted.'),
            ));

        // Act & Assert
        try {
            $processor->process(
                null,
                $this->tester->getDeleteOperation(),
                ['uuid' => static::ROLE_UUID],
                $this->tester->getContext()->toArray(),
            );

            $this->fail('Deleting a default company role must not succeed.');
        } catch (GlueApiException $glueApiException) {
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_IS_DEFAULT,
                $glueApiException->getErrorCode(),
            );
        }
    }

    public function testProcessDeleteSurfacesTheAssignedCompanyUsersRefusalAsItsOwnErrorCode(): void
    {
        // Arrange
        $processor = $this->createProcessor(deleteResponse: (new CompanyRoleResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage(
                (new ResponseMessageTransfer())->setText('company.company_role.delete.error.has_users'),
            ));

        // Act & Assert
        try {
            $processor->process(
                null,
                $this->tester->getDeleteOperation(),
                ['uuid' => static::ROLE_UUID],
                $this->tester->getContext()->toArray(),
            );

            $this->fail('Deleting a company role that still has company users assigned must not succeed.');
        } catch (GlueApiException $glueApiException) {
            $this->assertSame(
                CustomerExperienceManagementConfig::RESPONSE_CODE_COMPANY_ROLE_HAS_COMPANY_USERS,
                $glueApiException->getErrorCode(),
            );
        }
    }

    public function testProcessPostSurfacesAFacadeValidationFailure(): void
    {
        // Arrange
        $processor = $this->createProcessor(createResponse: (new CompanyRoleResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new ResponseMessageTransfer())->setText('A company role with this name already exists in this company.')));

        // Act & Assert
        $this->tester->assertThrowsGlueApiExceptionWithStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            fn () => $processor->process(
                $this->createResource(['name' => 'Approver', 'companyUuid' => static::COMPANY_UUID]),
                $this->tester->getPostOperation(),
                [],
                $this->tester->getContext()->toArray(),
            ),
        );
    }

    protected function getCaptured(?CompanyRoleTransfer $companyRoleTransfer): CompanyRoleTransfer
    {
        $this->assertNotNull($companyRoleTransfer, 'The facade was never handed a company role.');

        return $companyRoleTransfer;
    }

    /**
     * @return \ArrayObject<int, \Generated\Shared\Transfer\PermissionTransfer>
     */
    protected function getCapturedPermissions(?CompanyRoleTransfer $companyRoleTransfer): ArrayObject
    {
        return $this->getCaptured($companyRoleTransfer)->getPermissionCollectionOrFail()->getPermissions();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function createResource(array $attributes): CompanyRolesBackendResource
    {
        return $this->tester->getResource(CompanyRolesBackendResource::class, $attributes);
    }

    protected function createProcessor(
        ?CompanyRoleResponseTransfer $createResponse = null,
        ?CompanyRoleResponseTransfer $deleteResponse = null,
        ?CompanyRoleTransfer &$capturedCompanyRole = null
    ): CompanyRolesBackendProcessor {
        $storedCompanyRoleTransfer = $this->createStoredCompanyRole();

        $capture = function (CompanyRoleTransfer $companyRoleTransfer) use (&$capturedCompanyRole, $storedCompanyRoleTransfer): CompanyRoleResponseTransfer {
            $capturedCompanyRole = $companyRoleTransfer;

            return (new CompanyRoleResponseTransfer())
                ->setIsSuccessful(true)
                ->setCompanyRoleTransfer($storedCompanyRoleTransfer);
        };

        $this->tester->setService(
            CompanyRoleFacadeInterface::class,
            $this->tester->createClientStub(CompanyRoleFacadeInterface::class, [
                'findCompanyRoleByUuid' => (new CompanyRoleResponseTransfer())
                    ->setIsSuccessful(true)
                    ->setCompanyRoleTransfer($storedCompanyRoleTransfer),
                'findCompanyRoleById' => $storedCompanyRoleTransfer,
                'findCompanyRolePermissions' => $this->createStoredPermissionCollection(),
                'createCompanyRole' => $createResponse ?? $capture,
                'updateCompanyRole' => $capture,
                'delete' => $deleteResponse ?? (new CompanyRoleResponseTransfer())->setIsSuccessful(true),
            ]),
        );

        $this->tester->setService(
            CompanyFacadeInterface::class,
            $this->tester->createClientStub(CompanyFacadeInterface::class, [
                'findCompanyByUuid' => (new CompanyResponseTransfer())
                    ->setIsSuccessful(true)
                    ->setCompanyTransfer(
                        (new CompanyTransfer())->setIdCompany(static::ID_COMPANY)->setUuid(static::COMPANY_UUID),
                    ),
            ]),
        );

        $this->tester->setService(
            PermissionFacadeInterface::class,
            $this->tester->createClientStub(PermissionFacadeInterface::class, [
                'findMergedRegisteredNonInfrastructuralPermissions' => $this->createStoredPermissionCollection(),
            ]),
        );

        return $this->tester->getProcessor(CompanyRolesBackendProcessor::class);
    }

    protected function createStoredCompanyRole(): CompanyRoleTransfer
    {
        return (new CompanyRoleTransfer())
            ->setIdCompanyRole(static::ID_COMPANY_ROLE)
            ->setUuid(static::ROLE_UUID)
            ->setName('Approver')
            ->setFkCompany(static::ID_COMPANY)
            ->setCompany((new CompanyTransfer())->setIdCompany(static::ID_COMPANY)->setUuid(static::COMPANY_UUID))
            ->setPermissionCollection($this->createStoredPermissionCollection());
    }

    protected function createStoredPermissionCollection(): PermissionCollectionTransfer
    {
        return (new PermissionCollectionTransfer())->addPermission(
            (new PermissionTransfer())
                ->setIdPermission(static::ID_PERMISSION)
                ->setKey('ApproveQuotePermissionPlugin'),
        );
    }
}
