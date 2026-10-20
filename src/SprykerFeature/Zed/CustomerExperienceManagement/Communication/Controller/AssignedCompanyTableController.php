<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Controller;

use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Communication\CustomerExperienceManagementCommunicationFactory getFactory()
 */
class AssignedCompanyTableController extends AbstractAssignmentTableController
{
    public function tableAction(Request $request): JsonResponse
    {
        return $this->jsonResponse(
            $this->getFactory()->createAssignedCompanyTable($this->findIdCustomerGroup($request))->fetchData(),
        );
    }

    protected function createSelectableTable(?int $idCustomerGroup): AbstractTable
    {
        return $this->getFactory()->createSelectableAssignedCompanyTable($idCustomerGroup);
    }
}
