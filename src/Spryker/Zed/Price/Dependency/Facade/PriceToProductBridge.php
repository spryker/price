<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Price\Dependency\Facade;

class PriceToProductBridge implements PriceToProductInterface
{

    /**
     * @var \Spryker\Zed\Product\Business\ProductFacadeInterface
     */
    protected $productFacade;

    /**
     * @param \Spryker\Zed\Product\Business\ProductFacadeInterface $productFacade
     */
    public function __construct($productFacade)
    {
        $this->productFacade = $productFacade;
    }

    /**
     * @param string $sku
     *
     * @return int
     */
    public function getProductAbstractIdBySku($sku)
    {
        return $this->productFacade->getProductAbstractIdBySku($sku);
    }

    /**
     * @param string $sku
     *
     * @return int
     */
    public function getProductConcreteIdBySku($sku)
    {
        return $this->productFacade->getProductConcreteIdBySku($sku);
    }

    /**
     * @param string $sku
     *
     * @return bool
     */
    public function hasProductAbstract($sku)
    {
        return $this->productFacade->hasProductAbstract($sku);
    }

    /**
     * @param string $sku
     *
     * @return bool
     */
    public function hasProductConcrete($sku)
    {
        return $this->productFacade->hasProductConcrete($sku);
    }

    /**
     * @param string $sku
     *
     * @return string
     */
    public function getAbstractSkuFromProductConcrete($sku)
    {
        return $this->productFacade->getAbstractSkuFromProductConcrete($sku);
    }

}
