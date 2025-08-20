<?php
namespace Dnd\OfferManager\Model\Offer;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
{
    $result = [];
    foreach ($this->getCollection()->getItems() as $offer) {
        $result[$offer->getId()] = $offer->getData();
    }
    return [
        'items' => $result,
    ];
}

}
