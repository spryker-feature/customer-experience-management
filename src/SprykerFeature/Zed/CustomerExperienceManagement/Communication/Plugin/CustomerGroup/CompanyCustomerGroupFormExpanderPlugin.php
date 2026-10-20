<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class CompanyCustomerGroupFormExpanderPlugin extends AbstractPlugin implements CustomerGroupFormExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Adds the `companyAssignment` sub-form, which carries the IDs of the companies to assign and to de-assign.
     * - The submitted IDs are persisted by {@link \SprykerFeature\Zed\CustomerExperienceManagement\Communication\Plugin\CustomerGroup\CompanyCustomerGroupPostSavePlugin}.
     *
     * @api
     *
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     *
     * @return \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface>
     */
    public function expand(FormBuilderInterface $builder, CustomerGroupTransfer $customerGroupTransfer): FormBuilderInterface
    {
        return $this->getFactory()
            ->createCustomerGroupFormExpander()
            ->expandWithCompanyAssignment($builder, $customerGroupTransfer);
    }
}
