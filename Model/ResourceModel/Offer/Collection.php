<?php
namespace Dnd\OfferManager\Model\ResourceModel\Offer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Dnd\OfferManager\Model\Offer as OfferModel;
use Dnd\OfferManager\Model\ResourceModel\Offer as OfferResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(OfferModel::class, OfferResource::class);
    }
}
