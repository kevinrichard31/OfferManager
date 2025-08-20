<?php
namespace Dnd\OfferManager\Model\ResourceModel\Offer\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * Override _initSelect to add category names join
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        
        // Add category_ids to the select
        $this->addFieldToSelect('category_ids');
        
        return $this;
    }
}
