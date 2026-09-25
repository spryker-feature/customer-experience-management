<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper;

use Generated\Shared\Transfer\CustomerAccessTransfer;

class CustomerAccessResourceMapper implements CustomerAccessResourceMapperInterface
{
    protected const string FIELD_CONTENT_TYPE_ACCESS = 'contentTypeAccess';

    protected const string FIELD_CONTENT_TYPE = 'contentType';

    protected const string FIELD_IS_RESTRICTED = 'isRestricted';

    /**
     * @return array<string, mixed>
     */
    public function mapCustomerAccessTransferToResourceData(CustomerAccessTransfer $customerAccessTransfer): array
    {
        $contentTypeAccess = [];

        foreach ($customerAccessTransfer->getContentTypeAccess() as $contentTypeAccessTransfer) {
            $contentTypeAccess[] = [
                static::FIELD_CONTENT_TYPE => $contentTypeAccessTransfer->getContentType(),
                static::FIELD_IS_RESTRICTED => (bool)$contentTypeAccessTransfer->getIsRestricted(),
            ];
        }

        return [static::FIELD_CONTENT_TYPE_ACCESS => $contentTypeAccess];
    }
}
