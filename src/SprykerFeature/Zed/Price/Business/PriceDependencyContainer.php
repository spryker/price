<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Price\Business;

use SprykerFeature\Zed\Price\Business\Model\BulkWriter;
use SprykerFeature\Zed\Price\Business\Model\Writer;
use SprykerFeature\Zed\Price\Business\Model\Reader;
use Generated\Zed\Ide\FactoryAutoCompletion\PriceBusiness;
use SprykerEngine\Zed\Kernel\Business\AbstractBusinessDependencyContainer;
use SprykerFeature\Zed\Price\Business\Model\BulkWriterInterface;
use SprykerFeature\Zed\Price\Persistence\PriceQueryContainer;
use SprykerFeature\Zed\Price\Business\Model\WriterInterface;
use SprykerFeature\Zed\Price\Business\Model\ReaderInterface;
use SprykerFeature\Zed\Price\Dependency\Facade\PriceToProductInterface;
use SprykerFeature\Zed\Price\Dependency\Facade\PriceToTouchInterface;
use SprykerFeature\Zed\Price\Business\Internal\Install;
use SprykerEngine\Shared\Kernel\Messenger\MessengerInterface;
use SprykerFeature\Zed\Price\PriceConfig;

/**
 * @method PriceConfig getConfig()
 */
class PriceDependencyContainer extends AbstractBusinessDependencyContainer
{

    /**
     * @var PriceQueryContainer
     */
    private $queryContainer;

    /**
     * @var PriceToTouchInterface
     */
    protected $touchFacade;

    /**
     * @var PriceToProductInterface
     */
    protected $productFacade;

    /**
     * @return ReaderInterface
     */
    public function getReaderModel()
    {
        return new Reader(
            $this->getQueryContainer(),
            $this->getProductFacade(),
            $this->getConfig()
        );
    }

    /**
     * @return WriterInterface
     */
    public function getWriterModel()
    {
        return new Writer(
            $this->getLocator(),
            $this->getQueryContainer(),
            $this->getReaderModel(),
            $this->getTouchFacade(),
            $this->getConfig()
        );
    }

    /**
     * @return BulkWriterInterface
     */
    public function getBulkWriterModel()
    {
        return new BulkWriter(
            $this->getLocator(),
            $this->getQueryContainer(),
            $this->getReaderModel(),
            $this->getTouchFacade(),
            $this->getConfig()
        );
    }

    /**
     * @return PriceQueryContainer
     */
    protected function getQueryContainer()
    {
        if (empty($this->queryContainer)) {
            $this->queryContainer = $this->getLocator()->price()->queryContainer();
        }

        return $this->queryContainer;
    }

    /**
     * @return PriceToProductInterface
     */
    protected function getProductFacade()
    {
        return $this->getLocator()->product()->facade();
    }

    /**
     * @return PriceToTouchInterface
     */
    protected function getTouchFacade()
    {
        if (empty($this->touchFacade)) {
            $this->touchFacade = $this->getLocator()->touch()->facade();
        }

        return $this->touchFacade;
    }

    /**
     * @param MessengerInterface $messenger
     *
     * @return Install
     */
    public function getInstaller(MessengerInterface $messenger)
    {
        $installer = new Install(
            $this->getLocator()->price()->facade(),
            $this->getConfig()
        );
        $installer->setMessenger($messenger);

        return $installer;
    }

}
