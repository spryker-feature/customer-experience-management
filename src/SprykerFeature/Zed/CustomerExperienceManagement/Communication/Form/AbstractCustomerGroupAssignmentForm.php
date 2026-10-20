<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form;

use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
abstract class AbstractCustomerGroupAssignmentForm extends AbstractType
{
    public const string OPTION_ID_CUSTOMER_GROUP = 'id_customer_group';

    protected const string OPTION_CONSTRAINTS = 'constraints';

    protected const string VAR_AVAILABLE_TABLE = 'availableTable';

    protected const string VAR_AVAILABLE_TABLE_TABS = 'availableTableTabs';

    protected const string VAR_AVAILABLE_DESTINATION_TABLE_ID = 'availableDestinationTableId';

    protected const string VAR_ASSIGNED_TABLE = 'assignedTable';

    protected const string VAR_ASSIGNED_TABLE_TABS = 'assignedTableTabs';

    protected const string VAR_ASSIGNED_DESTINATION_TABLE_ID = 'assignedDestinationTableId';

    protected const string VAR_DESTINATION_TABLE_COLUMN_TITLES = 'destinationTableColumnTitles';

    protected const string MESSAGE_INVALID_IDS = 'The selected IDs are not valid.';

    protected const string ID_SELECTOR_TEMPLATE = '#%s';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'error_bubbling' => false,
            static::OPTION_ID_CUSTOMER_GROUP => null,
        ]);

        $resolver->setAllowedTypes(static::OPTION_ID_CUSTOMER_GROUP, ['null', 'int']);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface<string, \Symfony\Component\Form\FormBuilderInterface> $builder
     */
    protected function addIdsField(FormBuilderInterface $builder, string $fieldName): void
    {
        $builder->add($fieldName, HiddenType::class, [
            'invalid_message' => static::MESSAGE_INVALID_IDS,
        ]);

        $builder->get($fieldName)->addModelTransformer($this->getFactory()->createIdsCsvDataTransformer());
    }

    protected function getInputSelector(FormView $view, string $fieldName): string
    {
        return sprintf(static::ID_SELECTOR_TEMPLATE, $view->children[$fieldName]->vars['id']);
    }

    /**
     * @param list<string> $destinationTableColumnTitles
     */
    protected function addTableVariables(
        FormView $view,
        string $availableTable,
        TabsViewTransfer $availableTableTabsViewTransfer,
        string $availableDestinationTableId,
        string $assignedTable,
        TabsViewTransfer $assignedTableTabsViewTransfer,
        string $assignedDestinationTableId,
        array $destinationTableColumnTitles
    ): void {
        $view->vars[static::VAR_AVAILABLE_TABLE] = $availableTable;
        $view->vars[static::VAR_AVAILABLE_TABLE_TABS] = $availableTableTabsViewTransfer;
        $view->vars[static::VAR_AVAILABLE_DESTINATION_TABLE_ID] = $availableDestinationTableId;
        $view->vars[static::VAR_ASSIGNED_TABLE] = $assignedTable;
        $view->vars[static::VAR_ASSIGNED_TABLE_TABS] = $assignedTableTabsViewTransfer;
        $view->vars[static::VAR_ASSIGNED_DESTINATION_TABLE_ID] = $assignedDestinationTableId;
        $view->vars[static::VAR_DESTINATION_TABLE_COLUMN_TITLES] = $destinationTableColumnTitles;
    }
}
