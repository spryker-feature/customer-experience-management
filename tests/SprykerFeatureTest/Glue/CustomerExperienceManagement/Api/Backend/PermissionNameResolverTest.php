<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Stub;
use Generated\Shared\Transfer\GlossaryKeyTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Generated\Shared\Transfer\PermissionTransfer;
use Generated\Shared\Transfer\TranslationTransfer;
use Spryker\Zed\Glossary\Business\GlossaryFacadeInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Resolver\PermissionNameResolver;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group PermissionNameResolverTest
 * Add your own group annotations below this line
 */
class PermissionNameResolverTest extends BackendApiTestCase
{
    /**
     * @var string
     */
    protected const PERMISSION_KEY_TRANSLATED = 'AddCompanyUserPermissionPlugin';

    /**
     * @var string
     */
    protected const PERMISSION_KEY_UNTRANSLATED = 'ReadSharedCartPermissionPlugin';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_PREFIX = 'permission.name.';

    /**
     * @var int
     */
    protected const ID_LOCALE_EN = 66;

    /**
     * @var int
     */
    protected const ID_LOCALE_DE = 46;

    /**
     * @var string
     */
    protected const LOCALE_EN = 'en_US';

    /**
     * @var string
     */
    protected const LOCALE_DE = 'de_DE';

    public function testResolvesANameForEveryAvailableLocale(): void
    {
        // Arrange
        $resolver = $this->createResolver([
            $this->createTranslation(static::PERMISSION_KEY_TRANSLATED, static::ID_LOCALE_EN, 'Add company users'),
            $this->createTranslation(static::PERMISSION_KEY_TRANSLATED, static::ID_LOCALE_DE, 'Firmennutzer hinzufügen'),
        ]);

        // Act
        $localizedNames = $resolver->resolveLocalizedNamesByPermissionKey(
            $this->createPermissionCollection([static::PERMISSION_KEY_TRANSLATED]),
        );

        // Assert
        $this->assertSame(
            [static::LOCALE_EN => 'Add company users', static::LOCALE_DE => 'Firmennutzer hinzufügen'],
            $localizedNames[static::PERMISSION_KEY_TRANSLATED],
        );
    }

    /**
     * Not every shipped permission carries a translation, and the resolver must answer for the whole
     * catalogue rather than fail on the first gap.
     */
    public function testFallsBackToThePermissionKeyInALocaleWithNoTranslation(): void
    {
        // Arrange
        $resolver = $this->createResolver([
            $this->createTranslation(static::PERMISSION_KEY_UNTRANSLATED, static::ID_LOCALE_EN, 'Read shared carts'),
        ]);

        // Act
        $localizedNames = $resolver->resolveLocalizedNamesByPermissionKey(
            $this->createPermissionCollection([static::PERMISSION_KEY_UNTRANSLATED]),
        );

        // Assert
        $this->assertSame('Read shared carts', $localizedNames[static::PERMISSION_KEY_UNTRANSLATED][static::LOCALE_EN]);
        $this->assertSame(
            static::PERMISSION_KEY_UNTRANSLATED,
            $localizedNames[static::PERMISSION_KEY_UNTRANSLATED][static::LOCALE_DE],
        );
    }

    /**
     * `GlossaryFacadeInterface::translate()` throws on a missing key and queries once per call, so
     * the resolver must never reach for it to fill a gap.
     */
    public function testNeverResolvesAMissingNameThroughTheTranslateCall(): void
    {
        // Arrange
        $glossaryFacade = Stub::makeEmpty(GlossaryFacadeInterface::class, [
            'getTranslationsByGlossaryKeysAndLocaleTransfers' => [],
            'translate' => function (): string {
                $this->fail('translate() must not be called: it throws on a missing key and queries per call.');
            },
        ]);

        $resolver = new PermissionNameResolver($glossaryFacade, $this->createLocaleFacadeStub());

        // Act
        $localizedNames = $resolver->resolveLocalizedNamesByPermissionKey(
            $this->createPermissionCollection([static::PERMISSION_KEY_UNTRANSLATED]),
        );

        // Assert
        $this->assertSame(
            [static::LOCALE_EN => static::PERMISSION_KEY_UNTRANSLATED, static::LOCALE_DE => static::PERMISSION_KEY_UNTRANSLATED],
            $localizedNames[static::PERMISSION_KEY_UNTRANSLATED],
        );
    }

    public function testResolvesEveryPermissionInASingleBulkLookup(): void
    {
        // Arrange
        $callCount = 0;
        $glossaryFacade = Stub::makeEmpty(GlossaryFacadeInterface::class, [
            'getTranslationsByGlossaryKeysAndLocaleTransfers' => function () use (&$callCount): array {
                $callCount++;

                return [];
            },
        ]);
        $resolver = new PermissionNameResolver($glossaryFacade, $this->createLocaleFacadeStub());

        // Act
        $resolver->resolveLocalizedNamesByPermissionKey(
            $this->createPermissionCollection([
                static::PERMISSION_KEY_TRANSLATED,
                static::PERMISSION_KEY_UNTRANSLATED,
            ]),
        );

        // Assert
        $this->assertSame(1, $callCount);
    }

    public function testReturnsNothingWhenThereAreNoPermissions(): void
    {
        // Arrange
        $resolver = $this->createResolver([]);

        // Act
        $localizedNames = $resolver->resolveLocalizedNamesByPermissionKey(new PermissionCollectionTransfer());

        // Assert
        $this->assertSame([], $localizedNames);
    }

    /**
     * @param list<\Generated\Shared\Transfer\TranslationTransfer> $translationTransfers
     */
    protected function createResolver(array $translationTransfers): PermissionNameResolver
    {
        $glossaryFacade = Stub::makeEmpty(GlossaryFacadeInterface::class, [
            'getTranslationsByGlossaryKeysAndLocaleTransfers' => $translationTransfers,
        ]);

        return new PermissionNameResolver($glossaryFacade, $this->createLocaleFacadeStub());
    }

    protected function createLocaleFacadeStub(): LocaleFacadeInterface
    {
        /** @var \Spryker\Zed\Locale\Business\LocaleFacadeInterface $stub */
        $stub = Stub::makeEmpty(LocaleFacadeInterface::class, [
            'getAvailableLocales' => [
                static::ID_LOCALE_EN => static::LOCALE_EN,
                static::ID_LOCALE_DE => static::LOCALE_DE,
            ],
        ]);

        return $stub;
    }

    protected function createTranslation(string $permissionKey, int $idLocale, string $value): TranslationTransfer
    {
        return (new TranslationTransfer())
            ->setFkLocale($idLocale)
            ->setValue($value)
            ->setGlossaryKey((new GlossaryKeyTransfer())->setKey(static::GLOSSARY_KEY_PREFIX . $permissionKey));
    }

    /**
     * @param list<string> $permissionKeys
     */
    protected function createPermissionCollection(array $permissionKeys): PermissionCollectionTransfer
    {
        $permissionCollectionTransfer = new PermissionCollectionTransfer();

        foreach ($permissionKeys as $permissionKey) {
            $permissionCollectionTransfer->addPermission((new PermissionTransfer())->setKey($permissionKey));
        }

        return $permissionCollectionTransfer;
    }
}
