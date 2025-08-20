<?php
namespace Dnd\OfferManager\Model\Offer;

use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

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
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
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
            $this->loadedData[$offer->getId()] = $offer->getData();
        }

        $data = $this->dataPersistor->get('dnd_offer_manager_offer');
        if (!empty($data)) {
            $offer = $this->collection->getNewEmptyItem();
            $offer->setData($data);
            $this->loadedData[$offer->getId()] = $offer->getData();
            $this->dataPersistor->clear('dnd_offer_manager_offer');
        }

        return $this->loadedData;
    }
}
