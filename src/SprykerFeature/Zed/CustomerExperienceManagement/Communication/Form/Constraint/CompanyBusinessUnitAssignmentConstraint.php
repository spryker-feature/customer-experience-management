<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\Constraint;

use Generated\Shared\Transfer\CustomerGroupToCompanyBusinessUnitAssignmentTransfer;

class CompanyBusinessUnitAssignmentConstraint extends AbstractCustomerGroupAssignmentConstraint
{
    protected const string MESSAGE_UNKNOWN_IDS = 'Unknown business unit IDs: %ids%.';

    protected const string MESSAGE_IDS_TO_ASSIGN_AND_DE_ASSIGN = 'Business unit IDs selected to be both assigned and de-assigned: %ids%.';

    protected const string MESSAGE_ALREADY_ASSIGNED_IDS = 'Business unit IDs already assigned to the customer group: %ids%.';

    protected const string MESSAGE_NOT_ASSIGNED_IDS = 'Business unit IDs not assigned to the customer group: %ids%.';

    /**
     * @return list<int>
     */
    public function getIdsToAssign(mixed $assignment): array
    {
        if (!$assignment instanceof CustomerGroupToCompanyBusinessUnitAssignmentTransfer) {
            return [];
        }

        return array_values($assignment->getIdsCompanyBusinessUnitToAssign());
    }

    /**
     * @return list<int>
     */
    public function getIdsToDeAssign(mixed $assignment): array
    {
        if (!$assignment instanceof CustomerGroupToCompanyBusinessUnitAssignmentTransfer) {
            return [];
        }

        return array_values($assignment->getIdsCompanyBusinessUnitToDeAssign());
    }

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    public function getExistingIds(array $ids): array
    {
        return $this->customerExperienceManagementRepository->getExistingCompanyBusinessUnitIds($ids);
    }

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    public function getAssignedIds(int $idCustomerGroup, array $ids): array
    {
        return $this->customerExperienceManagementRepository->getAssignedCompanyBusinessUnitIds($idCustomerGroup, $ids);
    }

    public function getUnknownIdsMessage(): string
    {
        return static::MESSAGE_UNKNOWN_IDS;
    }

    public function getIdsToAssignAndDeAssignMessage(): string
    {
        return static::MESSAGE_IDS_TO_ASSIGN_AND_DE_ASSIGN;
    }

    public function getAlreadyAssignedIdsMessage(): string
    {
        return static::MESSAGE_ALREADY_ASSIGNED_IDS;
    }

    public function getNotAssignedIdsMessage(): string
    {
        return static::MESSAGE_NOT_ASSIGNED_IDS;
    }
}
