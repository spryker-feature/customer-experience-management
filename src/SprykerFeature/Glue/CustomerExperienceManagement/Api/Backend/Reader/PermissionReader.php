<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Generated\Shared\Transfer\PermissionTransfer;
use Spryker\Zed\Permission\Business\PermissionFacadeInterface;

class PermissionReader implements PermissionReaderInterface
{
    public function __construct(
        protected PermissionFacadeInterface $permissionFacade,
    ) {
    }

    public function getAvailablePermissionCollection(): PermissionCollectionTransfer
    {
        return $this->permissionFacade->findMergedRegisteredNonInfrastructuralPermissions();
    }

    /**
     * @param array<int|string, mixed> $permissionKeys
     */
    public function resolvePermissionCollectionByKeys(array $permissionKeys): PermissionCollectionTransfer
    {
        $permissionCollectionTransfer = new PermissionCollectionTransfer();
        $availablePermissionTransfersByKey = $this->indexAvailablePermissionsByKey();

        foreach ($permissionKeys as $permissionKey) {
            $permissionKey = (string)$permissionKey;

            $permissionCollectionTransfer->addPermission(
                $availablePermissionTransfersByKey[$permissionKey]
                    ?? (new PermissionTransfer())->setKey($permissionKey),
            );
        }

        return $permissionCollectionTransfer;
    }

    /**
     * The available set is a plugin-stack intersection rather than a query, so it is resolved once
     * and indexed here. Reading it per requested key would re-merge the whole permission stack for
     * every entry of the payload.
     *
     * @return array<string, \Generated\Shared\Transfer\PermissionTransfer>
     */
    protected function indexAvailablePermissionsByKey(): array
    {
        $availablePermissionTransfersByKey = [];

        foreach ($this->getAvailablePermissionCollection()->getPermissions() as $permissionTransfer) {
            $key = $permissionTransfer->getKey();

            if ($key !== null) {
                $availablePermissionTransfersByKey[$key] = $permissionTransfer;
            }
        }

        return $availablePermissionTransfersByKey;
    }
}
