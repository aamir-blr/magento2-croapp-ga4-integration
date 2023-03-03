<?php
namespace Croapp\Integration\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SignUp implements ObserverInterface
{
    /**
     * @var \Croapp\Integration\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Croapp\Integration\Model\Cro
     */
    protected $_croModel;

    public function __construct(
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customer = $observer->getCustomer();
            $eventName = 'sign_up';
            $eventData = $this->_croModel->getCustomerData($customer);
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->error(null, $e->getMessage());
        }
    }
}
