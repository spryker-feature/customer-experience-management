<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form;

use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCompanyBusinessUnitAssignmentTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AssignedCompanyBusinessUnitTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AvailableCompanyBusinessUnitTable;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CustomerGroupCompanyBusinessUnitAssignmentForm extends AbstractCustomerGroupAssignmentForm
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', CustomerGroupToCompanyBusinessUnitAssignmentTransfer::class);
        $resolver->setDefault(static::OPTION_CONSTRAINTS, function (Options $options): array {
            return [$this->getFactory()->createCompanyBusinessUnitAssignmentConstraint($options[static::OPTION_ID_CUSTOMER_GROUP])];
        });
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addIdsField($builder, CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_ASSIGN);
        $this->addIdsField($builder, CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     * @param array<string, mixed> $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $idCustomerGroup = $options[static::OPTION_ID_CUSTOMER_GROUP];
        $factory = $this->getFactory();

        $this->addTableVariables(
            $view,
            $factory->createSelectableAvailableCompanyBusinessUnitTable(
                $idCustomerGroup,
                $this->getInputSelector($view, CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_ASSIGN),
            )->render(),
            $factory->createAvailableCompanyBusinessUnitTableTabs()->createView(),
            AvailableCompanyBusinessUnitTable::DESTINATION_TABLE_ID,
            $factory->createSelectableAssignedCompanyBusinessUnitTable(
                $idCustomerGroup,
                $this->getInputSelector($view, CustomerGroupToCompanyBusinessUnitAssignmentTransfer::IDS_COMPANY_BUSINESS_UNIT_TO_DE_ASSIGN),
            )->render(),
            $factory->createAssignedCompanyBusinessUnitTableTabs()->createView(),
            AssignedCompanyBusinessUnitTable::DESTINATION_TABLE_ID,
            AbstractCompanyBusinessUnitAssignmentTable::DESTINATION_TABLE_COLUMN_TITLES,
        );
    }

    public function getBlockPrefix(): string
    {
        return 'customer_group_company_business_unit_assignment';
    }
}
