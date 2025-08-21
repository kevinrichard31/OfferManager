<?php
declare(strict_types=1);

namespace Dnd\OfferManager\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;



/**
 * Class OfferActions
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();

        if (!$this->authorization->isAllowed('Dnd_OfferManager::offers_delete')
            && !$this->authorization->isAllowed('Dnd_OfferManager::offers_edit')) {
            $this->setData('config', ['componentDisabled' => true]);
        }
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
        foreach ($dataSource['data']['items'] as &$item) {
            $name = $this->getData('name');

            if (isset($item['offer_id'])) {

                if ($this->authorization->isAllowed('Dnd_OfferManager::offers_edit')) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'offermanager/offer/edit',
                            ['offer_id' => $item['offer_id']]
                        ),
                        'label' => __('Edit')
                    ];
                }

                if ($this->authorization->isAllowed('Dnd_OfferManager::offers_delete')) {
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'offermanager/offer/delete',
                            ['offer_id' => $item['offer_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Offer'),
                            'message' => __('Are you sure you want to delete this offer?')
                        ],
                        'post' => true
                    ];
                }

            }
        }
    }

        return $dataSource;
    }
}
