<?php
/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Price;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Price\PriceModeResolver\PriceModeResolver;

/**
 * @method \Spryker\Client\Price\PriceConfig getConfig()
 */
class PriceFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Client\Price\PriceModeResolver\PriceModeResolverInterface
     */
    public function createPriceModeResolver()
    {
        return new PriceModeResolver($this->getQuoteClient(), $this->getConfig());
    }

    /**
     * @return \Spryker\Client\Price\Dependency\Client\PriceToQuoteClientInterface
     */
    protected function getQuoteClient()
    {
        return $this->getProvidedDependency(PriceDependencyProvider::CLIENT_QUOTE);
    }
}
