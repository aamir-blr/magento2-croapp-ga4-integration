<?php
namespace Croapp\Integration\Observer\Cart;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;

class CheckoutViewed implements ObserverInterface
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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $eventName  = 'begin_checkout';
            $eventData = [];
            if (!empty($this->_checkoutSession) && is_object($this->_checkoutSession)) {
                $store = $this->_storeManager->getStore();
                $currency = is_object($store) ? $store->getCurrentCurrencyCode() : null;
                $eventData = $this->_croModel->getCartEventData($this->_checkoutSession->getQuote(), $currency);
            }
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
