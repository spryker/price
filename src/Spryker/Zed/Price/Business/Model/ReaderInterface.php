<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Price\Business\Model;

use Orm\Zed\Price\Persistence\SpyPriceType;
use Spryker\Zed\Product\Business\Exception\MissingProductException;

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
    public function hasProductAbstract($sku);

    /**
     * @param string $sku
     *
     * @return bool
     */
    public function hasConcreteProduct($sku);

    /**
     * @param string $sku
     *
     * @throws MissingProductException
     *
     * @return int
     */
    public function getProductAbstractIdBySku($sku);

    /**
     * @param string $sku
     *
     * @throws MissingProductException
     *
     * @return int
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
