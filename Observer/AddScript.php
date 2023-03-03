<?php
namespace Croapp\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\LayoutInterface;

class AddScript implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

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
     * @param \Magento\Framework\View\LayoutInterface $_layout
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Helper\Data $_dataHelper
     * @param \Croapp\Integration\Model\Cro $_croModel
     */
    public function __construct(
        LayoutInterface $_layout,
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Helper\Data $_dataHelper,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_dataHelper = $_dataHelper;
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
        $this->_layout = $_layout;
    }

    /**
     * Insert gtag script to head
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $initScript = $this->_croModel->getInitScript();
            if (empty($initScript)) {
                return;
            }

            $layout = $this->_layout;
            if (!is_object($layout)) {
                return;
            }

            $head = $layout->getBlock('head.additional');
            if (!is_object($head)) {
                return;
            }
            $head->append($initScript);
            $this->attachEvents($head);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * Add gtag events to head
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $head
     * @return void
     */
    private function attachEvents($head)
    {
        if (!is_object($head)) {
            return;
        }
        $gaEvents = $this->_croModel->fetchGaEvents();
        if (empty($gaEvents) || !is_array($gaEvents)) {
            return;
        }
        foreach ($gaEvents as $gaEvent) {
            $eventBlock = $this->_croModel->getEventScript($gaEvent);
            $head->append($eventBlock);
        }
    }
}
