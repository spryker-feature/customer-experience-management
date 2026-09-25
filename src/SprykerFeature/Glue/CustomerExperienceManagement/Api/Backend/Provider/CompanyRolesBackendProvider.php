<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CompanyRolesBackendResource;
use Generated\Shared\Transfer\CompanyRoleCollectionCriteriaTransfer;
use Generated\Shared\Transfer\CompanyRoleConditionsTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\CompanyRole\Business\CompanyRoleFacadeInterface;
use Spryker\Zed\CompanyRole\CompanyRoleConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyRolesBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyRoleResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyRoleReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;

class CompanyRolesBackendProvider extends AbstractBackendProvider
{
    use UuidUriVariableAwareTrait;

    protected const string QUERY_PARAM_FILTER = 'filter';

    /**
     * Required by the JSON:API convention, which addresses filters as `filter[<resourceName>.<property>]`.
     */
    protected const string FILTER_KEY_PREFIX = 'company-roles.';

    protected const string QUERY_PARAM_SEARCH = 'q';

    protected const string FILTER_NAME = 'name';

    protected const string FILTER_COMPANY_UUID = 'companyUuid';

    protected const string FILTER_IS_DEFAULT = 'isDefault';

    protected const string FILTER_COMPANY_NAME = 'companyName';

    /**
     * The allow list `filter[company-roles.<property>]` is validated against. An unsupported field
     * is refused rather than ignored, so a typo cannot read as an unfiltered collection.
     *
     * @var list<string>
     */
    protected const array SUPPORTED_FILTER_FIELDS = [
        self::FILTER_NAME,
        self::FILTER_COMPANY_UUID,
        self::FILTER_IS_DEFAULT,
        self::FILTER_COMPANY_NAME,
    ];

    public function __construct(
        protected CompanyRoleFacadeInterface $companyRoleFacade,
        protected SerializerServiceInterface $serializer,
        protected CompanyRoleReaderInterface $companyRoleReader,
        protected CompanyRoleResourceMapperInterface $companyRoleResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CompanyRoleConfig $companyRoleConfig,
        protected CompanyRolesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    protected function provideItem(): ?object
    {
        return $this->buildResource(
            $this->companyRoleReader->getCompanyRoleByUuid($this->getUuidUriVariable()),
        );
    }

    /**
     * @return array<\Generated\Api\Backend\CompanyRolesBackendResource>
     */
    protected function provideCollection(): array
    {
        $paginationTransfer = $this->buildPaginationTransfer();

        $companyRoleCollectionCriteriaTransfer = (new CompanyRoleCollectionCriteriaTransfer())
            ->setCompanyRoleConditions($this->buildConditionsFromRequest())
            ->setPagination($paginationTransfer);

        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->companyRoleConfig->getCompanyRoleCollectionSortableFieldMap()),
        );

        foreach ($sortTransfers as $sortTransfer) {
            $companyRoleCollectionCriteriaTransfer->addSort($sortTransfer);
        }

        $companyRoleCollectionTransfer = $this->companyRoleFacade->getCompanyRoleCollectionByCollectionCriteria(
            $companyRoleCollectionCriteriaTransfer,
        );

        $resources = [];

        foreach ($companyRoleCollectionTransfer->getRoles() as $companyRoleTransfer) {
            $resources[] = $this->buildResource($companyRoleTransfer);
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $companyRoleCollectionTransfer->getPagination()?->getNbResults() ?? 0,
        );

        return $resources;
    }

    protected function buildConditionsFromRequest(): CompanyRoleConditionsTransfer
    {
        $companyRoleConditionsTransfer = new CompanyRoleConditionsTransfer();
        $filters = $this->extractFiltersFromRequest();

        $this->assertFiltersAreSupported($filters);

        if (!empty($filters[static::FILTER_COMPANY_UUID])) {
            $companyRoleConditionsTransfer->addCompanyUuid((string)$filters[static::FILTER_COMPANY_UUID]);
        }

        if (isset($filters[static::FILTER_IS_DEFAULT])) {
            $companyRoleConditionsTransfer->setIsDefault(
                filter_var($filters[static::FILTER_IS_DEFAULT], FILTER_VALIDATE_BOOLEAN),
            );
        }

        if (!empty($filters[static::FILTER_NAME])) {
            $companyRoleConditionsTransfer->setName((string)$filters[static::FILTER_NAME]);
        }

        if (!empty($filters[static::FILTER_COMPANY_NAME])) {
            $companyRoleConditionsTransfer->setCompanyName((string)$filters[static::FILTER_COMPANY_NAME]);
        }

        $searchTerm = $this->findSearchTerm();

        if ($searchTerm !== null) {
            $companyRoleConditionsTransfer->setSearchTerm($searchTerm);
        }

        return $companyRoleConditionsTransfer;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertFiltersAreSupported(array $filters): void
    {
        foreach ($filters as $property => $value) {
            if (!in_array($property, static::SUPPORTED_FILTER_FIELDS, true)) {
                throw $this->exceptionFactory->createUnsupportedFilterFieldException(
                    $property,
                    static::SUPPORTED_FILTER_FIELDS,
                );
            }

            if (!is_scalar($value)) {
                throw $this->exceptionFactory->createNonScalarFilterValueException($property);
            }
        }
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

    protected function findSearchTerm(): ?string
    {
        $searchQuery = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);

        return is_string($searchQuery) && $searchQuery !== '' ? $searchQuery : null;
    }

    protected function buildResource(CompanyRoleTransfer $companyRoleTransfer): CompanyRolesBackendResource
    {
        /** @var \Generated\Api\Backend\CompanyRolesBackendResource $resource */
        $resource = $this->serializer->denormalize(
            $this->companyRoleResourceMapper->mapCompanyRoleTransferToResourceData($companyRoleTransfer),
            CompanyRolesBackendResource::class,
        );

        return $resource;
    }
}
