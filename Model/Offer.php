<?php
namespace Dnd\OfferManager\Model;

use Magento\Framework\Model\AbstractModel;

class Offer extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Dnd\OfferManager\Model\ResourceModel\Offer::class);
    }
}