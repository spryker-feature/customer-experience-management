<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Api\Backend\CompanyRolesBackendResource;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;

class CompanyRoleResourceMapper implements CompanyRoleResourceMapperInterface
{
    protected const string KEY_UUID = 'uuid';

    protected const string KEY_NAME = 'name';

    protected const string KEY_IS_DEFAULT = 'isDefault';

    protected const string KEY_COMPANY_UUID = 'companyUuid';

    protected const string KEY_COMPANY_NAME = 'companyName';

    protected const string KEY_PERMISSION_KEYS = 'permissionKeys';

    /**
     * @return array<string, mixed>
     */
    public function mapCompanyRoleTransferToResourceData(CompanyRoleTransfer $companyRoleTransfer): array
    {
        return [
            static::KEY_UUID => $companyRoleTransfer->getUuid(),
            static::KEY_NAME => $companyRoleTransfer->getName(),
            static::KEY_IS_DEFAULT => $companyRoleTransfer->getIsDefault(),
            static::KEY_COMPANY_UUID => $companyRoleTransfer->getCompany()?->getUuid(),
            static::KEY_COMPANY_NAME => $companyRoleTransfer->getCompany()?->getName(),
            static::KEY_PERMISSION_KEYS => $this->extractPermissionKeys($companyRoleTransfer->getPermissionCollection() ?? new PermissionCollectionTransfer()),
        ];
    }

    public function mapResourceToCompanyRoleTransfer(
        CompanyRolesBackendResource $companyRolesBackendResource,
        CompanyRoleTransfer $companyRoleTransfer
    ): CompanyRoleTransfer {
        return $companyRoleTransfer
            ->setName($companyRolesBackendResource->name ?? $companyRoleTransfer->getName())
            ->setIsDefault($companyRolesBackendResource->isDefault ?? $companyRoleTransfer->getIsDefault());
    }

    /**
     * @return list<string>
     */
    protected function extractPermissionKeys(PermissionCollectionTransfer $permissionCollectionTransfer): array
    {
        $permissionKeys = [];

        foreach ($permissionCollectionTransfer->getPermissions() as $permissionTransfer) {
            $key = $permissionTransfer->getKey();

            if ($key !== null) {
                $permissionKeys[] = $key;
            }
        }

        return $permissionKeys;
    }
}
