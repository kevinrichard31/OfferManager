<?php
namespace Dnd\OfferManager\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Image extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName]) && !empty($item[$fieldName])) {
                    $imagePath = $item[$fieldName];
                    $imageUrl = $this->getImageUrl($imagePath);
                    
                    // Génération du HTML de l'image
                    $imageHtml = sprintf(
                        '<img src="%s" alt="%s" style="width:60px;height:60px;object-fit:cover;" />',
                        $imageUrl,
                        htmlspecialchars($item['label'] ?? 'Offer Image')
                    );
                    
                    // Remplacer le contenu de la colonne par le HTML de l'image
                    $item[$fieldName] = $imageHtml;
                } else {
                    // Si pas d'image, afficher un placeholder
                    $item[$fieldName] = '<span style="color:#ccc;">No image</span>';
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get image URL
     *
     * @param string $imagePath
     * @return string
     */
    protected function getImageUrl($imagePath)
    {
        // Construction simple de l'URL - à adapter selon votre configuration
        return '/media/' . ltrim($imagePath, '/');
    }
}
