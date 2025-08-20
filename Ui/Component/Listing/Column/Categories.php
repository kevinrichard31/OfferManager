<?php
namespace Dnd\OfferManager\Ui\Component\Listing\Column;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Categories extends Column
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CategoryFactory $categoryFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryFactory $categoryFactory,
        array $components = [],
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['category_ids']) && !empty($item['category_ids'])) {
                    $categoryIds = explode(',', $item['category_ids']);
                    $categoryNames = [];
                    
                    foreach ($categoryIds as $categoryId) {
                        $categoryId = trim($categoryId);
                        if ($categoryId) {
                            try {
                                $category = $this->categoryFactory->create()->load($categoryId);
                                if ($category->getId() && $category->getIsActive()) {
                                    $categoryNames[] = $category->getName();
                                }
                            } catch (\Exception $e) {
                                // Skip invalid categories
                                continue;
                            }
                        }
                    }
                    
                    $item['category_names'] = implode(', ', $categoryNames);
                } else {
                    $item['category_names'] = '';
                }
            }
        }
        return $dataSource;
    }
}
