<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Provider;

use Generated\Api\Backend\CustomersBackendResource;
use Generated\Shared\Transfer\CustomerCollectionCriteriaTransfer;
use Generated\Shared\Transfer\CustomerConditionsTransfer;
use Generated\Shared\Transfer\CustomerCriteriaSearchTermsTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\Customer\CustomerConfig;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomersBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CustomerResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReaderInterface;

class CustomersBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    protected const string QUERY_PARAM_FILTER = 'filter';

    /**
     * Required by the JSON:API convention, which addresses filters as `filter[<resourceName>.<property>]`.
     */
    protected const string FILTER_KEY_PREFIX = 'customers.';

    protected const string QUERY_PARAM_SEARCH = 'q';

    protected const string FILTER_EMAIL = 'email';

    protected const string FILTER_CUSTOMER_REFERENCE = 'customerReference';

    protected const string FILTER_FIRST_NAME = 'firstName';

    protected const string FILTER_LAST_NAME = 'lastName';

    protected const string FILTER_INCLUDE_ANONYMIZED = 'includeAnonymized';

    public function __construct(
        protected CustomerFacadeInterface $customerFacade,
        protected SerializerServiceInterface $serializer,
        protected CustomersBackendExceptionFactory $exceptionFactory,
        protected CustomerResourceMapperInterface $customerResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected CustomerConfig $customerConfig,
    ) {
    }

    protected function provideItem(): ?object
    {
        $customerReference = (string)$this->getUriVariable(static::URI_VARIABLE_CUSTOMER_REFERENCE);

        $customersBackendResource = $this->getCustomerByReference($customerReference);

        if ($customersBackendResource === null) {
            throw $this->exceptionFactory->createCustomerNotFoundException($customerReference);
        }

        return $customersBackendResource;
    }

    protected function getCustomerByReference(string $customerReference): ?CustomersBackendResource
    {
        $customerResponseTransfer = $this->customerFacade->findCustomerByReference($customerReference);

        if (!$customerResponseTransfer->getHasCustomer()) {
            return null;
        }

        return $this->buildCustomersBackendResource($customerResponseTransfer->getCustomerTransferOrFail());
    }

    protected function buildCustomersBackendResource(CustomerTransfer $customerTransfer): CustomersBackendResource
    {
        /** @var \Generated\Api\Backend\CustomersBackendResource $customersBackendResource */
        $customersBackendResource = $this->serializer->denormalize(
            $this->customerResourceMapper->mapCustomerTransferToResourceData($customerTransfer),
            CustomersBackendResource::class,
        );

        return $customersBackendResource;
    }

    /**
     * @return array<\Generated\Api\Backend\CustomersBackendResource>
     */
    protected function provideCollection(): array
    {
        $paginationTransfer = $this->buildPaginationTransfer();

        $customerCollectionCriteriaTransfer = (new CustomerCollectionCriteriaTransfer())
            ->setCustomerConditions($this->buildConditionsFromRequest())
            ->setPagination($paginationTransfer);

        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $this->getRequest(),
            array_keys($this->customerConfig->getCustomerCollectionSortableFieldMap()),
        );

        foreach ($sortTransfers as $sortTransfer) {
            $customerCollectionCriteriaTransfer->addSort($sortTransfer);
        }

        $customerCollectionTransfer = $this->customerFacade->getCustomerCollectionByCollectionCriteria(
            $customerCollectionCriteriaTransfer,
        );

        $customersBackendResources = [];

        foreach ($customerCollectionTransfer->getCustomers() as $customerTransfer) {
            $customersBackendResources[] = $this->buildCustomersBackendResource($customerTransfer);
        }

        $nbResults = $customerCollectionTransfer->getPagination()?->getNbResults();
        if ($nbResults !== null) {
            $this->setCollectionPagination(
                $paginationTransfer->getOffsetOrFail(),
                $paginationTransfer->getLimitOrFail(),
                $nbResults,
            );
        }

        return $customersBackendResources;
    }

    protected function buildConditionsFromRequest(): CustomerConditionsTransfer
    {
        $customerConditionsTransfer = new CustomerConditionsTransfer();
        $filters = $this->extractFiltersFromRequest();

        if (!empty($filters[static::FILTER_CUSTOMER_REFERENCE])) {
            $customerConditionsTransfer->addCustomerReference((string)$filters[static::FILTER_CUSTOMER_REFERENCE]);
        }

        if (!empty($filters[static::FILTER_EMAIL])) {
            $customerConditionsTransfer->addEmail((string)$filters[static::FILTER_EMAIL]);
        }

        if (!empty($filters[static::FILTER_INCLUDE_ANONYMIZED])) {
            $customerConditionsTransfer->setHasAnonymizedAt(true);
        }

        $customerCriteriaSearchTermsTransfer = $this->buildSearchTerms($filters);

        if ($customerCriteriaSearchTermsTransfer !== null) {
            $customerConditionsTransfer->setSearchTerms($customerCriteriaSearchTermsTransfer);
        }

        return $customerConditionsTransfer;
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
    protected function buildSearchTerms(array $filters): ?CustomerCriteriaSearchTermsTransfer
    {
        $searchQuery = $this->getRequest()->query->get(static::QUERY_PARAM_SEARCH);
        $search = is_string($searchQuery) && $searchQuery !== '' ? $searchQuery : null;
        $firstName = !empty($filters[static::FILTER_FIRST_NAME]) ? (string)$filters[static::FILTER_FIRST_NAME] : null;
        $lastName = !empty($filters[static::FILTER_LAST_NAME]) ? (string)$filters[static::FILTER_LAST_NAME] : null;

        if ($search !== null) {
            return (new CustomerCriteriaSearchTermsTransfer())
                ->setEmail($search)
                ->setFirstName($search)
                ->setLastName($search);
        }

        if ($firstName === null && $lastName === null) {
            return null;
        }

        return (new CustomerCriteriaSearchTermsTransfer())
            ->setFirstName($firstName)
            ->setLastName($lastName);
    }
}
