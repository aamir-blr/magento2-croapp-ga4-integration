<?php
namespace Croapp\Integration\Observer\Cart;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Psr\Log\LogLevel;

class AddCart implements ObserverInterface
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Model\Cro $_croModel
     */
    public function __construct(
        StoreManagerInterface $_storeManager,
        Session $_checkoutSession,
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_checkoutSession = $_checkoutSession;
        $this->_storeManager = $_storeManager;
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
    }

    /**
     * Add add_to_cart event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            $eventName  = 'add_to_cart';
            $eventData = [];

            if (is_object($product) && is_object($this->_checkoutSession)) {
                $item = $this->_checkoutSession->getQuote()->getItemByProduct($product);
                $store = $this->_storeManager->getStore();
                $currency = is_object($store) ? $store->getCurrentCurrencyCode() : null;
    
                if (is_object($item)) {
                    $eventData['currency'] = $currency;
                    $eventData['value'] = $item->getPrice();
        
                    $cartItem = [];
                    $cartItem['item_id'] = $item->getProductId();
                    $cartItem['item_name'] = str_replace("'", "", $item->getName());
                    $cartItem['price'] = $item->getPrice();
                    $cartItem['quantity'] = $item->getQty();
                    $eventData['items'][] = $cartItem;
                }
            }
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->log(LogLevel::WARNING, $e->getMessage());
        }
    }
}
