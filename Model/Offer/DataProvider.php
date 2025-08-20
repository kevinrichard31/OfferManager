<?php
namespace Dnd\OfferManager\Model\Offer;

use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Dnd\OfferManager\Model\ResourceModel\Offer\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        StoreManagerInterface $storeManager = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Dnd\OfferManager\Model\Offer $offer */
        foreach ($items as $offer) {
            $data = $offer->getData();
            if (!empty($data['image'])) {
                $data['image'] = [[
                    'name' => basename($data['image']),
                    'url' => rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/') . '/' . ltrim($data['image'], '/'),
                ]];
            }
            $this->loadedData[$offer->getId()] = $data;
        }

        $data = $this->dataPersistor->get('dnd_offer_manager_offer');
        if (!empty($data)) {
            $offer = $this->collection->getNewEmptyItem();
            $offer->setData($data);
            $normalized = $offer->getData();
            if (!empty($normalized['image']) && is_string($normalized['image'])) {
                $normalized['image'] = [[
                    'name' => basename($normalized['image']),
                    'url' => rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/') . '/' . ltrim($normalized['image'], '/'),
                ]];
            }
            $this->loadedData[$offer->getId()] = $normalized;
            $this->dataPersistor->clear('dnd_offer_manager_offer');
        }

        return $this->loadedData;
    }
}
