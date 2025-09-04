<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_edit';

    private PageFactory $resultPageFactory;
    private OfferFactory $offerFactory;
    private DataPersistorInterface $dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OfferFactory $offerFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->offerFactory = $offerFactory;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('offer_id');
        $model = $this->offerFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage('This offer no longer exists.');
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? 'Edit Offer' : 'New Offer',
            $id ? 'Edit Offer' : 'New Offer'
        );
        $resultPage->getConfig()->getTitle()->prepend('Offers');
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getData('label') : 'New Offer'
        );

        return $resultPage;
    }

    private function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Dnd_OfferManager::offers')
            ->addBreadcrumb('Offer Manager', 'Offer Manager')
            ->addBreadcrumb('Offers', 'Offers');

        return $resultPage;
    }
}
