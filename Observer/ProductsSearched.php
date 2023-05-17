<?php
namespace Croapp\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Psr\Log\LogLevel;

class ProductsSearched implements ObserverInterface
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
     * @var \Croapp\Integration\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Model\Cro $_croModel
     * @param \Croapp\Integration\Helper\Data $_dataHelper
     */
    public function __construct(
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel,
        \Croapp\Integration\Helper\Data $_dataHelper
    ) {
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
        $this->_dataHelper = $_dataHelper;
    }

    /**
     * Add view_search_results event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $eventName = 'view_search_results';
            $eventData = [];
            $query = $observer->getDataObject();
            if (is_object($query)) {
                $eventData['search_term'] = $this->_dataHelper->sanitizeParam($query->getQueryText());
            }
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->log(LogLevel::WARNING, $e->getMessage());
        }
    }
}
