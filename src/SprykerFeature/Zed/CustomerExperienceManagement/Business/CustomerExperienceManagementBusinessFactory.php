<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business;

use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\DataImport\Business\DataImportFactoryTrait;
use Spryker\Zed\DataImport\Business\Model\DataImporterInterface;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBroker;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetWriterInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CompanyBusinessUnitKeyToIdCompanyBusinessUnitStep;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CompanyKeyToIdCompanyStep;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CustomerGroupNameToIdCustomerGroupStep;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CustomerGroupWriterStep;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Step\CustomerReferenceToIdCustomerStep;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Writer\CustomerGroupCompanyBusinessUnitDataSetWriter;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Writer\CustomerGroupCompanyDataSetWriter;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Writer\CustomerGroupCustomerDataSetWriter;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Expander\CustomerGroupQueryJoinExpander;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Expander\CustomerGroupQueryJoinExpanderInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader\AssignedEntityNameReader;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader\AssignedEntityNameReaderInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader\CompanyUserCustomerGroupReader;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader\CompanyUserCustomerGroupReaderInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Resolver\CompanyBusinessUnitAncestorResolver;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Resolver\CompanyBusinessUnitAncestorResolverInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Writer\CustomerGroupCompanyBusinessUnitRelationWriter;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Writer\CustomerGroupCompanyBusinessUnitRelationWriterInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Writer\CustomerGroupCompanyRelationWriter;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\Writer\CustomerGroupCompanyRelationWriterInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementDependencyProvider;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface getEntityManager()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface getRepository()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig getConfig()
 */
class CustomerExperienceManagementBusinessFactory extends AbstractBusinessFactory
{
    use DataImportFactoryTrait;

    public function createCustomerGroupCompanyRelationWriter(): CustomerGroupCompanyRelationWriterInterface
    {
        return new CustomerGroupCompanyRelationWriter(
            $this->getEntityManager(),
            $this->getConfig(),
        );
    }

    public function createCustomerGroupCompanyBusinessUnitRelationWriter(): CustomerGroupCompanyBusinessUnitRelationWriterInterface
    {
        return new CustomerGroupCompanyBusinessUnitRelationWriter(
            $this->getEntityManager(),
            $this->getConfig(),
        );
    }

    public function createCompanyUserCustomerGroupReader(): CompanyUserCustomerGroupReaderInterface
    {
        return new CompanyUserCustomerGroupReader(
            $this->getRepository(),
            $this->createCompanyBusinessUnitAncestorResolver(),
        );
    }

    public function createCompanyBusinessUnitAncestorResolver(): CompanyBusinessUnitAncestorResolverInterface
    {
        return new CompanyBusinessUnitAncestorResolver();
    }

    public function getCustomerGroupDataImporter(
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface $dataImporter */
        $dataImporter = $this->getCsvDataImporter(
            $this->getConfig()->getCustomerGroupDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createTransactionAwareDataSetStepBroker();
        $dataSetStepBroker->addStep($this->createCustomerGroupWriterStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);

        return $dataImporter;
    }

    public function getCustomerGroupCompanyDataImporter(
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface&\Spryker\Zed\DataImport\Business\Model\DataImporterDataSetWriterAwareInterface $dataImporter */
        $dataImporter = $this->getCsvDataImporterWriterAware(
            $this->getConfig()->getCustomerGroupCompanyDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createDataSetStepBroker();
        $dataSetStepBroker
            ->addStep($this->createCustomerGroupNameToIdCustomerGroupStep())
            ->addStep($this->createCompanyKeyToIdCompanyStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);
        $dataImporter->setDataSetWriter($this->createCustomerGroupCompanyDataSetWriter());

        return $dataImporter;
    }

    public function getCustomerGroupCompanyBusinessUnitDataImporter(
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface&\Spryker\Zed\DataImport\Business\Model\DataImporterDataSetWriterAwareInterface $dataImporter */
        $dataImporter = $this->getCsvDataImporterWriterAware(
            $this->getConfig()->getCustomerGroupCompanyBusinessUnitDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createDataSetStepBroker();
        $dataSetStepBroker
            ->addStep($this->createCustomerGroupNameToIdCustomerGroupStep())
            ->addStep($this->createCompanyBusinessUnitKeyToIdCompanyBusinessUnitStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);
        $dataImporter->setDataSetWriter($this->createCustomerGroupCompanyBusinessUnitDataSetWriter());

        return $dataImporter;
    }

    public function getCustomerGroupCustomerDataImporter(
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface&\Spryker\Zed\DataImport\Business\Model\DataImporterDataSetWriterAwareInterface $dataImporter */
        $dataImporter = $this->getCsvDataImporterWriterAware(
            $this->getConfig()->getCustomerGroupCustomerDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createDataSetStepBroker();
        $dataSetStepBroker
            ->addStep($this->createCustomerGroupNameToIdCustomerGroupStep())
            ->addStep($this->createCustomerReferenceToIdCustomerStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);
        $dataImporter->setDataSetWriter($this->createCustomerGroupCustomerDataSetWriter());

        return $dataImporter;
    }

    public function getCsvDataImporter(
        DataImporterDataSourceConfigurationTransfer $dataImporterDataSourceConfigurationTransfer,
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        return $dataImporterConfigurationTransfer?->getReaderConfiguration() !== null
            ? $this->getDataImportFactory()->getCsvDataImporterFromConfig($dataImporterConfigurationTransfer)
            : $this->getCsvDataImporterFromConfig($dataImporterDataSourceConfigurationTransfer);
    }

    public function getCsvDataImporterWriterAware(
        DataImporterDataSourceConfigurationTransfer $dataImporterDataSourceConfigurationTransfer,
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface $dataImporter */
        $dataImporter = $this->getDataImportFactory()->getCsvDataImporterWriterAwareFromConfig(
            $dataImporterConfigurationTransfer?->getReaderConfiguration() !== null
                ? $dataImporterConfigurationTransfer
                : $this->buildImporterConfiguration($dataImporterDataSourceConfigurationTransfer),
        );

        return $dataImporter;
    }

    public function createDataSetStepBroker(): DataSetStepBrokerInterface
    {
        return new DataSetStepBroker();
    }

    public function createCustomerGroupWriterStep(): DataImportStepInterface
    {
        return new CustomerGroupWriterStep();
    }

    public function createCustomerGroupNameToIdCustomerGroupStep(): DataImportStepInterface
    {
        return new CustomerGroupNameToIdCustomerGroupStep();
    }

    public function createCompanyKeyToIdCompanyStep(): DataImportStepInterface
    {
        return new CompanyKeyToIdCompanyStep();
    }

    public function createCompanyBusinessUnitKeyToIdCompanyBusinessUnitStep(): DataImportStepInterface
    {
        return new CompanyBusinessUnitKeyToIdCompanyBusinessUnitStep();
    }

    public function createCustomerReferenceToIdCustomerStep(): DataImportStepInterface
    {
        return new CustomerReferenceToIdCustomerStep();
    }

    public function createCustomerGroupCompanyDataSetWriter(): DataSetWriterInterface
    {
        return new CustomerGroupCompanyDataSetWriter(
            $this->getEntityManager(),
            $this->getConfig(),
        );
    }

    public function createCustomerGroupCompanyBusinessUnitDataSetWriter(): DataSetWriterInterface
    {
        return new CustomerGroupCompanyBusinessUnitDataSetWriter(
            $this->getEntityManager(),
            $this->getConfig(),
        );
    }

    public function createCustomerGroupCustomerDataSetWriter(): DataSetWriterInterface
    {
        return new CustomerGroupCustomerDataSetWriter(
            $this->getEntityManager(),
            $this->getConfig(),
        );
    }

    public function createCustomerGroupQueryJoinExpander(): CustomerGroupQueryJoinExpanderInterface
    {
        return new CustomerGroupQueryJoinExpander();
    }

    public function createAssignedEntityNameReader(): AssignedEntityNameReaderInterface
    {
        return new AssignedEntityNameReader(
            $this->getCustomerFacade(),
            $this->getCompanyFacade(),
            $this->getCompanyBusinessUnitFacade(),
        );
    }

    public function getCustomerFacade(): CustomerFacadeInterface
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::FACADE_CUSTOMER);
    }

    public function getCompanyFacade(): CompanyFacadeInterface
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::FACADE_COMPANY);
    }

    public function getCompanyBusinessUnitFacade(): CompanyBusinessUnitFacadeInterface
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::FACADE_COMPANY_BUSINESS_UNIT);
    }
}
