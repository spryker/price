<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Price\Business\Model;

use Generated\Zed\Ide\AutoCompletion;
use SprykerEngine\Shared\Kernel\LocatorLocatorInterface;
use SprykerFeature\Zed\Price\Persistence\PriceQueryContainer;
use SprykerFeature\Zed\Price\Dependency\Facade\PriceToTouchInterface;
use Generated\Shared\Transfer\PriceProductTransfer;
use Orm\Zed\Price\Persistence\SpyPriceProduct;
use Orm\Zed\Price\Persistence\SpyPriceType;
use Propel\Runtime\Exception\PropelException;
use SprykerFeature\Zed\Price\PriceConfig;

class Writer implements WriterInterface
{

    const TOUCH_PRODUCT = 'product';
    const ENTITY_NOT_FOUND = 'entity not found';

    /**
     * @var AutoCompletion
     */
    protected $locator;

    /**
     * @var PriceQueryContainer
     */
    protected $queryContainer;

    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var PriceToTouchInterface
     */
    protected $touchFacade;

    /**
     * @var PriceConfig
     */
    protected $priceSettings;

    /**
     * @param LocatorLocatorInterface $locator
     * @param PriceQueryContainer $queryContainer
     * @param ReaderInterface $reader
     * @param PriceToTouchInterface $touchFacade
     * @param PriceConfig $priceSettings
     */
    public function __construct(
        LocatorLocatorInterface $locator,
        PriceQueryContainer $queryContainer,
        ReaderInterface $reader,
        PriceToTouchInterface $touchFacade,
        PriceConfig $priceSettings
    ) {
        $this->locator = $locator;
        $this->queryContainer = $queryContainer;
        $this->reader = $reader;
        $this->touchFacade = $touchFacade;
        $this->priceSettings = $priceSettings;
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     * @throws PropelException
     *
     * @return SpyPriceType
     */
    public function createPriceType($name)
    {
        $priceTypeEntity = $this->queryContainer->queryPriceType($name)->findOneOrCreate();
        $priceTypeEntity->setName($name)->save();

        return $priceTypeEntity;
    }

    /**
     * @param PriceProductTransfer $priceProductTransfer
     *
     * @throws \Exception
     *
     * @return SpyPriceProduct
     */
    public function createPriceForProduct(PriceProductTransfer $priceProductTransfer)
    {
        $priceProductTransfer = $this->setPriceType($priceProductTransfer);
        if (
            !$this->isPriceTypeExistingForAbstractProduct($priceProductTransfer)
            && !$this->isPriceTypeExistingForConcreteProduct($priceProductTransfer)
        ) {
            $this->loadAbstractProductIdForPriceProductTransfer($priceProductTransfer);
            $this->loadConcreteProductIdForPriceProductTransfer($priceProductTransfer);

            $entity = new SpyPriceProduct();
            $newPrice = $this->savePriceProductEntity($priceProductTransfer, $entity);

            if ($priceProductTransfer->getIdProduct()) {
                $this->insertTouchRecord(self::TOUCH_PRODUCT, $priceProductTransfer->getIdProduct());
            }

            return $newPrice;
        }
        throw new \Exception('This couple product price type is already set');
    }

    /**
     * @param PriceProductTransfer $priceProductTransfer
     *
     * @throws \Exception
     */
    public function setPriceForProduct(PriceProductTransfer $priceProductTransfer)
    {
        $priceProductTransfer = $this->setPriceType($priceProductTransfer);

        if (
            $this->isPriceTypeExistingForConcreteProduct($priceProductTransfer)
            || $this->isPriceTypeExistingForAbstractProduct($priceProductTransfer)
        ) {
            $this->loadAbstractProductIdForPriceProductTransfer($priceProductTransfer);
            $this->loadConcreteProductIdForPriceProductTransfer($priceProductTransfer);

            $priceProductEntity = $this->getPriceProductById($priceProductTransfer->getIdPriceProduct());
            $this->savePriceProductEntity($priceProductTransfer, $priceProductEntity);

            if ($priceProductTransfer->getIdProduct()) {
                $this->insertTouchRecord(self::TOUCH_PRODUCT, $priceProductTransfer->getIdProduct());
            }
        } else {
            throw new \Exception('There is no price assigned for selected product!');
        }
    }

    /**
     * @param PriceProductTransfer $transferPriceProduct
     */
    protected function loadAbstractProductIdForPriceProductTransfer(PriceProductTransfer $transferPriceProduct)
    {
        if ($transferPriceProduct->getIdAbstractProduct() === null) {
            $transferPriceProduct->setIdAbstractProduct(
                $this->reader->getAbstractProductIdBySku($transferPriceProduct->getSkuAbstractProduct())
            );
        }
    }

    /**
     * @param PriceProductTransfer $transferPriceProduct
     */
    protected function loadConcreteProductIdForPriceProductTransfer(PriceProductTransfer $transferPriceProduct)
    {
        if (
            $transferPriceProduct->getIdProduct() === null &&
            $this->reader->hasConcreteProduct($transferPriceProduct->getSkuProduct())
        ) {
            $transferPriceProduct->setIdProduct(
                $this->reader->getConcreteProductIdBySku($transferPriceProduct->getSkuProduct())
            );
        }
    }

    /**
     * @param PriceProductTransfer $transferPriceProduct
     * @param SpyPriceProduct $priceProductEntity
     *
     * @return SpyPriceProduct
     */
    protected function savePriceProductEntity(PriceProductTransfer $transferPriceProduct, SpyPriceProduct $priceProductEntity)
    {
        $priceType = $this->reader->getPriceTypeByName($transferPriceProduct->getPriceTypeName());
        $priceProductEntity
            ->setPriceType($priceType)
            ->setPrice($transferPriceProduct->getPrice());

        if ($transferPriceProduct->getIdProduct()) {
            $priceProductEntity->setFkProduct($transferPriceProduct->getIdProduct());
        } else {
            $priceProductEntity->setFkAbstractProduct($transferPriceProduct->getIdAbstractProduct());
        }

        $priceProductEntity->save();

        return $priceProductEntity;
    }

    /**
     * @param string $itemType
     * @param int $itemId
     */
    protected function insertTouchRecord($itemType, $itemId)
    {
        $this->touchFacade->touchActive($itemType, $itemId);
    }

    /**
     * @param PriceProductTransfer $transferPriceProduct
     *
     * @throws \Exception
     * @throws PropelException
     *
     * @return PriceProductTransfer
     */
    protected function setPriceType(PriceProductTransfer $transferPriceProduct)
    {
        if ($transferPriceProduct->getPriceTypeName() === null) {
            $transferPriceProduct->setPriceTypeName($this->priceSettings->getPriceTypeDefaultName());
        }

        return $transferPriceProduct;
    }

    /**
     * @param int $idPriceProduct
     *
     * @throws \Exception
     *
     * @return SpyPriceProduct
     */
    protected function getPriceProductById($idPriceProduct)
    {
        $priceProductEntity = $this->queryContainer->queryPriceProductEntity($idPriceProduct)->find();
        if (!count($priceProductEntity) > 0) {
            throw new \Exception(self::ENTITY_NOT_FOUND);
        }

        return $this->queryContainer->queryPriceProductEntity($idPriceProduct)->findOne();
    }

    /**
     * @param PriceProductTransfer $transferPriceProduct
     *
     * @return bool
     */
    protected function isPriceTypeExistingForAbstractProduct(PriceProductTransfer $transferPriceProduct)
    {
        $priceType = $this->reader->getPriceTypeByName($transferPriceProduct->getPriceTypeName());
        $priceEntities = $this->queryContainer
            ->queryPriceEntityForAbstractProduct($transferPriceProduct->getSkuAbstractProduct(), $priceType);

        return $priceEntities->count() > 0;
    }

    /**
     * @param int $idConcreteProduct
     * @param string $priceType
     * @param \DateTime $date
     *
     * @return SpyPriceProduct
     */
    protected function getPriceEntityForConcreteProduct($idConcreteProduct, $priceType, \DateTime $date)
    {
        $idPriceType = $this->reader->getPriceTypeByName($priceType)->getIdPriceType();

        return $this->queryContainer->queryPriceEntityForConcreteProduct($idConcreteProduct, $date, $idPriceType)->findOne();
    }

    /**
     * @param PriceProductTransfer $transferPriceProduct
     *
     * @return bool
     */
    protected function isPriceTypeExistingForConcreteProduct(PriceProductTransfer $transferPriceProduct)
    {
        $priceType = $this->reader->getPriceTypeByName($transferPriceProduct->getPriceTypeName());
        $priceEntities = $this->queryContainer
            ->queryPriceEntityForConcreteProduct($transferPriceProduct->getSkuProduct(), $priceType);

        return $priceEntities->count() > 0;
    }

}
