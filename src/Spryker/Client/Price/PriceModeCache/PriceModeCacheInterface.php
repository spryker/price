<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Price\PriceModeCache;

interface PriceModeCacheInterface
{
    public function isCached(): bool;

    public function get(): string;

    public function cache(string $priceMode): void;

    public function invalidate(): void;
}
