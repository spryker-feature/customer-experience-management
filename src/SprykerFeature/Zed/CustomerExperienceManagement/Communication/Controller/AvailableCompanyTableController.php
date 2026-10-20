<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller;

use Spryker\Zed\Gui\Communication\Table\AbstractTable;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class AvailableCompanyTableController extends AbstractAssignmentTableController
{
    protected function createSelectableTable(?int $idCustomerGroup): AbstractTable
    {
        return $this->getFactory()->createSelectableAvailableCompanyTable($idCustomerGroup);
    }
}
