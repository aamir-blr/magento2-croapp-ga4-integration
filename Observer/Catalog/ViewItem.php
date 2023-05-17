<?php
namespace Croapp\Integration\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory;
use Psr\Log\LogLevel;

class ViewItem implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockItemRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory
     */
    protected $_configurableProductProductTypeConfigurableFactory;

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
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $_stockItemRepository
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param ConfigurableFactory $_configurableProductProductTypeConfigurableFactory
     * @param \Croapp\Integration\Logger\Logger $_logger
     * @param \Croapp\Integration\Model\Cro $_croModel
     */
    public function __construct(
        Registry $_registry,
        StockRegistryInterface $_stockItemRepository,
        StoreManagerInterface $_storeManager,
        ConfigurableFactory $_configurableProductProductTypeConfigurableFactory,
        \Croapp\Integration\Logger\Logger $_logger,
        \Croapp\Integration\Model\Cro $_croModel
    ) {
        $this->_registry = $_registry;
        $this->_stockItemRepository = $_stockItemRepository;
        $this->_storeManager = $_storeManager;
        $this->_configurableProductProductTypeConfigurableFactory = $_configurableProductProductTypeConfigurableFactory;
        $this->_logger = $_logger;
        $this->_croModel = $_croModel;
    }

    /**
     * Add view_item event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $eventName = 'view_item';
            $eventData = [];
            $product = $this->_registry->registry('current_product');
            if (is_object($product)) {
                $itemImage = null;
                $itemInternalType = $product->getTypeId();
                $store = $this->_storeManager->getStore();
                $stock = $this->_stockItemRepository->getStockItem($product->getId());
                if (!empty($product->getImage())) {
                    $itemImage = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    $itemImage.= 'catalog/product'.$product->getImage();
                }

                $eventData = [
                    'currency' => is_object($store) ? $store->getCurrentCurrencyCode() : null,
                    'value' => $product->getFinalPrice(),
                ];

                $item = [
                    'item_id' => $product->getId(),
                    'item_name' => $product->getName(),
                    'price' => $product->getFinalPrice(),

                    // additional params
                    'item_url' => $product->getProductUrl(),
                    'item_image' => $itemImage,
                    'item_sku' => $product->getSku(),
                    'item_internal_type' => $itemInternalType,
                    'item_type' => $itemInternalType == "configurable" ? 'parent' : 'simple',
                    'item_original_price' => $product->getPrice(),
                    'item_in_stock' => is_object($stock) ? $stock->getIsInStock() : null,
                ];
                $eventData['items'][] = $item;
            }
            $this->_croModel->storeGaEvents($eventName, $eventData);
        } catch (\Exception $e) {
            $this->_logger->log(LogLevel::WARNING, $e->getMessage());
        }
    }
}
