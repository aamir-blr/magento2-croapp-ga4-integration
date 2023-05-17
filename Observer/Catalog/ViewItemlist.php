<?php
namespace Croapp\Integration\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Psr\Log\LogLevel;

class ViewItemlist implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

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
     * @param \Magento\Framework\Registry $_registry
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Model\Cro $_croModel
     */
    public function __construct(
        Registry $_registry,
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_registry = $_registry;
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
    }

    /**
     * Add view_item_list event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $eventName = 'view_item_list';
            $eventData = [];
            $category = $this->_registry->registry('current_category');
            if (is_object($category)) {
                $eventData['item_list_id'] = $category->getId();
                $eventData['item_list_name'] = $category->getName();
                $eventData['items'] = [];
            }
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->log(LogLevel::WARNING, $e->getMessage());
        }
    }
}
