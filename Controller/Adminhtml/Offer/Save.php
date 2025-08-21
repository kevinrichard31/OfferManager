<?php
namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * @var OfferFactory
     */
    protected $offerFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param OfferFactory $offerFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        OfferFactory $offerFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->offerFactory = $offerFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_create';

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('offer_id');
            $model = $this->offerFactory->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage('This offer no longer exists.');
                return $resultRedirect->setPath('*/*/');
            }

            if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                $data['image'] = 'catalog/category/offers/' . $data['image'][0]['name'];
            } elseif (isset($data['image'][0]['name']) && !isset($data['image'][0]['tmp_name'])) {
                $data['image'] = $data['image'][0]['name'];
            } else {
                $data['image'] = null;
            }

            // Handle category_ids - convert array to comma-separated string
            if (isset($data['category_ids']) && is_array($data['category_ids'])) {
                $data['category_ids'] = implode(',', $data['category_ids']);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage('You saved the offer.');
                $this->dataPersistor->clear('dnd_offer_manager_offer');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['offer_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, 'Something went wrong while saving the offer.');
            }

            $this->dataPersistor->set('dnd_offer_manager_offer', $data);
            return $resultRedirect->setPath('*/*/edit', ['offer_id' => $this->getRequest()->getParam('offer_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
