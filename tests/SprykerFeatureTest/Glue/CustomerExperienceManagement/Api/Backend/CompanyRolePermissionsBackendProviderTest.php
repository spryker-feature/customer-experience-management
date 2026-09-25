<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\GetCollection;
use Generated\Api\Backend\CompanyRolePermissionsBackendResource;
use Generated\Shared\Transfer\GlossaryKeyTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Generated\Shared\Transfer\PermissionTransfer;
use Generated\Shared\Transfer\TranslationTransfer;
use Spryker\ApiPlatform\ResponseTransform\PaginationLinksTransform;
use Spryker\Zed\Glossary\Business\GlossaryFacadeInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;
use Spryker\Zed\Permission\Business\PermissionFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider\CompanyRolePermissionsBackendProvider;
use SprykerFeatureTest\Glue\CustomerExperienceManagement\CustomerExperienceManagementApiTester;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyRolePermissionsBackendProviderTest
 * Add your own group annotations below this line
 */
class CompanyRolePermissionsBackendProviderTest extends BackendApiTestCase
{
    /**
     * @var int
     */
    protected const ITEMS_PER_PAGE = 50;

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
    protected const GLOSSARY_KEY_PREFIX = 'permission.name.';

    /**
     * @var string
     */
    protected const LOCALE_EN = 'en_US';

    /**
     * @var string
     */
    protected const LOCALE_DE = 'de_DE';

    /**
     * @var array<string, array<string, string>>
     */
    protected const array LOCALIZED_NAMES = [
        'AddCompanyUserPermissionPlugin' => [
            'en_US' => 'Add company users',
            'de_DE' => 'Firmennutzer hinzufügen',
        ],
        'ApproveQuotePermissionPlugin' => [
            'en_US' => 'Approve quotes',
            'de_DE' => 'Angebote genehmigen',
        ],
        'ReadSharedCartPermissionPlugin' => [
            'en_US' => 'Read shared carts',
            'de_DE' => 'Geteilte Warenkörbe lesen',
        ],
    ];

    protected CustomerExperienceManagementApiTester $tester;

    public function testProvideCollectionReturnsEveryAvailablePermission(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide($this->createGetCollectionOperation(), [], $this->createContext());

        // Assert
        $this->assertCount(3, $resources);
        $this->assertInstanceOf(CompanyRolePermissionsBackendResource::class, $resources[0]);
    }

    public function testProvideCollectionReturnsANameForEveryAvailableLocale(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide($this->createGetCollectionOperation(), [], $this->createContext());

        // Assert
        $this->assertSame(
            [
                ['localeName' => static::LOCALE_EN, 'name' => 'Add company users'],
                ['localeName' => static::LOCALE_DE, 'name' => 'Firmennutzer hinzufügen'],
            ],
            $this->toArray($resources[0]->localizedNames),
        );
    }

    public function testProvideCollectionFiltersByNameInAnyLocaleWhenNoLocaleIsGiven(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(['filter' => ['company-role-permissions.name' => 'genehmigen']]),
        );

        // Assert
        $this->assertCount(1, $resources);
        $this->assertSame('Approve quotes', $this->toArray($resources[0]->localizedNames)[0]['name']);
    }

    /**
     * The locale is a filter of its own rather than the request locale, so narrowing by a locale the
     * term does not appear in must exclude the permission even though another locale matches.
     */
    public function testProvideCollectionFiltersByNameWithinTheRequestedLocaleOnly(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext([
                'filter' => ['company-role-permissions.name' => 'genehmigen', 'company-role-permissions.localeName' => static::LOCALE_EN],
            ]),
        );

        // Assert
        $this->assertSame([], $resources);
    }

    public function testProvideCollectionMatchesTheNameFilterCaseInsensitively(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext([
                'filter' => ['company-role-permissions.name' => 'ADD COMPANY', 'company-role-permissions.localeName' => static::LOCALE_EN],
            ]),
        );

        // Assert
        $this->assertCount(1, $resources);
    }

    public function testProvideCollectionMatchesNothingForAnUnknownLocale(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext([
                'filter' => ['company-role-permissions.name' => 'Add company', 'company-role-permissions.localeName' => 'xx_XX'],
            ]),
        );

        // Assert
        $this->assertSame([], $resources);
    }

    public function testProvideCollectionAppliesThePageWindow(): void
    {
        // Arrange
        $provider = $this->createProvider();

        // Act
        $resources = $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->createContext(['page' => ['offset' => 1, 'limit' => 1]]),
        );

        // Assert
        $this->assertCount(1, $resources);
        $this->assertSame('Approve quotes', $this->toArray($resources[0]->localizedNames)[0]['name']);
    }

    /**
     * The total is the size of the filtered set, not of the page, so a client can tell how many
     * pages follow.
     */
    public function testProvideCollectionPublishesTheFilteredTotalForThePaginationLinks(): void
    {
        // Arrange
        $request = new Request(['page' => ['offset' => 0, 'limit' => 2]]);
        $provider = $this->createProvider();

        // Act
        $provider->provide(
            $this->createGetCollectionOperation(),
            [],
            $this->tester->getContext(['request' => $request])->toArray(),
        );

        // Assert
        $pagination = $request->attributes->get(PaginationLinksTransform::REQUEST_ATTRIBUTE_PAGINATION);
        $this->assertSame(3, $pagination['numFound']);
    }

    /**
     * The real name resolver runs here — only its glossary and locale dependencies are stubbed — so
     * these tests cover the provider and the resolver together, which is where the name filter
     * actually lives.
     */
    protected function createProvider(): CompanyRolePermissionsBackendProvider
    {
        $permissionCollectionTransfer = new PermissionCollectionTransfer();

        foreach (array_keys(static::LOCALIZED_NAMES) as $index => $permissionKey) {
            $permissionCollectionTransfer->addPermission(
                (new PermissionTransfer())
                    ->setIdPermission($index + 1)
                    ->setKey($permissionKey),
            );
        }

        $this->tester->setService(
            PermissionFacadeInterface::class,
            $this->tester->createClientStub(PermissionFacadeInterface::class, [
                'findMergedRegisteredNonInfrastructuralPermissions' => $permissionCollectionTransfer,
            ]),
        );

        $this->tester->setService(
            LocaleFacadeInterface::class,
            $this->tester->createClientStub(LocaleFacadeInterface::class, [
                'getAvailableLocales' => [
                    static::ID_LOCALE_EN => static::LOCALE_EN,
                    static::ID_LOCALE_DE => static::LOCALE_DE,
                ],
            ]),
        );

        $this->tester->setService(
            GlossaryFacadeInterface::class,
            $this->tester->createClientStub(GlossaryFacadeInterface::class, [
                'getTranslationsByGlossaryKeysAndLocaleTransfers' => $this->createTranslations(),
            ]),
        );

        return $this->tester->getProvider(CompanyRolePermissionsBackendProvider::class);
    }

    /**
     * @return list<\Generated\Shared\Transfer\TranslationTransfer>
     */
    protected function createTranslations(): array
    {
        $translationTransfers = [];

        foreach (static::LOCALIZED_NAMES as $permissionKey => $namesByLocale) {
            foreach ($namesByLocale as $localeName => $name) {
                $translationTransfers[] = (new TranslationTransfer())
                    ->setFkLocale($localeName === static::LOCALE_EN ? static::ID_LOCALE_EN : static::ID_LOCALE_DE)
                    ->setValue($name)
                    ->setGlossaryKey(
                        (new GlossaryKeyTransfer())->setKey(static::GLOSSARY_KEY_PREFIX . $permissionKey),
                    );
            }
        }

        return $translationTransfers;
    }

    protected function createGetCollectionOperation(): GetCollection
    {
        return new GetCollection(
            class: CompanyRolePermissionsBackendResource::class,
            paginationItemsPerPage: static::ITEMS_PER_PAGE,
        );
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    protected function createContext(array $query = []): array
    {
        return $this->tester->getContext(['request' => new Request($query)])->toArray();
    }

    /**
     * @param array<int, mixed> $localizedNames
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toArray(array $localizedNames): array
    {
        return array_map(
            static fn (mixed $entry): array => (array)$entry,
            $localizedNames,
        );
    }
}
