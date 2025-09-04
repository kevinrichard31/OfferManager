<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDelete
 */
class MassDelete extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_delete';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OfferFactory
     */
    private $offerFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OfferFactory $offerFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OfferFactory $offerFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->offerFactory = $offerFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            
            foreach ($collection as $offer) {
                $offerModel = $this->offerFactory->create();
                $offerModel->load($offer->getId());
                $offerModel->delete();
            }
            
        $this->messageManager->addSuccessMessage(
            __('A total of %1 offer(s) have been deleted.', $collectionSize)
        );


        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, 'An error occurred while deleting offers.');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}