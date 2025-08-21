<?php

namespace Dnd\OfferManager\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Phrase;

class Create extends Action
{
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_create';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
    $resultPage = $this->resultPageFactory->create();
    $resultPage->getConfig()->getTitle()->prepend(new Phrase('Add New Offer'));

        return $resultPage;
    }
}
