<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Price\Business\Model;

use SprykerFeature\Zed\Price\Persistence\Propel\SpyPriceProduct;
use SprykerFeature\Zed\Price\Persistence\Propel\SpyPriceType;
use SprykerFeature\Zed\Product\Business\Exception\MissingProductException;

interface ReaderInterface
{
    /**
     * @return array
     */
    public function getPriceTypes();

    /**
     * @param string $sku
     * @param string $priceTypeName
     *
     * @return int
     */
    public function getPriceBySku($sku, $priceTypeName = null);

    /**
     * @param string $priceTypeNameName
     *
     * @return SpyPriceType
     */
    public function getPriceTypeByName($priceTypeNameName);

    /**
     * @param string $sku
     * @param string $priceTypeName
     *
     * @return bool
     */
    public function hasValidPrice($sku, $priceTypeName = null);

    /**
     * @param string $sku
     *
     * @return bool
     */
    public function hasAbstractProduct($sku);

    /**
     * @param string $sku
     *
     * @return bool
     */
    public function hasConcreteProduct($sku);

    /**
     * @param string $sku
     *
     * @return int
     * @throws MissingProductException
     */
    public function getAbstractProductIdBySku($sku);


     /**
     * @param string $sku
     *
     * @return int
     * @throws MissingProductException
     */
    public function getConcreteProductIdBySku($sku);

    /**
     * @param string $sku
     * @param string $priceTypeName
     *
     * @return int
     */
    public function getProductPriceIdBySku($sku, $priceTypeName);
}
