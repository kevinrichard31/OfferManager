<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_delete';

    /**
     * @var OfferFactory
     */
    private $offerFactory;

    /**
     * @param Context $context
     * @param OfferFactory $offerFactory
     */
    public function __construct(
        Context $context,
        OfferFactory $offerFactory
    ) {
        parent::__construct($context);
        $this->offerFactory = $offerFactory;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $id = $this->getRequest()->getParam('offer_id');
        if ($id) {
            try {
                $model = $this->offerFactory->create();
                $model->load($id);
                
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage('This offer no longer exists.');
                    return $resultRedirect->setPath('*/*/');
                }
                
                $model->delete();
                $this->messageManager->addSuccessMessage('The offer has been deleted.');
                
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('An error occurred while deleting the offer.');
            }
            
            return $resultRedirect->setPath('*/*/edit', ['offer_id' => $id]);
        }
        
        $this->messageManager->addErrorMessage('We can\'t find an offer to delete.');
        return $resultRedirect->setPath('*/*/');
    }
}
