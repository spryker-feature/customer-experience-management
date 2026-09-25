<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Shared\Transfer\PermissionTransfer;

class PermissionResourceMapper implements PermissionResourceMapperInterface
{
    protected const string KEY_KEY = 'key';

    protected const string KEY_LOCALIZED_NAMES = 'localizedNames';

    protected const string KEY_LOCALE_NAME = 'localeName';

    protected const string KEY_NAME = 'name';

    /**
     * @param array<string, string> $localizedNames
     *
     * @return array<string, mixed>
     */
    public function mapPermissionTransferToResourceData(PermissionTransfer $permissionTransfer, array $localizedNames): array
    {
        $localizedNameEntries = [];

        foreach ($localizedNames as $localeName => $name) {
            $localizedNameEntries[] = [
                static::KEY_LOCALE_NAME => $localeName,
                static::KEY_NAME => $name,
            ];
        }

        return [
            static::KEY_KEY => $permissionTransfer->getKey(),
            static::KEY_LOCALIZED_NAMES => $localizedNameEntries,
        ];
    }
}
