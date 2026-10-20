<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form;

use Generated\Shared\Transfer\CustomerGroupToCompanyAssignmentTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AbstractCompanyAssignmentTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AssignedCompanyTable;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Table\AvailableCompanyTable;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CustomerGroupCompanyAssignmentForm extends AbstractCustomerGroupAssignmentForm
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', CustomerGroupToCompanyAssignmentTransfer::class);
        $resolver->setDefault(static::OPTION_CONSTRAINTS, function (Options $options): array {
            return [$this->getFactory()->createCompanyAssignmentConstraint($options[static::OPTION_ID_CUSTOMER_GROUP])];
        });
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addIdsField($builder, CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN);
        $this->addIdsField($builder, CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_DE_ASSIGN);
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
            $factory->createSelectableAvailableCompanyTable(
                $idCustomerGroup,
                $this->getInputSelector($view, CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_ASSIGN),
            )->render(),
            $factory->createAvailableCompanyTableTabs()->createView(),
            AvailableCompanyTable::DESTINATION_TABLE_ID,
            $factory->createSelectableAssignedCompanyTable(
                $idCustomerGroup,
                $this->getInputSelector($view, CustomerGroupToCompanyAssignmentTransfer::IDS_COMPANY_TO_DE_ASSIGN),
            )->render(),
            $factory->createAssignedCompanyTableTabs()->createView(),
            AssignedCompanyTable::DESTINATION_TABLE_ID,
            AbstractCompanyAssignmentTable::DESTINATION_TABLE_COLUMN_TITLES,
        );
    }

    public function getBlockPrefix(): string
    {
        return 'customer_group_company_assignment';
    }
}
