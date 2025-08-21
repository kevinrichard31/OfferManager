<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\Registry;

const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_edit';

/**
 * Class Edit
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_edit';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var OfferFactory
     */
    private $offerFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OfferFactory $offerFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OfferFactory $offerFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->offerFactory = $offerFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('offer_id');
        $model = $this->offerFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage('This offer no longer exists.');
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('dnd_offer_manager_offer', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? 'Edit Offer' : 'New Offer',
            $id ? 'Edit Offer' : 'New Offer'
        );
        $resultPage->getConfig()->getTitle()->prepend('Offers');
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getLabel() : 'New Offer'
        );

        return $resultPage;
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    private function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Dnd_OfferManager::offers')
            ->addBreadcrumb('Offer Manager', 'Offer Manager')
            ->addBreadcrumb('Offers', 'Offers');

        return $resultPage;
    }
}
