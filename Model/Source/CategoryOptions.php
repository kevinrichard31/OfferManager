<?php
namespace Dnd\OfferManager\Model\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryOptions implements OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->getCategoryOptions();
        }
        return $this->options;
    }

    /**
     * Get category options with hierarchy
     *
     * @return array
     */
    protected function getCategoryOptions()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('level')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSort('path', 'ASC');

        $options = [];
        foreach ($collection as $category) {
            if ($category->getLevel() >= 2) { // Skip root and default category
                $level = $category->getLevel() - 2; // Adjust level for display
                $prefix = str_repeat('-- ', $level);
                $options[] = [
                    'value' => $category->getId(),
                    'label' => $prefix . $category->getName()
                ];
            }
        }

        return $options;
    }
}
