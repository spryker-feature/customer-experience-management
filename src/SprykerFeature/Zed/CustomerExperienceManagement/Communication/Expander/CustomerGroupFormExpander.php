<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\AbstractCustomerGroupAssignmentForm;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\CustomerGroupCompanyAssignmentForm;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\CustomerGroupCompanyBusinessUnitAssignmentForm;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerGroupFormExpander implements CustomerGroupFormExpanderInterface
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     *
     * @return \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface>
     */
    public function expandWithCompanyAssignment(FormBuilderInterface $builder, CustomerGroupTransfer $customerGroupTransfer): FormBuilderInterface
    {
        return $builder->add(
            CustomerGroupTransfer::COMPANY_ASSIGNMENT,
            CustomerGroupCompanyAssignmentForm::class,
            $this->getAssignmentFormOptions($customerGroupTransfer),
        );
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     *
     * @return \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface>
     */
    public function expandWithCompanyBusinessUnitAssignment(FormBuilderInterface $builder, CustomerGroupTransfer $customerGroupTransfer): FormBuilderInterface
    {
        return $builder->add(
            CustomerGroupTransfer::COMPANY_BUSINESS_UNIT_ASSIGNMENT,
            CustomerGroupCompanyBusinessUnitAssignmentForm::class,
            $this->getAssignmentFormOptions($customerGroupTransfer),
        );
    }

    /**
     * @return array<string, int|null>
     */
    protected function getAssignmentFormOptions(CustomerGroupTransfer $customerGroupTransfer): array
    {
        return [
            AbstractCustomerGroupAssignmentForm::OPTION_ID_CUSTOMER_GROUP => $customerGroupTransfer->getIdCustomerGroup(),
        ];
    }
}
