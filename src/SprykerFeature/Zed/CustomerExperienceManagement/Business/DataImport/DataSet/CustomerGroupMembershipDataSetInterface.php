<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet;

interface CustomerGroupMembershipDataSetInterface
{
    public const string COLUMN_CUSTOMER_GROUP_NAME = 'customer_group_name';

    public const string COLUMN_COMPANY_KEY = 'company_key';

    public const string COLUMN_BUSINESS_UNIT_KEY = 'business_unit_key';

    public const string COLUMN_CUSTOMER_REFERENCE = 'customer_reference';

    public const string KEY_ID_CUSTOMER_GROUP = 'id_customer_group';

    public const string KEY_ID_COMPANY = 'id_company';

    public const string KEY_ID_COMPANY_BUSINESS_UNIT = 'id_company_business_unit';

    public const string KEY_ID_CUSTOMER = 'id_customer';
}
