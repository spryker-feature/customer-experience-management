<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Resolver;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Spryker\Zed\Glossary\Business\GlossaryFacadeInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;

class PermissionNameResolver implements PermissionNameResolverInterface
{
    /**
     * @uses \Spryker\Zed\CompanyRoleGui\Communication\Form\DataProvider\CompanyRoleCreateDataProvider::GLOSSARY_KEY_PREFIX_PERMISSION_NAME
     */
    protected const string GLOSSARY_KEY_PREFIX_PERMISSION_NAME = 'permission.name.';

    public function __construct(
        protected GlossaryFacadeInterface $glossaryFacade,
        protected LocaleFacadeInterface $localeFacade,
    ) {
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function resolveLocalizedNamesByPermissionKey(PermissionCollectionTransfer $permissionCollectionTransfer): array
    {
        $permissionKeys = $this->extractPermissionKeys($permissionCollectionTransfer);
        $localeNamesByIdLocale = $this->localeFacade->getAvailableLocales();

        if ($permissionKeys === [] || $localeNamesByIdLocale === []) {
            return [];
        }

        $translations = $this->findTranslations($permissionKeys, $localeNamesByIdLocale);

        $localizedNamesByPermissionKey = [];

        foreach ($permissionKeys as $permissionKey) {
            $glossaryKey = static::GLOSSARY_KEY_PREFIX_PERMISSION_NAME . $permissionKey;

            foreach ($localeNamesByIdLocale as $localeName) {
                $localizedNamesByPermissionKey[$permissionKey][$localeName]
                    = $translations[$glossaryKey][$localeName] ?? $permissionKey;
            }
        }

        return $localizedNamesByPermissionKey;
    }

    /**
     * @return list<string>
     */
    protected function extractPermissionKeys(PermissionCollectionTransfer $permissionCollectionTransfer): array
    {
        $permissionKeys = [];

        foreach ($permissionCollectionTransfer->getPermissions() as $permissionTransfer) {
            $permissionKey = $permissionTransfer->getKey();

            if ($permissionKey !== null) {
                $permissionKeys[] = $permissionKey;
            }
        }

        return $permissionKeys;
    }

    /**
     * @param list<string> $permissionKeys
     * @param array<int, string> $localeNamesByIdLocale
     *
     * @return array<string, array<string, string>> Glossary key => locale name => translation.
     */
    protected function findTranslations(array $permissionKeys, array $localeNamesByIdLocale): array
    {
        $glossaryKeys = array_map(
            static fn (string $permissionKey): string => static::GLOSSARY_KEY_PREFIX_PERMISSION_NAME . $permissionKey,
            $permissionKeys,
        );

        $translationTransfers = $this->glossaryFacade->getTranslationsByGlossaryKeysAndLocaleTransfers(
            $glossaryKeys,
            $this->buildLocaleTransfers($localeNamesByIdLocale),
        );

        $translations = [];

        foreach ($translationTransfers as $translationTransfer) {
            $glossaryKeyTransfer = $translationTransfer->getGlossaryKey();
            $localeName = $localeNamesByIdLocale[$translationTransfer->getFkLocale()] ?? null;

            if ($glossaryKeyTransfer === null || $localeName === null || $translationTransfer->getValue() === null) {
                continue;
            }

            $translations[$glossaryKeyTransfer->getKeyOrFail()][$localeName] = $translationTransfer->getValue();
        }

        return $translations;
    }

    /**
     * @param array<int, string> $localeNamesByIdLocale
     *
     * @return list<\Generated\Shared\Transfer\LocaleTransfer>
     */
    protected function buildLocaleTransfers(array $localeNamesByIdLocale): array
    {
        $localeTransfers = [];

        foreach ($localeNamesByIdLocale as $idLocale => $localeName) {
            $localeTransfers[] = (new LocaleTransfer())
                ->setIdLocale($idLocale)
                ->setLocaleName($localeName);
        }

        return $localeTransfers;
    }
}
