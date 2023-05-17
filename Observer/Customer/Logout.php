<?php
namespace Croapp\Integration\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LogLevel;

class Logout implements ObserverInterface
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
     * Constructor
     *
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Model\Cro $_croModel
     */
    public function __construct(
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
    }

    /**
     * Add logout event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customer = $observer->getCustomer();
            $eventName = 'logout';
            $eventData = $this->_croModel->getCustomerData($customer);
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->log(LogLevel::WARNING, null, $e->getMessage());
        }
    }
}
