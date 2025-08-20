<?php
namespace Dnd\OfferManager\Block\Adminhtml\Offer\Edit;

use Magento\Backend\Block\Template;
use Dnd\OfferManager\Model\OfferFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save extends Template implements ButtonProviderInterface  
{
    protected $offerFactory;
    protected $request;

    public function __construct(
        Template\Context $context,
        OfferFactory $offerFactory,
        RequestInterface $request,
        array $data = []
    ) {
        $this->offerFactory = $offerFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    public function saveOffer($data)
    {
        $id = isset($data['offer_id']) ? $data['offer_id'] : null;
        $model = $this->offerFactory->create();
        if ($id) {
            $model->load($id);
        }
        if (isset($data['category_ids']) && is_array($data['category_ids'])) {
            $data['category_ids'] = implode(',', $data['category_ids']);
        }
        $model->setData($data);
        $model->save();
        return $model->getId();
    }

    /**
     * Implémentation obligatoire de ButtonProviderInterface
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Offer'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
