<?php
namespace Croapp\Integration\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;

class GenAccountId implements ObserverInterface
{
    /**
     * @var \Croapp\Integration\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Croapp\Integration\Logger\Logger
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param \Croapp\Integration\Helper\Data $_dataHelper
     * @param \Croapp\Integration\Logger\Logger $_logger
     */
    public function __construct(
        \Croapp\Integration\Helper\Data $_dataHelper,
        \Croapp\Integration\Logger\Logger $_logger,
    ) {
        $this->_dataHelper = $_dataHelper;
        $this->_logger = $_logger;
    }

    /**
     * Generate CRO App Account ID, if empty
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->_dataHelper->genAccIdIfEmpty();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
