<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Symfony\Component\Form\FormBuilderInterface;

interface CustomerGroupFormExpanderInterface
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     *
     * @return \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface>
     */
    public function expandWithCompanyAssignment(FormBuilderInterface $builder, CustomerGroupTransfer $customerGroupTransfer): FormBuilderInterface;

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     *
     * @return \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface>
     */
    public function expandWithCompanyBusinessUnitAssignment(FormBuilderInterface $builder, CustomerGroupTransfer $customerGroupTransfer): FormBuilderInterface;
}
