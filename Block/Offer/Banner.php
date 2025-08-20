<?php
namespace Dnd\OfferManager\Block\Offer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class Banner extends Template
{
    /**
     * @var OfferCollectionFactory
     */
    protected $offerCollectionFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var LayerResolver
     */
    protected $layerResolver;

    /**
     * @param Context $context
     * @param OfferCollectionFactory $offerCollectionFactory
     * @param DateTime $date
     * @param LayerResolver $layerResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        OfferCollectionFactory $offerCollectionFactory,
        DateTime $date,
        LayerResolver $layerResolver,
        array $data = []
    ) {
        $this->offerCollectionFactory = $offerCollectionFactory;
        $this->date = $date;
        $this->layerResolver = $layerResolver;
        parent::__construct($context, $data);
    }

    /**
     * @return \Dnd\OfferManager\Model\ResourceModel\Offer\Collection
     */
    public function getActiveOffers()
    {
        $collection = $this->offerCollectionFactory->create();
        $currentDate = $this->date->gmtDate();
        $collection->addFieldToFilter('start_date', ['lteq' => $currentDate])
                   ->addFieldToFilter('end_date', ['gteq' => $currentDate]);

        $category = $this->getCurrentCategory();
        if ($category) {
            $collection->addFieldToFilter('category_ids', ['finset' => $category->getId()]);
        }

        return $collection;
    }

    /**
     * @return Category|null
     */
    public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }
}