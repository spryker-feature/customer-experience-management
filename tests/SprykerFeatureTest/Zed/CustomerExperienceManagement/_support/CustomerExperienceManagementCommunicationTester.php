<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement;

use Codeception\Actor;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\DataImporterReportTransfer;
use Orm\Zed\Company\Persistence\SpyCompany;
use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\Customer\Persistence\SpyCustomerQuery;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyBusinessUnitQuery;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyQuery;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use ReflectionClass;
use Spryker\Shared\Kernel\Container\GlobalContainer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyBusinessUnitCustomerGroupPostSavePlugin;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupPostSavePlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class CustomerExperienceManagementCommunicationTester extends Actor
{
    use _generated\CustomerExperienceManagementCommunicationTesterActions;

    protected const string COMPANY_STATUS_APPROVED = 'approved';

    protected const string COMPANY_NAME_PREFIX = 'customer-group-company-';

    protected const int TABLE_COLUMN_INDEX_ID = 0;

    /**
     * @uses \Spryker\Zed\Gui\Communication\Table\AbstractTable::getRequest()
     */
    protected const string SERVICE_REQUEST = 'request';

    /**
     * @uses \Spryker\Zed\Gui\Communication\Table\AbstractTable::SERVICE_TWIG
     */
    protected const string SERVICE_TWIG = 'twig';

    /**
     * @uses \Spryker\Zed\Gui\Communication\Table\AbstractTable::getSearchTerm()
     */
    protected const string PARAMETER_SEARCH = 'search';

    /**
     * @uses \Spryker\Zed\Gui\Communication\Table\AbstractTable::getOrderParameter()
     */
    protected const string PARAMETER_ORDER = 'order';

    protected const string TWIG_NAMESPACE_GUI = 'Gui';

    protected const string TWIG_FILTER_TRANS = 'trans';

    protected const string GUI_PRESENTATION_PATH_TEMPLATE = '%s/Presentation';

    public function setTableSearchRequest(string $searchTerm): void
    {
        $this->setTableRequest($this->createSearchQuery($searchTerm));
    }

    public function setSortedTableSearchRequest(string $searchTerm, int $orderColumnIndex, string $orderDirection): void
    {
        $this->setTableRequest($this->createSearchQuery($searchTerm) + [
            static::PARAMETER_ORDER => [[
                AbstractTable::SORT_BY_COLUMN => $orderColumnIndex,
                AbstractTable::SORT_BY_DIRECTION => $orderDirection,
            ]],
        ]);
    }

    /**
     * @param list<list<mixed>> $rows
     *
     * @return list<int>
     */
    public function getTableRowIds(array $rows): array
    {
        return array_map(fn (array $row): int => (int)$row[static::TABLE_COLUMN_INDEX_ID], $rows);
    }

    public function getCommunicationFactory(): CustomerExperienceManagementCommunicationFactory
    {
        /** @var \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory $customerExperienceManagementCommunicationFactory */
        $customerExperienceManagementCommunicationFactory = $this->getFactory();

        return $customerExperienceManagementCommunicationFactory;
    }

    public function setCustomerGroupPostSavePlugins(): void
    {
        $this->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE, [
            new CompanyCustomerGroupPostSavePlugin(),
            new CompanyBusinessUnitCustomerGroupPostSavePlugin(),
        ]);
    }

    public function setCustomerGroupPostSavePluginsWithRelationChunkSize(int $customerGroupRelationChunkSize): void
    {
        $customerExperienceManagementBusinessFactory = $this->createBusinessFactoryWithRelationChunkSize($customerGroupRelationChunkSize);

        $this->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE, [
            (new CompanyCustomerGroupPostSavePlugin())->setBusinessFactory($customerExperienceManagementBusinessFactory),
            (new CompanyBusinessUnitCustomerGroupPostSavePlugin())->setBusinessFactory($customerExperienceManagementBusinessFactory),
        ]);
    }

    public function createBusinessFactoryWithRelationChunkSize(int $customerGroupRelationChunkSize): CustomerExperienceManagementBusinessFactory
    {
        /** @var \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig $customerExperienceManagementConfig */
        $customerExperienceManagementConfig = $this->mockConfigMethod('getCustomerGroupRelationChunkSize', $customerGroupRelationChunkSize);

        /** @var \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementBusinessFactory $customerExperienceManagementBusinessFactory */
        $customerExperienceManagementBusinessFactory = (new CustomerExperienceManagementBusinessFactory())->setConfig($customerExperienceManagementConfig);

        return $customerExperienceManagementBusinessFactory;
    }

    public function getCustomerGroupFacade(): CustomerGroupFacadeInterface
    {
        return $this->getLocator()->customerGroup()->facade();
    }

    /**
     * @param array<string, mixed> $seed
     */
    public function haveApprovedCompany(array $seed = []): CompanyTransfer
    {
        return $this->haveCompany($seed + [
            CompanyTransfer::STATUS => static::COMPANY_STATUS_APPROVED,
            CompanyTransfer::IS_ACTIVE => true,
        ]);
    }

    public function haveCompanyWithoutDependentRecords(): CompanyTransfer
    {
        $companyEntity = (new SpyCompany())
            ->setName(uniqid(static::COMPANY_NAME_PREFIX, true))
            ->setStatus(static::COMPANY_STATUS_APPROVED)
            ->setIsActive(true);

        $companyEntity->save();

        return (new CompanyTransfer())->fromArray($companyEntity->toArray(), true);
    }

    /**
     * @param array<string, mixed> $seed
     */
    public function haveCompanyBusinessUnitOfCompany(CompanyTransfer $companyTransfer, array $seed = []): CompanyBusinessUnitTransfer
    {
        return $this->haveCompanyBusinessUnit($seed + [
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompanyOrFail(),
        ]);
    }

    public function haveCompanyUserInCompanyBusinessUnit(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer,
        bool $isActive = true
    ): CompanyUserTransfer {
        $customerTransfer = $this->haveCustomer();

        return $this->haveCompanyUser([
            CompanyUserTransfer::CUSTOMER => $customerTransfer,
            CompanyUserTransfer::FK_CUSTOMER => $customerTransfer->getIdCustomer(),
            CompanyUserTransfer::FK_COMPANY => $companyBusinessUnitTransfer->getFkCompany(),
            CompanyUserTransfer::FK_COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer->getIdCompanyBusinessUnit(),
            CompanyUserTransfer::IS_ACTIVE => $isActive,
        ]);
    }

    /**
     * @return list<int>
     */
    public function getAssignedCompanyIds(CustomerGroupTransfer $customerGroupTransfer): array
    {
        $companyIds = SpyCustomerGroupToCompanyQuery::create()
            ->filterByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
            ->orderByFkCompany()
            ->find()
            ->getColumnValues('FkCompany');

        return array_map('intval', $companyIds);
    }

    /**
     * @return list<int>
     */
    public function getAssignedCompanyBusinessUnitIds(CustomerGroupTransfer $customerGroupTransfer): array
    {
        $companyBusinessUnitIds = SpyCustomerGroupToCompanyBusinessUnitQuery::create()
            ->filterByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
            ->orderByFkCompanyBusinessUnit()
            ->find()
            ->getColumnValues('FkCompanyBusinessUnit');

        return array_map('intval', $companyBusinessUnitIds);
    }

    /**
     * @return array<int, int>
     */
    public function getCompanyMembershipIdsIndexedByIdCompany(CustomerGroupTransfer $customerGroupTransfer): array
    {
        $membershipIdsByIdCompany = [];

        foreach (SpyCustomerGroupToCompanyQuery::create()->filterByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())->find() as $customerGroupToCompanyEntity) {
            $membershipIdsByIdCompany[$customerGroupToCompanyEntity->getFkCompany()] = $customerGroupToCompanyEntity->getIdCustomerGroupToCompany();
        }

        return $membershipIdsByIdCompany;
    }

    /**
     * @return array<int, int>
     */
    public function getCompanyBusinessUnitMembershipIdsIndexedByIdCompanyBusinessUnit(CustomerGroupTransfer $customerGroupTransfer): array
    {
        $membershipIdsByIdCompanyBusinessUnit = [];

        foreach (SpyCustomerGroupToCompanyBusinessUnitQuery::create()->filterByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())->find() as $customerGroupToCompanyBusinessUnitEntity) {
            $membershipIdsByIdCompanyBusinessUnit[$customerGroupToCompanyBusinessUnitEntity->getFkCompanyBusinessUnit()] = $customerGroupToCompanyBusinessUnitEntity->getIdCustomerGroupToCompanyBusinessUnit();
        }

        return $membershipIdsByIdCompanyBusinessUnit;
    }

    /**
     * @return array<int, string>
     */
    public function getDataImporterReportMessagesByDataSetNumber(DataImporterReportTransfer $dataImporterReportTransfer): array
    {
        $messages = [];

        foreach ($dataImporterReportTransfer->getMessages() as $dataImporterReportMessageTransfer) {
            $messages[(int)$dataImporterReportMessageTransfer->getDataSetNumber()] = (string)$dataImporterReportMessageTransfer->getMessage();
        }

        return $messages;
    }

    public function hasCustomerGroupNamed(string $customerGroupName): bool
    {
        return SpyCustomerGroupQuery::create()->filterByName($customerGroupName)->exists();
    }

    public function findCustomerGroupEntityByName(string $customerGroupName): ?SpyCustomerGroup
    {
        return SpyCustomerGroupQuery::create()->filterByName($customerGroupName)->findOne();
    }

    /**
     * @return list<int>
     */
    public function getCustomerGroupIdsByNameIgnoringCase(string $customerGroupName): array
    {
        $customerGroupIds = SpyCustomerGroupQuery::create()
            ->where(sprintf('LOWER(%s) = ?', SpyCustomerGroupTableMap::COL_NAME), mb_strtolower($customerGroupName))
            ->orderByIdCustomerGroup()
            ->find()
            ->getColumnValues('IdCustomerGroup');

        return array_map('intval', $customerGroupIds);
    }

    /**
     * @return list<int>
     */
    public function getAssignedCustomerIds(CustomerGroupTransfer $customerGroupTransfer): array
    {
        $customerIds = SpyCustomerGroupToCustomerQuery::create()
            ->filterByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail())
            ->orderByFkCustomer()
            ->find()
            ->getColumnValues('FkCustomer');

        return array_map('intval', $customerIds);
    }

    public function hasCustomerWithReference(string $customerReference): bool
    {
        return SpyCustomerQuery::create()->filterByCustomerReference($customerReference)->exists();
    }

    public function hasCompanyWithKey(string $companyKey): bool
    {
        return SpyCompanyQuery::create()->filterByKey($companyKey)->exists();
    }

    public function hasCompanyBusinessUnitWithKey(string $companyBusinessUnitKey): bool
    {
        return SpyCompanyBusinessUnitQuery::create()->filterByKey($companyBusinessUnitKey)->exists();
    }

    /**
     * @param array<string, mixed> $query
     */
    public function setTableRequest(array $query = []): void
    {
        $container = $this->getContainer();
        $container->set(static::SERVICE_REQUEST, new Request($query));
        $container->set(static::SERVICE_TWIG, $this->createTableTwigEnvironment());

        GlobalContainer::setContainer($container);
    }

    protected function createTableTwigEnvironment(): Environment
    {
        $guiTemplateLoader = new FilesystemLoader();
        $guiTemplateLoader->addPath($this->getGuiPresentationPath(), static::TWIG_NAMESPACE_GUI);

        $twigEnvironment = new Environment(new ChainLoader([$guiTemplateLoader]));
        $twigEnvironment->addFilter(new TwigFilter(static::TWIG_FILTER_TRANS, fn (string $message): string => $message));

        return $twigEnvironment;
    }

    protected function getGuiPresentationPath(): string
    {
        $abstractTablePath = (string)(new ReflectionClass(AbstractTable::class))->getFileName();

        return sprintf(static::GUI_PRESENTATION_PATH_TEMPLATE, dirname($abstractTablePath, 3));
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function createSearchQuery(string $searchTerm): array
    {
        return [static::PARAMETER_SEARCH => [AbstractTable::PARAMETER_VALUE => $searchTerm]];
    }
}
