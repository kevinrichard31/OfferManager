<?php
namespace Dnd\OfferManager\Controller\Adminhtml\Offer\Image;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\Framework\UrlInterface;

class Upload implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Dnd_OfferManager::offers_actions';

    protected UploaderFactory $uploaderFactory;
    protected Filesystem $filesystem;
    protected ResultFactory $resultFactory;
    protected StoreManagerInterface $storeManager;
    protected SynchronizeFilesInterface $synchronizeFiles;

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ResultFactory $resultFactory,
        StoreManagerInterface $storeManager,
        SynchronizeFilesInterface $synchronizeFiles
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
        $this->synchronizeFiles = $synchronizeFiles;
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        try {
            if (isset($_FILES['image']['name'])) {
                $_FILES['image']['name'] = str_replace(' ', '_', $_FILES['image']['name']);
            }

            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory->getAbsolutePath('catalog/category/offers'));

            $relativePath = '/catalog/category/offers/' . ltrim($result['file'], '/');
            $this->synchronizeFiles->execute([$relativePath]);

            $baseMediaUrl = rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
            $result['url'] = $baseMediaUrl . $relativePath;
            $result['cookie'] = [
                'name' => session_name(),
                'value' => session_id(),
                'lifetime' => session_get_cookie_params()['lifetime'],
                'path' => session_get_cookie_params()['path'],
                'domain' => session_get_cookie_params()['domain'],
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
