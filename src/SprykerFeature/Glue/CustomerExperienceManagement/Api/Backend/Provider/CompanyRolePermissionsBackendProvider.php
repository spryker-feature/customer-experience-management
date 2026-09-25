<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CompanyRolePermissionsBackendResource;
use Generated\Shared\Transfer\PermissionTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\PermissionResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\PermissionReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Resolver\PermissionNameResolverInterface;

class CompanyRolePermissionsBackendProvider extends AbstractBackendProvider
{
    protected const string QUERY_PARAM_FILTER = 'filter';

    protected const string FILTER_KEY_PREFIX = 'company-role-permissions.';

    protected const string FILTER_NAME = 'name';

    protected const string FILTER_LOCALE_NAME = 'localeName';

    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected PermissionReaderInterface $permissionReader,
        protected PermissionResourceMapperInterface $permissionResourceMapper,
        protected PermissionNameResolverInterface $permissionNameResolver,
    ) {
    }

    /**
     * @return array<\Generated\Api\Backend\CompanyRolePermissionsBackendResource>
     */
    protected function provideCollection(): array
    {
        $permissionCollectionTransfer = $this->permissionReader->getAvailablePermissionCollection();
        $localizedNamesByPermissionKey = $this->permissionNameResolver
            ->resolveLocalizedNamesByPermissionKey($permissionCollectionTransfer);

        $permissionTransfers = $this->filterByName(
            array_values(iterator_to_array($permissionCollectionTransfer->getPermissions())),
            $localizedNamesByPermissionKey,
        );

        $paginationTransfer = $this->buildPaginationTransfer();
        $offset = $paginationTransfer->getOffsetOrFail();
        $limit = $paginationTransfer->getLimitOrFail();

        $resources = [];

        foreach (array_slice($permissionTransfers, $offset, $limit) as $permissionTransfer) {
            $resources[] = $this->buildResource(
                $permissionTransfer,
                $localizedNamesByPermissionKey[(string)$permissionTransfer->getKey()] ?? [],
            );
        }

        $this->setCollectionPagination($offset, $limit, count($permissionTransfers));

        return $resources;
    }

    /**
     * @param list<\Generated\Shared\Transfer\PermissionTransfer> $permissionTransfers
     * @param array<string, array<string, string>> $localizedNamesByPermissionKey
     *
     * @return list<\Generated\Shared\Transfer\PermissionTransfer>
     */
    protected function filterByName(array $permissionTransfers, array $localizedNamesByPermissionKey): array
    {
        $filters = $this->extractFiltersFromRequest();
        $nameFilter = $this->findFilterValue($filters, static::FILTER_NAME);

        if ($nameFilter === null) {
            return $permissionTransfers;
        }

        $localeName = $this->findFilterValue($filters, static::FILTER_LOCALE_NAME);

        return array_values(array_filter(
            $permissionTransfers,
            function (PermissionTransfer $permissionTransfer) use ($localizedNamesByPermissionKey, $nameFilter, $localeName): bool {
                $localizedNames = $localizedNamesByPermissionKey[(string)$permissionTransfer->getKey()] ?? [];

                if ($localeName !== null) {
                    $localizedNames = array_key_exists($localeName, $localizedNames)
                        ? [$localizedNames[$localeName]]
                        : [];
                }

                foreach ($localizedNames as $name) {
                    if (mb_stripos($name, $nameFilter) !== false) {
                        return true;
                    }
                }

                return false;
            },
        ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractFiltersFromRequest(): array
    {
        $filters = [];

        foreach ($this->getRequest()->query->all(static::QUERY_PARAM_FILTER) as $key => $value) {
            $property = str_starts_with($key, static::FILTER_KEY_PREFIX)
                ? substr($key, strlen(static::FILTER_KEY_PREFIX))
                : $key;

            $filters[$property] = $value;
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function findFilterValue(array $filters, string $name): ?string
    {
        $value = $filters[$name] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param array<string, string> $localizedNames
     */
    protected function buildResource(PermissionTransfer $permissionTransfer, array $localizedNames): CompanyRolePermissionsBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyRolePermissionsBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->permissionResourceMapper->mapPermissionTransferToResourceData($permissionTransfer, $localizedNames),
            CompanyRolePermissionsBackendResource::class,
        );

        return $resource;
    }
}
