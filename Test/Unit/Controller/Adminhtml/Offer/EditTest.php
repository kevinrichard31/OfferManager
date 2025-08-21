<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Test\Unit\Controller\Adminhtml\Offer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Dnd\OfferManager\Controller\Adminhtml\Offer\Edit;
use Dnd\OfferManager\Model\OfferFactory;
use Dnd\OfferManager\Model\Offer;

/**
 * Test class for Edit controller
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var OfferFactory|MockObject
     */
    private $offerFactoryMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private $dataPersistorMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Page|MockObject
     */
    private $resultPageMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Offer|MockObject
     */
    private $offerModelMock;

    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    private $pageTitleMock;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->offerFactoryMock = $this->createMock(OfferFactory::class);
        $this->dataPersistorMock = $this->createMock(DataPersistorInterface::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultPageMock = $this->createMock(Page::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->offerModelMock = $this->createMock(Offer::class);
        $this->pageConfigMock = $this->createMock(Config::class);
        $this->pageTitleMock = $this->createMock(Title::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->resultRedirectFactoryMock->method('create')->willReturn($this->resultRedirectMock);
        $this->resultPageFactoryMock->method('create')->willReturn($this->resultPageMock);

        // Setup page configuration chain
        $this->resultPageMock->method('getConfig')->willReturn($this->pageConfigMock);
        $this->pageConfigMock->method('getTitle')->willReturn($this->pageTitleMock);
        $this->resultPageMock->method('setActiveMenu')->willReturnSelf();
        $this->resultPageMock->method('addBreadcrumb')->willReturnSelf();
        $this->pageTitleMock->method('prepend')->willReturnSelf();

        $this->controller = new Edit(
            $this->contextMock,
            $this->resultPageFactoryMock,
            $this->offerFactoryMock,
            $this->dataPersistorMock
        );

        $reflection = new \ReflectionClass($this->controller);
        $resultRedirectFactoryProperty = $reflection->getProperty('resultRedirectFactory');
        $resultRedirectFactoryProperty->setAccessible(true);
        $resultRedirectFactoryProperty->setValue($this->controller, $this->resultRedirectFactoryMock);
    }

    /**
     * Test execute method for editing existing offer
     */
    public function testExecuteEditExistingOffer(): void
    {
        $offerId = 123;
        $offerLabel = 'Test Offer';

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('getData')->with('label')->willReturn($offerLabel);

        $this->resultPageMock->expects($this->exactly(3))
            ->method('addBreadcrumb')
            ->withConsecutive(
                ['Offer Manager', 'Offer Manager'],
                ['Offers', 'Offers'],
                ['Edit Offer', 'Edit Offer']
            )
            ->willReturnSelf();

        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Dnd_OfferManager::offers')
            ->willReturnSelf();

        $this->pageTitleMock->expects($this->exactly(2))
            ->method('prepend')
            ->withConsecutive(['Offers'], [$offerLabel])
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultPageMock, $result);
    }

    /**
     * Test execute method with offer that has empty label
     */
    public function testExecuteWithOfferEmptyLabel(): void
    {
        $offerId = 123;
        $offerLabel = '';

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('getData')->with('label')->willReturn($offerLabel);

        $this->pageTitleMock->expects($this->exactly(2))
            ->method('prepend')
            ->withConsecutive(['Offers'], [$offerLabel])
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultPageMock, $result);
    }

    /**
     * Test initPage method functionality (implicitly tested through execute)
     */
    public function testInitPageMethod(): void
    {
        $offerId = 123;

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('getData')->with('label')->willReturn('Test Offer');

        // Verify that initPage is called and sets up the page correctly
        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Dnd_OfferManager::offers')
            ->willReturnSelf();

        $this->resultPageMock->expects($this->exactly(3))
            ->method('addBreadcrumb')
            ->withConsecutive(
                ['Offer Manager', 'Offer Manager'],
                ['Offers', 'Offers'],
                ['Edit Offer', 'Edit Offer']
            )
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultPageMock, $result);
    }

    /**
     * Test execute method with non-existent offer ID
     */
    public function testExecuteWithNonExistentOfferId(): void
    {
        $offerId = 999;

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn(null); // Offer doesn't exist

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('This offer no longer exists.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }
}
