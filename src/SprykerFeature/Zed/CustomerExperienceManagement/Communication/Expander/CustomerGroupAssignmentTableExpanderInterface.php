<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Expander;

use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

interface CustomerGroupAssignmentTableExpanderInterface
{
    public function expandConfiguration(TableConfiguration $config, string $idColumn): TableConfiguration;

    public function expandUrlPath(string $urlPath): string;

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function expandRow(array $row, string $idColumn): array;
}
