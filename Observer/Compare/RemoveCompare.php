<?php
namespace Croapp\Integration\Observer\Compare;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;

class RemoveCompare implements ObserverInterface
{
    /**
     * @var \Croapp\Integration\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Croapp\Integration\Model\Cro
     */
    protected $_croModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Model\Cro $_croModel
     */
    public function __construct(
        StoreManagerInterface $_storeManager,
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_storeManager = $_storeManager;
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
    }

    /**
     * Add remove_from_compare event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();

            $eventName  = 'remove_from_compare';
            $eventData = [];

            if (is_object($product)) {
                $store = $this->_storeManager->getStore();
                $currency = is_object($store) ? $store->getCurrentCurrencyCode() : null;
                $eventData['currency'] = $currency;
        
                $item = [];
                $item['item_id'] = $product->getProductId();
                $eventData['items'][] = $item;
            }
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
