<?php
namespace Dnd\OfferManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Offer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dnd_offer_manager_offer', 'offer_id');
    }
}
