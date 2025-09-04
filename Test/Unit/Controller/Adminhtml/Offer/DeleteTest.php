<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Test\Unit\Controller\Adminhtml\Offer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Dnd\OfferManager\Controller\Adminhtml\Offer\Delete;
use Dnd\OfferManager\Model\OfferFactory;
use Dnd\OfferManager\Model\Offer;

/**
 * Test class for Delete controller
 */
class DeleteTest extends TestCase
{
    /**
     * @var Delete
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var OfferFactory|MockObject
     */
    private $offerFactoryMock;

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
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

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
        $this->offerFactoryMock = $this->createMock(OfferFactory::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->offerModelMock = $this->createMock(Offer::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->resultRedirectFactoryMock->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->controller = new Delete(
            $this->contextMock,
            $this->offerFactoryMock
        );

        // Use reflection to set the protected resultRedirectFactory property
        $reflection = new \ReflectionClass($this->controller);
        $resultRedirectFactoryProperty = $reflection->getProperty('resultRedirectFactory');
        $resultRedirectFactoryProperty->setAccessible(true);
        $resultRedirectFactoryProperty->setValue($this->controller, $this->resultRedirectFactoryMock);
    }

    /**
     * Test execute method with no offer ID
     */
    public function testExecuteWithNoOfferId(): void
    {
        $this->requestMock->method('getParam')->with('offer_id')->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('We can\'t find an offer to delete.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with successful deletion
     */
    public function testExecuteSuccessfulDeletion(): void
    {
        $offerId = 123;

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->expects($this->once())->method('delete');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The offer has been deleted.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
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

    /**
     * Test execute method with LocalizedException during deletion
     */
    public function testExecuteWithLocalizedException(): void
    {
        $offerId = 123;
        $exceptionMessage = 'Cannot delete offer due to constraints';

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('delete')
            ->willThrowException(new LocalizedException(new Phrase($exceptionMessage)));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with general Exception during deletion
     */
    public function testExecuteWithGeneralException(): void
    {
        $offerId = 123;
        $exception = new \Exception('Database error');

        $this->requestMock->method('getParam')->with('offer_id')->willReturn($offerId);

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('delete')->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('An error occurred while deleting the offer.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with empty offer ID (string)
     */
    public function testExecuteWithEmptyOfferIdString(): void
    {
        $this->requestMock->method('getParam')->with('offer_id')->willReturn('');

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('We can\'t find an offer to delete.');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }
}
