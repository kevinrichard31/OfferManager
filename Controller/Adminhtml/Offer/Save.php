<?php

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Dnd\OfferManager\Model\OfferFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers';

    /** @var OfferFactory */
    private $offerFactory;

    public function __construct(
        Context $context, 
        OfferFactory $offerFactory)
    {
        $this->offerFactory = $offerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest();
        var_dump($data);
        die();

        if (!$data) {
            $this->_redirect('*/*/');
            return;
        }

    $id = isset($data['offer_id']) ? $data['offer_id'] : null;

        /** @var \Dnd\OfferManager\Model\Offer $model */
        $model = $this->offerFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This offer no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // Map posted fields to model
        try {
            // Remove form key if present
            if (isset($data['form_key'])) {
                unset($data['form_key']);
            }

            // If category_ids is array, convert to comma string
            if (isset($data['category_ids']) && is_array($data['category_ids'])) {
                $data['category_ids'] = implode(',', $data['category_ids']);
            }

            $model->setData($data);
            $model->save();

            $this->messageManager->addSuccessMessage(__('The offer has been saved aaa.'));

            // Redirect logic: if 'back' param present, return to edit page
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the offer.'));
        }

        // Persist data in session and redirect back to edit
    $this->_getSession()->setFormData($data);
    $this->_redirect('*/*/edit', ['id' => $id]);
    }
}
