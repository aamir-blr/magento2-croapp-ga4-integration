<?php
namespace Croapp\Integration\Helper;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Interface Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Interface Module List
     *
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * Interface Config Writer
     *
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_configWriter;
    
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Curl
     */
    protected $_curlClient;

    /**
     * @var \Croapp\Integration\Logger\Logger
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ModuleListInterface $moduleList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $_configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface $_cacheTypeList
     * @param \Magento\Framework\Message\ManagerInterface $_messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param Curl $_curlClient
     * @param \Croapp\Integration\Logger\Logger $_logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ModuleListInterface $moduleList,
        WriterInterface $_configWriter,
        TypeListInterface $_cacheTypeList,
        ManagerInterface $_messageManager,
        StoreManagerInterface $_storeManager,
        Curl $_curlClient,
        \Croapp\Integration\Logger\Logger $_logger,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_moduleList = $moduleList;
        $this->_configWriter = $_configWriter;
        $this->_cacheTypeList = $_cacheTypeList;
        $this->_messageManager = $_messageManager;
        $this->_storeManager = $_storeManager;
        $this->_curlClient = $_curlClient;
        $this->_logger = $_logger;
        parent::__construct(
            $context
        );
    }

    /**
     * Get Event Type
     *
     * @param string $event - event name
     */
    public function getEventType($event)
    {
        $eventMap = [
            'home_viewed'         =>  'home_viewed',
            'content_page_viewed' =>  'content_page_viewed',
            'view_item_list'      =>  'view_item_list',
            'view_item'           =>  'view_item',
            'view_search_results' =>  'view_search_results',
            'sign_up'             =>  'sign_up',
            'login'               =>  'login',
            'logout'              =>  'logout',
            'add_to_cart'         =>  'add_to_cart',
            'remove_from_cart'    =>  'remove_from_cart',
            'view_cart'           =>  'view_cart',
            'add_to_wishlist'     =>  'add_to_wishlist',
            'add_to_compare'      =>  'add_to_compare',
            'remove_from_compare' =>  'remove_from_compare',
            'begin_checkout'      =>  'begin_checkout',
            'purchase'            =>  'purchase',
        ];

        if (!empty($eventMap[$event])) {
            return $eventMap[$event];
        } else {
            return "unknown-$event";
        }
    }

    /**
     * Check if module is enabled
     */
    public function isEnabled()
    {
        if ($this->getGaId()) {
            return 1;
        } else {
            return false;
        }
    }

    /**
     * Get Google Analytics Measurement ID
     */
    public function getGaId()
    {
        $gaId = $this->scopeConfig->getValue('croapp/ga_configuration/gaId', ScopeInterface::SCOPE_STORE);
        if (empty($gaId)) {
            return false;
        } else {
            return $gaId;
        }
    }

    /**
     * Get CRO APP Account ID
     */
    public function getAccountId()
    {
        $accountId = $this->scopeConfig->getValue('croapp/croapp_configuration/accountId', ScopeInterface::SCOPE_STORE);
        if (empty($accountId)) {
            return false;
        } else {
            return $accountId;
        }
    }

    /**
     * Get CRO APP Account ID
     */
    public function genAccIdIfEmpty()
    {
        if (!$this->getAccountId()) {
            $email = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $store = $this->_storeManager->getStore();
            $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $accParams = ['shopDomain' => $baseUrl, 'email' => $email, 'platform' => 'magento2'];
            $accountId = $this->caQuickRegister($accParams);
            if (!empty($accountId)) {
                $this->_configWriter->save('croapp/croapp_configuration/accountId', $accountId, 'default', 0);
                $this->_cacheTypeList->cleanType('config');
                $this->_messageManager->addSuccess(__("Cro App Account Id Generated Successfully !"));
            }
        }
    }

    /**
     * Register / Create Account In CRO App
     *
     * @param array $params - params to register user in cro app
     */
    public function caQuickRegister($params)
    {
        $caApiUrl = 'https://account.croapp.com/api/user/quick-register';
        $this->_curlClient->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curlClient->post($caApiUrl, $params);
        $response = $this->_curlClient->getBody();
        $jsonResponse = json_decode($response, true);

        if (!empty($jsonResponse['account']) && !empty($jsonResponse['account']['id'])) {
            return $jsonResponse['account']['id'];
        }
    }

    /**
     * Get Module Version
     */
    public function getModuleVersion()
    {
        $ccModule = $this->_moduleList
            ->getOne('Croapp_Integration');
        return !empty($ccModule['setup_version']) ? $ccModule['setup_version'] : null;
    }

    /**
     * Sanitize Parameter
     *
     * @param string $param - parameter to be sanitized
     */
    public function sanitizeParam($param)
    {
        return strip_tags($param);
    }
}
