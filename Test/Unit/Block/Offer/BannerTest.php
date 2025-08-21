<?php
namespace Dnd\OfferManager\Test\Unit\Block\Offer;

use Dnd\OfferManager\Block\Offer\Banner;
use Dnd\OfferManager\Model\ResourceModel\Offer\Collection;
use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BannerTest extends TestCase
{
    /**
     * @var Banner
     */
    private $banner;

    /**
     * @var OfferCollectionFactory|MockObject
     */
    private $offerCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $offerCollectionMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateMock;

    /**
     * @var LayerResolver|MockObject
     */
    private $layerResolverMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Layer|MockObject
     */
    private $layerMock;

    /**
     * @var Category|MockObject
     */
    private $categoryMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->offerCollectionFactoryMock = $this->createMock(OfferCollectionFactory::class);
        $this->offerCollectionMock = $this->createMock(Collection::class);
        $this->dateMock = $this->createMock(DateTime::class);
        $this->layerResolverMock = $this->createMock(LayerResolver::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->layerMock = $this->createMock(Layer::class);
        $this->categoryMock = $this->createMock(Category::class);

        $this->offerCollectionFactoryMock->method('create')->willReturn($this->offerCollectionMock);
        $this->layerResolverMock->method('get')->willReturn($this->layerMock);

        $this->banner = new Banner(
            $this->contextMock,
            $this->offerCollectionFactoryMock,
            $this->dateMock,
            $this->layerResolverMock
        );
    }

    /**
     * Data provider for testing getActiveOffers with different dates
     *
     * @return array
     */
    public function getActiveOffersDataProvider()
    {
        return [
            'july_20_with_one_active_offer' => [
                'currentDate' => '2024-07-20 12:00:00',
                'offers' => [
                    [
                        'start_date' => '2024-07-15 00:00:00',
                        'end_date' => '2024-07-25 23:59:59',
                        'category_ids' => '1,2,3',
                        'expected' => true
                    ],
                    [
                        'start_date' => '2024-07-21 00:00:00',
                        'end_date' => '2024-07-30 23:59:59',
                        'category_ids' => '1,2,3',
                        'expected' => false
                    ]
                ],
                'categoryId' => 2,
                'expectedCount' => 1
            ],
            'july_25_with_two_active_offers' => [
                'currentDate' => '2024-07-25 12:00:00',
                'offers' => [
                    [
                        'start_date' => '2024-07-15 00:00:00',
                        'end_date' => '2024-07-25 23:59:59',
                        'category_ids' => '1,2,3',
                        'expected' => true
                    ],
                    [
                        'start_date' => '2024-07-21 00:00:00',
                        'end_date' => '2024-07-30 23:59:59',
                        'category_ids' => '1,2,3',
                        'expected' => true
                    ]
                ],
                'categoryId' => 2,
                'expectedCount' => 2
            ],
            'july_10_with_no_active_offers' => [
                'currentDate' => '2024-07-10 12:00:00',
                'offers' => [
                    [
                        'start_date' => '2024-07-15 00:00:00',
                        'end_date' => '2024-07-25 23:59:59',
                        'category_ids' => '1,2,3',
                        'expected' => false
                    ],
                    [
                        'start_date' => '2024-07-21 00:00:00',
                        'end_date' => '2024-07-30 23:59:59',
                        'category_ids' => '1,2,3',
                        'expected' => false
                    ]
                ],
                'categoryId' => 2,
                'expectedCount' => 0
            ]
        ];
    }

    /**
     * Test getActiveOffers with different dates
     *
     * @param string $currentDate
     * @param array $offers
     * @param int $categoryId
     * @param int $expectedCount
     * @dataProvider getActiveOffersDataProvider
     */
    public function testGetActiveOffers($currentDate, $offers, $categoryId, $expectedCount)
    {
        $this->dateMock->method('gmtDate')->willReturn($currentDate);

        $this->categoryMock->method('getId')->willReturn($categoryId);
        
        $this->layerMock->method('getCurrentCategory')->willReturn($this->categoryMock);

        $this->offerCollectionMock
            ->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $mockOffers = [];

        foreach ($offers as $offerData) {
            if ($offerData['expected']) {
                $offerMock = $this->createMock(\Dnd\OfferManager\Model\Offer::class);
                $offerMock->method('getData')
                    ->willReturnMap([
                        ['start_date', null, $offerData['start_date']],
                        ['end_date', null, $offerData['end_date']],
                        ['category_ids', null, $offerData['category_ids']]
                    ]);
                $mockOffers[] = $offerMock;
            }
        }

        $this->offerCollectionMock->method('getIterator')->willReturn(new \ArrayIterator($mockOffers));
        $this->offerCollectionMock->method('count')->willReturn(count($mockOffers));

        $result = $this->banner->getActiveOffers();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($expectedCount, $result->count());
        
        $this->assertEquals($expectedCount, count($mockOffers));
    }

    /**
     * Test getCurrentCategory with an existing category
     */
    public function testGetCurrentCategoryWithCategory()
    {
        $categoryId = 5;
        $categoryName = 'Test Category';

        $this->categoryMock->method('getId')->willReturn($categoryId);
        $this->categoryMock->method('getName')->willReturn($categoryName);
        
        $this->layerMock->method('getCurrentCategory')->willReturn($this->categoryMock);

        $result = $this->banner->getCurrentCategory();

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($categoryId, $result->getId());
        $this->assertEquals($categoryName, $result->getName());
    }

    /**
     * Test getCurrentCategory with no category (null)
     */
    public function testGetCurrentCategoryWithoutCategory()
    {
        $this->layerMock->method('getCurrentCategory')->willReturn(null);

        $result = $this->banner->getCurrentCategory();

        $this->assertNull($result);
    }

    /**
     * Test getActiveOffers with no current category
     */
    public function testGetActiveOffersWithoutCurrentCategory()
    {
        $currentDate = '2024-07-20 12:00:00';
        
        $this->dateMock->method('gmtDate')->willReturn($currentDate);

        $this->layerMock->method('getCurrentCategory')->willReturn(null);

        $this->offerCollectionMock
            ->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->offerCollectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));
        $this->offerCollectionMock->method('count')->willReturn(0);

        $result = $this->banner->getActiveOffers();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }
}
