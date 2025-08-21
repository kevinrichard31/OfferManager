<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Test\Unit\Controller\Adminhtml\Offer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory;
use Dnd\OfferManager\Model\ResourceModel\Offer\Collection;
use Dnd\OfferManager\Model\OfferFactory;
use Dnd\OfferManager\Model\Offer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Dnd\OfferManager\Controller\Adminhtml\Offer\MassDelete;

/**
 * Test class for MassDelete controller
 */
class MassDeleteTest extends TestCase
{
    /**
     * @var MassDelete
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Filter|MockObject
     */
    private $filterMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var OfferFactory|MockObject
     */
    private $offerFactoryMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var Offer|MockObject
     */
    private $offerModelMock;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->filterMock = $this->createMock(Filter::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->offerFactoryMock = $this->createMock(OfferFactory::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->offerModelMock = $this->createMock(Offer::class);

        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactoryMock);

        $this->resultFactoryMock->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->controller = new MassDelete(
            $this->contextMock,
            $this->filterMock,
            $this->collectionFactoryMock,
            $this->offerFactoryMock
        );
    }

    /**
     * Test execute method with successful mass deletion
     */
    public function testExecuteSuccessfulMassDeletion(): void
    {
        $collectionSize = 3;
        $offerIds = [1, 2, 3];

        $offers = [];
        foreach ($offerIds as $id) {
            $offer = $this->createMock(Offer::class);
            $offer->method('getId')->willReturn($id);
            $offers[] = $offer;
        }

        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);
        $this->filterMock->method('getCollection')
            ->with($this->collectionMock)
            ->willReturn($this->collectionMock);

        $this->collectionMock->method('getSize')->willReturn($collectionSize);
        $this->collectionMock->method('getIterator')->willReturn(new \ArrayIterator($offers));

        $offerModels = [];
        foreach ($offerIds as $id) {
            $offerModel = $this->createMock(Offer::class);
            $offerModel->method('load')->with($id)->willReturnSelf();
            $offerModel->expects($this->once())->method('delete');
            $offerModels[] = $offerModel;
        }

        $this->offerFactoryMock->method('create')
            ->willReturnOnConsecutiveCalls(...$offerModels);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('A total of 3 offer(s) have been deleted.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with empty collection
     */
    public function testExecuteWithEmptyCollection(): void
    {
        $collectionSize = 0;

        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);
        $this->filterMock->method('getCollection')
            ->with($this->collectionMock)
            ->willReturn($this->collectionMock);

        $this->collectionMock->method('getSize')->willReturn($collectionSize);
        $this->collectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->offerFactoryMock->expects($this->never())->method('create');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('A total of 0 offer(s) have been deleted.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with general Exception
     */
    public function testExecuteWithGeneralException(): void
    {
        $exception = new \Exception('Database error');

        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);
        $this->filterMock->method('getCollection')
            ->with($this->collectionMock)
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'An error occurred while deleting offers.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with single offer deletion
     */
    public function testExecuteWithSingleOffer(): void
    {
        $collectionSize = 1;
        $offerId = 5;

        $offer = $this->createMock(Offer::class);
        $offer->method('getId')->willReturn($offerId);

        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);
        $this->filterMock->method('getCollection')
            ->with($this->collectionMock)
            ->willReturn($this->collectionMock);

        $this->collectionMock->method('getSize')->willReturn($collectionSize);
        $this->collectionMock->method('getIterator')->willReturn(new \ArrayIterator([$offer]));

        $offerModel = $this->createMock(Offer::class);
        $offerModel->method('load')->with($offerId)->willReturnSelf();
        $offerModel->expects($this->once())->method('delete');

        $this->offerFactoryMock->method('create')->willReturn($offerModel);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('A total of 1 offer(s) have been deleted.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }
}
