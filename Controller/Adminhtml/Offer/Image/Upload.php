<?php
namespace Dnd\OfferManager\Controller\Adminhtml\Offer\Image;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload implements HttpPostActionInterface
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ResultFactory $resultFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory->getAbsolutePath('catalog/category/offers'));
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
