<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Redirect;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_delete';

    private OfferFactory $offerFactory;

    public function __construct(
        Context $context,
        OfferFactory $offerFactory
    ) {
        parent::__construct($context);
        $this->offerFactory = $offerFactory;
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('offer_id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find an offer to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            if ($this->deleteOfferById($id)) {
                $this->messageManager->addSuccessMessage(__('The offer has been deleted.'));
            } else {
                $this->messageManager->addErrorMessage(__('This offer no longer exists.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while deleting the offer.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function deleteOfferById(int $id): bool
    {
        $model = $this->offerFactory->create();
        $model->load($id);

        if (!$model->getId()) {
            return false;
        }

        $model->delete();
        return true;
    }
}
