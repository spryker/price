<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Price\Business\Model;

use Orm\Zed\Price\Persistence\SpyPriceType;

class Reader implements ReaderInterface
{

    const PRICE_TYPE_UNKNOWN = 'price type unknown: ';
    const NO_RESULT = 'no result';
    const SKU_UNKNOWN = 'sku unknown';

    /**
     * @var \Spryker\Zed\Price\Persistence\PriceQueryContainer
     */
    protected $queryContainer;

    /**
     * @var \Spryker\Zed\Price\Dependency\Facade\PriceToProductInterface
     */
    protected $productFacade;

    /**
     * @var \Spryker\Zed\Price\PriceConfig
     */
    protected $priceSettings;

    /**
     * @var array
     */
    protected $priceTypeEntityByNameCache = [];

    /**
     * @param \Spryker\Zed\Price\Persistence\PriceQueryContainer $queryContainer
     * @param \Spryker\Zed\Price\Dependency\Facade\PriceToProductInterface $productFacade
     * @param \Spryker\Zed\Price\PriceConfig $priceSettings
     */
    public function __construct(
        $queryContainer,
        $productFacade,
        $priceSettings
    ) {
        $this->queryContainer = $queryContainer;
        $this->productFacade = $productFacade;
        $this->priceSettings = $priceSettings;
    }

    /**
     * @return array
     */
    public function getPriceTypes()
    {
        $priceTypes = [];
        $priceTypeEntities = $this->queryContainer->queryAllPriceTypes()->find();

        /** @var \Orm\Zed\Price\Persistence\SpyPriceType $priceType */
        foreach ($priceTypeEntities as $priceType) {
            $priceTypes[] = $priceType->getName();
        }

        return $priceTypes;
    }

    /**
     * @param string $sku
     * @param string|null $priceTypeName
     *
     * @throws \Exception
     *
     * @return int
     */
    public function getPriceBySku($sku, $priceTypeName = null)
    {
        $priceTypeName = $this->handleDefaultPriceType($priceTypeName);
        $priceEntity = $this->getPriceEntity($sku, $this->getPriceTypeByName($priceTypeName));

        return $priceEntity->getPrice();
    }

    /**
     * @param string $priceTypeName
     *
     * @throws \Exception
     *
     * @return \Orm\Zed\Price\Persistence\SpyPriceType
     */
    public function getPriceTypeByName($priceTypeName)
    {
        if (!isset($this->priceTypeEntityByNameCache[$priceTypeName])) {
            $priceTypeEntity = $this->queryContainer->queryPriceType($priceTypeName)->findOne();
            if ($priceTypeEntity === null) {
                throw new \Exception(self::PRICE_TYPE_UNKNOWN . $priceTypeName);
            }

            $this->priceTypeEntityByNameCache[$priceTypeName] = $priceTypeEntity;
        }

        return $this->priceTypeEntityByNameCache[$priceTypeName];
    }

    /**
     * @param string $sku
     * @param string|null $priceTypeName
     *
     * @return bool
     */
    public function hasValidPrice($sku, $priceTypeName = null)
    {
        $priceTypeName = $this->handleDefaultPriceType($priceTypeName);
        $priceType = $this->getPriceTypeByName($priceTypeName);

        if ($this->hasPriceForProductConcrete($sku, $priceType)
            || $this->hasPriceForProductAbstract($sku, $priceType)) {
            return true;
        }

        return false;
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
     * @return bool
     */
    public function hasProductAbstract($sku)
    {
        return $this->productFacade->hasProductAbstract($sku);
    }

    /**
     * @param string $sku
     * @param string $priceTypeName
     *
     * @return int
     */
    public function getProductPriceIdBySku($sku, $priceTypeName)
    {
        $priceType = $this->getPriceTypeByName($priceTypeName);

        if ($this->hasPriceForProductConcrete($sku, $priceType)) {
            return $this->queryContainer
                ->queryPriceEntityForProductConcrete($sku, $priceType)
                ->findOne()
                ->getIdPriceProduct();
        }

        return $this->queryContainer
            ->queryPriceEntityForProductAbstract($sku, $priceType)
            ->findOne()
            ->getIdPriceProduct();
    }

    /**
     * @param string $sku
     * @param \Orm\Zed\Price\Persistence\SpyPriceType $priceType
     *
     * @throws \Exception
     *
     * @return \Orm\Zed\Price\Persistence\SpyPriceProduct
     */
    protected function getPriceEntity($sku, SpyPriceType $priceType)
    {
        if ($this->hasPriceForProductConcrete($sku, $priceType)) {
            return $this->getPriceEntityForProductConcrete($sku, $priceType);
        }
        if ($this->hasPriceForProductAbstract($sku, $priceType)) {
            return $this->getPriceEntityForProductAbstract($sku, $priceType);
        }
        $abstractSku = $this->productFacade->getAbstractSkuFromProductConcrete($sku);
        if (!$this->hasProductAbstract($sku)
            || !$this->hasPriceForProductAbstract($abstractSku, $priceType)
        ) {
            throw new \Exception(self::NO_RESULT);
        }

        return $this->getPriceEntityForProductAbstract($abstractSku, $priceType);
    }

    /**
     * @param string $sku
     * @param \Orm\Zed\Price\Persistence\SpyPriceType $priceType
     *
     * @return bool
     */
    protected function hasPriceForProductConcrete($sku, SpyPriceType $priceType)
    {
        $priceProductCount = $this->queryContainer
            ->queryPriceEntityForProductConcrete($sku, $priceType)
            ->count();

        return $priceProductCount > 0;
    }

    /**
     * @param string $sku
     * @param \Orm\Zed\Price\Persistence\SpyPriceType $priceType
     *
     * @return bool
     */
    protected function hasPriceForProductAbstract($sku, $priceType)
    {
        $priceProductCount = $this->queryContainer
            ->queryPriceEntityForProductAbstract($sku, $priceType)
            ->count();

        return $priceProductCount > 0;
    }

    /**
     * @param string $sku
     * @param \Orm\Zed\Price\Persistence\SpyPriceType $priceType
     *
     * @return \Orm\Zed\Price\Persistence\SpyPriceProduct
     */
    protected function getPriceEntityForProductConcrete($sku, $priceType)
    {
        return $this->queryContainer
            ->queryPriceEntityForProductConcrete($sku, $priceType)
            ->findOne();
    }

    /**
     * @param string $sku
     * @param \Orm\Zed\Price\Persistence\SpyPriceType $priceType
     *
     * @return \Orm\Zed\Price\Persistence\SpyPriceProduct
     */
    protected function getPriceEntityForProductAbstract($sku, $priceType)
    {
        return $this->queryContainer
            ->queryPriceEntityForProductAbstract($sku, $priceType)
            ->findOne();
    }

    /**
     * @param string $priceType
     *
     * @return \Orm\Zed\Price\Persistence\SpyPriceType
     */
    protected function handleDefaultPriceType($priceType = null)
    {
        if ($priceType === null) {
            $priceType = $this->priceSettings->getPriceTypeDefaultName();
        }

        return $priceType;
    }

    /**
     * @param string $sku
     *
     * @throws \Spryker\Zed\Product\Business\Exception\MissingProductException
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
     * @throws \Spryker\Zed\Product\Business\Exception\MissingProductException
     *
     * @return int
     */
    public function getProductConcreteIdBySku($sku)
    {
        return $this->productFacade->getProductConcreteIdBySku($sku);
    }

}
