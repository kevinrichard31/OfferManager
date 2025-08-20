<?php
namespace Dnd\OfferManager\Test\Unit\Model\Source;

use Dnd\OfferManager\Model\Source\CategoryOptions;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryOptionsTest extends TestCase
{
    /**
     * @var CategoryOptions
     */
    private $categoryOptions;

    /**
     * @var CollectionFactory|MockObject
     */
    private $categoryCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $categoryCollectionMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->categoryCollectionFactoryMock = $this->createMock(originalClassName: CollectionFactory::class);
        $this->categoryCollectionMock = $this->createMock(Collection::class);

        $this->categoryCollectionFactoryMock->method('create')->willReturn($this->categoryCollectionMock);

        $this->categoryOptions = new CategoryOptions($this->categoryCollectionFactoryMock);
    }

    /**
     * Create a mock category with specified properties
     *
     * @param int $id
     * @param string $name
     * @param int $level
     * @param bool $isActive
     * @return Category|MockObject
     */
    private function createMockCategory($id, $name, $level, $isActive)
    {
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn($id);
        $category->method('getName')->willReturn($name);
        $category->method('getLevel')->willReturn($level);
        $category->method('getIsActive')->willReturn($isActive);
        
        return $category;
    }

    /**
     * Setup category collection mock with given categories
     *
     * @param array $categories
     */
    private function setupCategoryCollectionMock($categories)
    {
        $this->categoryCollectionMock
            ->method('addAttributeToSelect')
            ->willReturnSelf();

        $this->categoryCollectionMock
            ->method('addAttributeToSort')
            ->willReturnSelf();

        // This simulates the filtering. The code under test calls addAttributeToFilter,
        // and then iterates. The iterator should return only the active categories.
        $this->categoryCollectionMock
            ->method('addAttributeToFilter')
            ->with('is_active', 1)
            ->willReturnCallback(function () use ($categories) {
                $activeCategories = array_filter($categories, function ($category) {
                    return $category->getIsActive();
                });
                // We re-configure the mock on the fly to return the filtered list when iterated.
                $this->categoryCollectionMock->method('getIterator')->willReturn(new \ArrayIterator($activeCategories));
                return $this->categoryCollectionMock;
            });
    }

    /**
     * Test toOptionArray with hierarchical categories and correct prefixing
     */
    public function testToOptionArrayWithHierarchicalCategories()
    {
        // Create mock categories with different levels
        $rootCategory = $this->createMockCategory(1, 'Root Catalog', 0, true);
        $defaultCategory = $this->createMockCategory(2, 'Default Category', 1, true);
        $electronicsCategory = $this->createMockCategory(3, 'Electronics', 2, true);
        $computersCategory = $this->createMockCategory(4, 'Computers', 3, true);
        $laptopsCategory = $this->createMockCategory(5, 'Laptops', 4, true);
        $deepCategory = $this->createMockCategory(6, 'Deep', 5, true);

        $categories = [
            $rootCategory,
            $defaultCategory,
            $electronicsCategory,
            $computersCategory,
            $laptopsCategory,
            $deepCategory
        ];

        $this->setupCategoryCollectionMock($categories);

        $result = $this->categoryOptions->toOptionArray();

        // Expected result should skip root (level 0) and default (level 1) categories
        // and have correct prefixes
        $expected = [
            ['value' => 3, 'label' => 'Electronics'],
            ['value' => 4, 'label' => '-- Computers'],
            ['value' => 5, 'label' => '-- -- Laptops'],
            ['value' => 6, 'label' => '-- -- -- Deep']
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test toOptionArray with only active categories
     */
    public function testToOptionArrayWithOnlyActiveCategories()
    {
        $activeCategory = $this->createMockCategory(3, 'Active Category', 2, true);
        $inactiveCategory = $this->createMockCategory(4, 'Inactive Category', 2, false);

        $categories = [$activeCategory, $inactiveCategory];

        $this->setupCategoryCollectionMock($categories);

        $result = $this->categoryOptions->toOptionArray();

        // Should only include active categories
        $expected = [
            ['value' => 3, 'label' => 'Active Category']
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test toOptionArray with empty collection
     */
    public function testToOptionArrayWithEmptyCollection()
    {
        $this->setupCategoryCollectionMock([]);

        $result = $this->categoryOptions->toOptionArray();

        $this->assertEquals([], $result);
    }
}
