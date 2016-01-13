<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Price\Business\Model;

use Generated\Shared\Transfer\PriceProductTransfer;
use Orm\Zed\Price\Persistence\SpyPriceProduct;

class BulkWriter extends Writer implements BulkWriterInterface
{

    /**
     * @var array
     */
    protected $recordsToTouch = [];

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

        $this->loadProductAbstractIdForPriceProductTransfer($priceProductTransfer);
        $this->loadConcreteProductIdForPriceProductTransfer($priceProductTransfer);

        $entity = new SpyPriceProduct();
        $newPrice = $this->savePriceProductEntity($priceProductTransfer, $entity);

        if ($priceProductTransfer->getIdProduct()) {
            $this->addRecordToTouch(self::TOUCH_PRODUCT, $priceProductTransfer->getIdProduct());
        }

        return $newPrice;
    }

    /**
     * @param PriceProductTransfer $priceProductTransfer
     *
     * @throws \Exception
     *
     * @return void
     */
    public function setPriceForProduct(PriceProductTransfer $priceProductTransfer)
    {
        $priceProductTransfer = $this->setPriceType($priceProductTransfer);

        $this->loadProductAbstractIdForPriceProductTransfer($priceProductTransfer);
        $this->loadConcreteProductIdForPriceProductTransfer($priceProductTransfer);

        $priceProductEntity = $this->getPriceProductById($priceProductTransfer->getIdPriceProduct());
        $this->savePriceProductEntity($priceProductTransfer, $priceProductEntity);

        if ($priceProductTransfer->getIdProduct()) {
            $this->addRecordToTouch(self::TOUCH_PRODUCT, $priceProductTransfer->getIdProduct());
        }
    }

    /**
     * @param string $itemType
     * @param int $itemId
     *
     * @return void
     */
    protected function addRecordToTouch($itemType, $itemId)
    {
        $this->recordsToTouch[$itemType][] = $itemId;
    }

    /**
     * @return void
     */
    public function flush()
    {
        foreach ($this->recordsToTouch as $itemType => $itemIds) {
            $this->touchFacade->bulkTouchActive($itemType, $itemIds);
        }
        $this->recordsToTouch = [];
    }

}
