<?php
/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Price;

use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Spryker\Client\Price\PriceFactory getFactory()
 * @method \Spryker\Client\Price\PriceConfig getConfig()
 */
class PriceClient extends AbstractClient implements PriceClientInterface
{

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return string
     */
    public function getCurrentPriceMode()
    {
        return $this->getFactory()
            ->createPriceModeResolver()
            ->getCurrentPriceMode();
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return string
     */
    public function getGrossPriceModeIdentifier()
    {
        return $this->getConfig()
            ->createSharedPriceConfig()
            ->getGrossPriceModeIdentifier();
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return string
     */
    public function getNetPriceModeIdentifier()
    {
        return $this->getConfig()
            ->createSharedPriceConfig()
            ->getNetPriceModeIdentifier();
    }
}