<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Functional\SprykerFeature\Zed\Price;

use Codeception\TestCase\Test;
use SprykerEngine\Zed\Kernel\Business\Factory;
use SprykerEngine\Zed\Kernel\Locator;
use SprykerFeature\Zed\Price\Business\PriceFacade;
use Generated\Zed\Ide\AutoCompletion;
use SprykerFeature\Zed\Price\Persistence\Propel\SpyPriceProductQuery;
use SprykerFeature\Zed\Price\Persistence\Propel\SpyPriceTypeQuery;
use SprykerFeature\Zed\Product\Persistence\Propel\SpyAbstractProductQuery;
use SprykerFeature\Zed\Product\Persistence\Propel\SpyProductQuery;

/**
 * @group PriceTest
 */
class WriterTest extends Test
{

    const DUMMY_PRICE_TYPE_1 = 'TYPE1';
    const DUMMY_PRICE_TYPE_2 = 'TYPE2';
    const DUMMY_SKU_ABSTRACT_PRODUCT = 'ABSTRACT';
    const DUMMY_SKU_CONCRETE_PRODUCT = 'CONCRETE';
    const DUMMY_NEW_PRICE_1 = 99;
    const DUMMY_NEW_PRICE_2 = 100;

    /**
     * @var PriceFacade
     */
    private $priceFacade;
    /**
     * @var AutoCompletion
     */
    protected $locator;

    public function setUp()
    {
        parent::setUp();

        $this->locator = Locator::getInstance();
        $this->priceFacade = new PriceFacade(new Factory('Price'), $this->locator);
        $this->setTestData();
    }

    public function testCreatePriceType()
    {
        $priceTypeEntity = $this->priceFacade->createPriceType(self::DUMMY_PRICE_TYPE_1);
        $priceTypeQuery = SpyPriceTypeQuery::create()->filterByName($priceTypeEntity->getName())->findOne();

        $this->assertNotEmpty($priceTypeQuery);
    }

    public function testCreatePriceForAbstractProduct()
    {
        $abstractProduct = SpyAbstractProductQuery::create()->filterBySku(self::DUMMY_SKU_ABSTRACT_PRODUCT)->findOne();
        $priceType1 = SpyPriceTypeQuery::create()->filterByName(self::DUMMY_PRICE_TYPE_1)->findOne();

        $request = SpyPriceProductQuery::create()->filterBySpyAbstractProduct($abstractProduct)->find();
        $this->assertEquals(0, count($request));

        $transferPriceProduct = $this->setTransferPriceProduct(
            self::DUMMY_SKU_ABSTRACT_PRODUCT,
            self::DUMMY_PRICE_TYPE_1
        );
        $this->priceFacade->createPriceForProduct($transferPriceProduct);

        $request = $this->findPriceEntitiesAbstractProduct($abstractProduct, $priceType1);
        $this->assertEquals(1, count($request));
    }

    public function testCreatePriceForConcreteProduct()
    {
        $concreteProduct = SpyProductQuery::create()->filterBySku(self::DUMMY_SKU_CONCRETE_PRODUCT)->findOne();
        $priceType2 = SpyPriceTypeQuery::create()->filterByName(self::DUMMY_PRICE_TYPE_2)->findOne();

        $request = SpyPriceProductQuery::create()->filterByProduct($concreteProduct)->find();
        $this->assertEquals(0, count($request));

        $transferPriceProduct = $this->setTransferPriceProduct(
            self::DUMMY_SKU_CONCRETE_PRODUCT,
            self::DUMMY_PRICE_TYPE_2
        );
        $this->priceFacade->createPriceForProduct($transferPriceProduct);

        $request = $this->findPriceEntitiesConcreteProduct($concreteProduct, $priceType2);
        $this->assertEquals(1, count($request));
    }

    public function testSetPriceForAbstractProduct()
    {
        $abstractProduct = SpyAbstractProductQuery::create()->filterBySku(self::DUMMY_SKU_ABSTRACT_PRODUCT)->findOne();

        $request = SpyPriceProductQuery::create()->filterBySpyAbstractProduct($abstractProduct)->find();
        $this->assertEquals(0, count($request));

        $this->deletePriceEntitiesAbstract($abstractProduct);
        $transferPriceProduct = $this->setTransferPriceProduct(
            self::DUMMY_SKU_ABSTRACT_PRODUCT,
            self::DUMMY_PRICE_TYPE_1
        );
        $this->priceFacade->createPriceForProduct($transferPriceProduct);
        $request = SpyPriceProductQuery::create()
            ->filterBySpyAbstractProduct($abstractProduct)
            ->findOne();

        $transferPriceProduct = $this->setTransferPriceProduct(
            self::DUMMY_SKU_ABSTRACT_PRODUCT,
            self::DUMMY_PRICE_TYPE_2
        );
        $transferPriceProduct->setIdPriceProduct($request->getIdPriceProduct());
        $this->priceFacade->setPriceForProduct($transferPriceProduct);

        $request = SpyPriceProductQuery::create()
            ->filterBySpyAbstractProduct($abstractProduct)
            ->find();

        $this->assertEquals(1, count($request));
    }

    protected function setTransferPriceProduct($sku, $priceType)
    {
        $transferPriceProduct = new \Generated\Shared\Transfer\PriceProductTransfer();
        $transferPriceProduct
            ->setPrice(100)
            ->setSkuProduct($sku)
            ->setPriceTypeName($priceType);

        return $transferPriceProduct;
    }

    protected function findPriceEntitiesAbstractProduct($abstractProduct, $priceType)
    {
        return SpyPriceProductQuery::create()
            ->filterBySpyAbstractProduct($abstractProduct)
            ->filterByPriceType($priceType)
            ->find();
    }

    protected function findPriceEntitiesConcreteProduct($concreteProduct, $priceType)
    {
        return SpyPriceProductQuery::create()
            ->filterByProduct($concreteProduct)
            ->filterByPriceType($priceType)
            ->find();
    }

    protected function deletePriceEntitiesAbstract($requestProduct)
    {
        SpyPriceProductQuery::create()->filterBySpyAbstractProduct($requestProduct)->delete();
    }

    protected function deletePriceEntitiesConcrete($requestProduct)
    {
        SpyPriceProductQuery::create()->filterByProduct($requestProduct)->delete();
    }

    protected function setTestData()
    {
        $priceType1 = SpyPriceTypeQuery::create()->filterByName(self::DUMMY_PRICE_TYPE_1)->findOneOrCreate();
        $priceType1->setName(self::DUMMY_PRICE_TYPE_1)->save();

        $priceType2 = SpyPriceTypeQuery::create()->filterByName(self::DUMMY_PRICE_TYPE_2)->findOneOrCreate();
        $priceType2->setName(self::DUMMY_PRICE_TYPE_2)->save();

        $abstractProduct = SpyAbstractProductQuery::create()
            ->filterBySku(self::DUMMY_SKU_ABSTRACT_PRODUCT)
            ->findOneOrCreate()
        ;
        $abstractProduct->setSku(self::DUMMY_SKU_ABSTRACT_PRODUCT)->save();

        $concreteProduct = SpyProductQuery::create()->filterBySku(self::DUMMY_SKU_CONCRETE_PRODUCT)->findOneOrCreate();
        $this->deletePriceEntitiesConcrete($concreteProduct);
        $concreteProduct->setSku(self::DUMMY_SKU_CONCRETE_PRODUCT)->setSpyAbstractProduct($abstractProduct)->save();

        $this->deletePriceEntitiesAbstract($abstractProduct);
    }

}
