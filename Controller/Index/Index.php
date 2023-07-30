<?php declare(strict_types=1);

namespace Macademy\SecretUrl\Controller\Index;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileReader;
use Magento\Framework\Url\DecoderInterface;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly DecoderInterface $urlDecoder,
        private readonly EncryptorInterface $encryptor,
        private readonly Filesystem $filesystem,
        private readonly FileReader $fileReader,
        private readonly FileFactory $fileFactory,
    ) {}

    /**
     * @throws NotFoundException
     * @throws FileSystemException
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $f = $this->request->getParam('f');
        $decodedFilename = $this->urlDecoder->decode($f);
        $filename = $this->encryptor->decrypt($decodedFilename);
        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $fileAbsolutePath = $mediaPath . $filename;

        if (!$this->fileReader->isExists($fileAbsolutePath)) {
            throw new NotFoundException(__('File not found.'));
        }

        $fileContent = $this->fileReader->fileGetContents($fileAbsolutePath);

        return $this->fileFactory->create(
            $filename,
            $fileContent,
            DirectoryList::MEDIA,
        );
    }
}
