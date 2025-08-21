<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Test\Unit\Controller\Adminhtml\Offer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Dnd\OfferManager\Controller\Adminhtml\Offer\Save;
use Dnd\OfferManager\Model\OfferFactory;
use Dnd\OfferManager\Model\Offer;

/**
 * Test class for Save controller
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
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
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

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
        $this->dataPersistorMock = $this->createMock(DataPersistorInterface::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->offerModelMock = $this->createMock(Offer::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactoryMock);

        $this->resultFactoryMock->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->resultRedirectFactoryMock->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->controller = new Save(
            $this->contextMock,
            $this->offerFactoryMock,
            $this->dataPersistorMock
        );

        // Use reflection to set the protected resultRedirectFactory property
        $reflection = new \ReflectionClass($this->controller);
        $resultRedirectFactoryProperty = $reflection->getProperty('resultRedirectFactory');
        $resultRedirectFactoryProperty->setAccessible(true);
        $resultRedirectFactoryProperty->setValue($this->controller, $this->resultRedirectFactoryMock);
    }

    /**
     * Test execute method with no POST data
     */
    public function testExecuteWithNoPostData(): void
    {
        $this->requestMock->method('getPostValue')->willReturn(null);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with successful save of new offer
     */
    public function testExecuteSuccessfulSaveNewOffer(): void
    {
        $postData = [
            'label' => 'Test Offer',
            'description' => 'Test Description',
            'image' => [
                0 => [
                    'name' => 'test-image.jpg',
                    'tmp_name' => '/tmp/test'
                ]
            ],
            'category_ids' => [1, 2, 3],
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31'
        ];

        $expectedData = [
            'label' => 'Test Offer',
            'description' => 'Test Description',
            'image' => 'catalog/category/offers/test-image.jpg',
            'category_ids' => '1,2,3',
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31'
        ];

        $this->requestMock->method('getPostValue')->willReturn($postData);
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) {
                switch ($param) {
                    case 'offer_id':
                        return null;
                    case 'back':
                        return null;
                    default:
                        return null;
                }
            });

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with(null)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn(null);
        $this->offerModelMock->expects($this->once())->method('setData')->with($expectedData);
        $this->offerModelMock->expects($this->once())->method('save');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You saved the offer.');

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('dnd_offer_manager_offer');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with LocalizedException during save
     */
    public function testExecuteWithLocalizedException(): void
    {
        $postData = ['label' => 'Test Offer'];
        $exceptionMessage = 'Validation error occurred';
        $offerId = 123;

        $this->requestMock->method('getPostValue')->willReturn($postData);
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) use ($offerId) {
                switch ($param) {
                    case 'offer_id':
                        return $offerId;
                    default:
                        return null;
                }
            });

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('save')
            ->willThrowException(new LocalizedException(new Phrase($exceptionMessage)));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage);

        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('dnd_offer_manager_offer', [
                'label' => 'Test Offer',
                'image' => null
            ]);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['offer_id' => $offerId])
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test execute method with general Exception during save
     */
    public function testExecuteWithGeneralException(): void
    {
        $postData = ['label' => 'Test Offer'];
        $exception = new \Exception('Database error');
        $offerId = 123;

        $this->requestMock->method('getPostValue')->willReturn($postData);
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) use ($offerId) {
                switch ($param) {
                    case 'offer_id':
                        return $offerId;
                    default:
                        return null;
                }
            });

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with($offerId)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn($offerId);
        $this->offerModelMock->method('save')->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Something went wrong while saving the offer.');

        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('dnd_offer_manager_offer', [
                'label' => 'Test Offer',
                'image' => null
            ]);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['offer_id' => $offerId])
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }

    /**
     * Test image handling when no image is provided
     */
    public function testExecuteWithNoImage(): void
    {
        $postData = [
            'label' => 'Test Offer',
            'category_ids' => [1, 2, 3]
        ];

        $expectedData = [
            'label' => 'Test Offer',
            'image' => null,
            'category_ids' => '1,2,3'
        ];

        $this->requestMock->method('getPostValue')->willReturn($postData);
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) {
                switch ($param) {
                    case 'offer_id':
                        return null;
                    case 'back':
                        return null;
                    default:
                        return null;
                }
            });

        $this->offerFactoryMock->method('create')->willReturn($this->offerModelMock);
        $this->offerModelMock->method('load')->with(null)->willReturnSelf();
        $this->offerModelMock->method('getId')->willReturn(null);
        $this->offerModelMock->expects($this->once())->method('setData')->with($expectedData);
        $this->offerModelMock->expects($this->once())->method('save');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You saved the offer.');

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('dnd_offer_manager_offer');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->resultRedirectMock, $result);
    }
}