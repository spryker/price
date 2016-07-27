<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Price\Business;

use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ZedProductPriceTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use Spryker\Zed\Messenger\Business\Model\MessengerInterface;

/**
 * @method \Spryker\Zed\Price\Business\PriceBusinessFactory getFactory()
 */
class PriceFacade extends AbstractFacade implements PriceFacadeInterface
{

    /**
     * @api
     *
     * @return array
     */
    public function getPriceTypeValues()
    {
        return $this->getFactory()->createReaderModel()->getPriceTypes();
    }

    /**
     * @api
     *
     * @param string $sku
     * @param string|null $priceType
     *
     * @return int
     */
    public function getPriceBySku($sku, $priceType = null)
    {
        return $this->getFactory()->createReaderModel()->getPriceBySku($sku, $priceType);
    }

    /**
     * @api
     *
     * @param int $idAbstractProduct
     * @param null $priceType
     *
     * @return \Generated\Shared\Transfer\ZedProductPriceTransfer|null
     */
    public function getProductAbstractPrice($idAbstractProduct, $priceType = null)
    {
        return $this->getFactory()->createReaderModel()->getProductAbstractPrice($idAbstractProduct, $priceType);
    }

    /**
     * @api
     *
     * @param int $idProduct
     * @param null $priceType
     *
     * @return \Generated\Shared\Transfer\PriceProductConcreteTransfer|null
     */
    public function getProductConcretePrice($idProduct, $priceType = null)
    {
        return $this->getFactory()->createReaderModel()->getProductConcretePrice($idProduct, $priceType);
    }

    /**
     * @api
     *
     * @param string $name
     *
     * @return \Orm\Zed\Price\Persistence\SpyPriceType
     */
    public function createPriceType($name)
    {
        return $this->getFactory()->createWriterModel()->createPriceType($name);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\PriceProductTransfer $transferPriceProduct
     *
     * @return mixed
     */
    public function setPriceForProduct(PriceProductTransfer $transferPriceProduct)
    {
        return $this->getFactory()->createWriterModel()->setPriceForProduct($transferPriceProduct);
    }

    /**
     * @api
     *
     * @param \Spryker\Zed\Messenger\Business\Model\MessengerInterface $messenger
     *
     * @return void
     */
    public function install(MessengerInterface $messenger)
    {
        $this->getFactory()->createInstaller($messenger)->install();
    }

    /**
     * @api
     *
     * @param string $sku
     * @param string|null $priceType
     *
     * @return bool
     */
    public function hasValidPrice($sku, $priceType = null)
    {
        return $this->getFactory()->createReaderModel()->hasValidPrice($sku, $priceType);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\PriceProductTransfer $transferPriceProduct
     *
     * @return void
     */
    public function createPriceForProduct(PriceProductTransfer $transferPriceProduct)
    {
        $this->getFactory()->createWriterModel()->createPriceForProduct($transferPriceProduct);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getDefaultPriceTypeName()
    {
        return $this->getFactory()->getConfig()->getPriceTypeDefaultName();
    }

    /**
     * @api
     *
     * @param string $sku
     * @param string $priceType
     *
     * @return int
     */
    public function getIdPriceProduct($sku, $priceType)
    {
        return $this->getFactory()->createReaderModel()->getProductPriceIdBySku($sku, $priceType);
    }

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\ZedProductPriceTransfer $priceTransfer
     *
     * @return int
     */
    public function persistAbstractProductPrice(ZedProductPriceTransfer $priceTransfer, $priceType = null)
    {
        return $this->getFactory()->createWriterModel()->persistAbstractProductPrice($priceTransfer, $priceType);
    }

}
