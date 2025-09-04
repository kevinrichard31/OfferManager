<?php
namespace Dnd\OfferManager\Model\Offer;

use Dnd\OfferManager\Model\ResourceModel\Offer\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Url;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Dnd\OfferManager\Model\ResourceModel\Offer\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        StoreManagerInterface $storeManager = null,
        Filesystem $filesystem = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $offer = $this->collection->getFirstItem();
        /** @var \Dnd\OfferManager\Model\Offer $offer */
            $data = $offer->getData();
            if (!empty($data['image'])) {
                $baseMediaUrl = rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
                $imageUrl = $baseMediaUrl . '/' . ltrim($data['image'], '/');
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                
                $imageData = [
                    'name' => basename($data['image']),
                    'url' => $imageUrl,
                ];
                
                if ($mediaDirectory->isExist($data['image'])) {
                    $stat = $mediaDirectory->stat($data['image']);
                    $imageData['size'] = $stat['size'];
                    
                    // Ajouter le type MIME basé sur l'extension
                    $extension = pathinfo($data['image'], PATHINFO_EXTENSION);
                    $mimeTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp'
                    ];
                    if (isset($mimeTypes[strtolower($extension)])) {
                        $imageData['type'] = $mimeTypes[strtolower($extension)];
                    }
                }
                
                $imageData['file'] = $data['image'];
                
                $data['image'] = [$imageData];
            }
            
            $this->loadedData[$offer->getId()] = $data;

        $data = $this->dataPersistor->get('dnd_offer_manager_offer');

        return $this->loadedData;
    }
}
