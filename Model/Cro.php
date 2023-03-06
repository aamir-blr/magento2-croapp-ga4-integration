<?php
namespace Croapp\Integration\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Cro extends \Magento\Framework\Session\SessionManager
{
    /**
     * @var \Croapp\Integration\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Croapp\Integration\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Session, used to store events data temporarily
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_fwSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $date;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\LayoutInterface $_layout
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Framework\Session\SessionManagerInterface $_fwSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Helper\Data $_dataHelper
     */
    public function __construct(
        LayoutInterface $_layout,
        StoreManagerInterface $_storeManager,
        SessionManagerInterface $_fwSession,
        DateTime $date,
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Helper\Data $_dataHelper
    ) {
        $this->_layout = $_layout;
        $this->_storeManager = $_storeManager;
        $this->_fwSession = $_fwSession;
        $this->date = $date;
        $this->_logger = $_logger;
        $this->_dataHelper = $_dataHelper;
    }

    /**
     * Get initial script block to be added in the page
     */
    public function getInitScript()
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return;
        }

        $gaId = $this->_dataHelper->getGaId();
        if (empty($gaId)) {
            return;
        }
        $script = $this->_layout->createBlock(\Croapp\Integration\Block\Script::class)
                ->setTemplate('Croapp_Integration::init.phtml')
                ->assign([
                    'gaId' => $gaId
                ]);
        return $script;
    }

    /**
     * Get gtag event script block to be added in the page
     *
     * @param array $eventData - event data to be added to gtag script
     */
    public function getEventScript($eventData = [])
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return;
        }

        $event = isset($eventData['event']) ? $eventData['event'] : 'unknown-event';
        unset($eventData['event']);
        $script = $this->_layout->createBlock(\Croapp\Integration\Block\Script::class)
                ->setTemplate('Croapp_Integration::event.phtml')
                ->assign([
                    'eventData' => json_encode($eventData),
                    'event' => $event
                ]);
        return $script;
    }

    /**
     * Store gtag events data in session
     *
     * @param string $eventName - name of the event
     * @param array $eventData - event data to be added to gtag script
     */
    public function storeGaEvents($eventName, $eventData = [])
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return;
        }
        $gaEvents = $this->_fwSession->getGaEvents();
        $eventData['event'] = $this->_dataHelper->getEventType($eventName);
        $eventLimit = 10;
        if (empty($gaEvents)) {
            $gaEvents = [];
            $gaEvents[] = $this->addMetaData($eventData);
        } elseif (count($gaEvents) >= $eventLimit) {
            $eventIndex = $eventLimit - 1;
            $gaEvents[$eventIndex] = $this->addMetaData($eventData);
        } else {
            $gaEvents[] = $this->addMetaData($eventData);
        }

        $this->_fwSession->setGaEvents($gaEvents);
    }

    /**
     * Fetch gtag events data in session
     */
    public function fetchGaEvents()
    {
        if ($this->_dataHelper->isEnabled() == false) {
            return;
        }

        $gaEvents = $this->_fwSession->getGaEvents();
        $this->_fwSession->setGaEvents();
        if (empty($gaEvents)) {
            return [];
        } else {
            return $gaEvents;
        }
    }

    /**
     * Add meta data - pv (plugin_version) to gtag events, can be used to debug later
     *
     * @param array $eventData - event data to be added to gtag script
     */
    public function addMetaData($eventData = [])
    {
        $eventData['pv'] = $this->_dataHelper->getModuleVersion();
        $eventData['dt'] = $this->date->gmtTimestamp();
        return $eventData;
    }

    /**
     * Get cart event data
     *
     * @param \Magento\Quote\Model\Quote $quote - event data to be added to gtag script
     * @param string $currency - currency of cart items
     */
    public function getCartEventData($quote, $currency)
    {
        if (!is_object($quote)) {
            return;
        }

        $cartItems = $quote->getAllVisibleItems();
        $eventData = [];
        $eventData['currency'] = $currency;
        $eventData['value'] = $quote->getGrandTotal();
        $eventData['items'] = [];
        foreach ($cartItems as $item) {
            $cartItem = [];
            $cartItem['item_id'] = $item->getProductId();
            $cartItem['item_name'] = str_replace("'", "", $item->getName());
            $cartItem['price'] = $item->getPrice();
            $cartItem['quantity'] = $item->getQty();
            $cartItem['item_variant'] = $item->getSku();

            // additional params
            $cartItem['item_sku'] = $item->getSku();
            $cartItem['item_custom_options'] = $this->getCartItemOptions($item);
            $product = $item->getProduct();
            if (is_object($product)) {
                $cartItem['item_url'] = $product->getProductUrl();
                if (!empty($product->getSmallImage())) {
                    $store = $this->_storeManager->getStore();
                    $cartItem['item_image'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    $cartItem['item_image'].= 'catalog/product'.$product->getSmallImage();
                }
            }
            $eventData['items'][] = $cartItem;
        }

        $eventData['coupon'] = $quote->getCouponCode();
        $eventData['subtotal'] = $quote->getSubtotal();
        $eventData['base_total'] = $quote->getBaseGrandTotal();
        return $eventData;
    }

    /**
     * Get cart item custom options
     *
     * @param \Magento\Quote\Model\Quote\Item $item - magento quote item
     */
    public function getCartItemOptions($item)
    {
        if (!is_object($item)) {
            return null;
        }

        $product = $item->getProduct();
        if (!is_object($product)) {
            return null;
        }

        $productInstance = $product->getTypeInstance(true);
        if (!is_object($productInstance)) {
            return null;
        }

        $productOptions = $productInstance->getOrderOptions($product);
        $options = !empty($productOptions['options']) ? $productOptions['options'] : null;
        if (empty($options)) {
            return null;
        }

        $customOptions = [];
        foreach ($options as $option) {
            $customOption = [];
            $customOption['label'] = !empty($option['label']) ? $option['label'] : null;
            $customOption['value'] = !empty($option['value']) ? $option['value'] : null;
            $customOption['option_id'] = !empty($option['option_id']) ? $option['option_id'] : null;
            $customOption['option_type'] = !empty($option['option_type']) ? $option['option_type'] : null;
            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    /**
     * Get customer data
     *
     * @param \Magento\Customer\Model\Customer $customer - magento customer
     */
    public function getCustomerData($customer)
    {
        if (!is_object($customer)) {
            return;
        }
        $customerData = [];
        $customerData['email'] = $customer->getEmail();
        $customerData['first_name'] = $customer->getFirstname();
        $customerData['last_name'] = $customer->getLastname();
        $customerData['id'] = $customer->getId();

        return $customerData;
    }
}
