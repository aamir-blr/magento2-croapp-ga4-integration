<?php
namespace Croapp\Integration\Helper;

use Magento\Framework\Module\ModuleListInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_moduleList;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ModuleListInterface $moduleList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_moduleList = $moduleList;
        parent::__construct(
            $context
        );
    }

    public function getEventType($event)
    {
        $eventMap = [
            'home_viewed'         =>  'home_viewed',
            'content_page_viewed' =>  'content_page_viewed',
            'collection_viewed'   =>  'collection_viewed',
            'view_item'           =>  'view_item',
            'view_search_results' =>  'view_search_results',
            'sign_up'             =>  'sign_up',
            'login'               =>  'login',
            'logout'              =>  'logout',
            'view_cart'           =>  'view_cart',
            'begin_checkout'      =>  'begin_checkout',
            'purchase'            =>  'purchase',
        ];

        if (!empty($eventMap[$event])) {
            return $eventMap[$event];
        } else {
            return "unknown-$event";
        }
    }

    public function isEnabled()
    {
        if ($this->getGaId()) {
            return 1;
        } else {
            return false;
        }
    }

    public function getGaId()
    {
        $gaId = $this->scopeConfig->getValue('croapp/configuration/gaId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (empty($gaId)) {
            return false;
        } else {
            return $gaId;
        }
    }

    public function getModuleVersion()
    {
        $ccModule = $this->_moduleList
            ->getOne('Croapp_Integration');
        return !empty($ccModule['setup_version']) ? $ccModule['setup_version'] : null;
    }

    public function sanitizeParam($param)
    {
        return strip_tags($param);
    }
}