<?php

declare(strict_types=1);

namespace Macademy\SecretUrl\ViewModel;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Url implements ArgumentInterface
{
    public function __construct(
        private readonly EncryptorInterface $encryptor,
        private readonly EncoderInterface $urlEncoder,
        private readonly UrlInterface $url,
    ) {}

    public function getSecretPath($filename): string
    {
        $encryptedFilename = $this->encryptor->encrypt($filename);

        return $this->urlEncoder->encode($encryptedFilename);
    }

    public function getSecretUrl($filename): string
    {
        $secretPath = $this->getSecretPath($filename);

        return $this->url->setQueryParam('f', $secretPath)->getUrl('secret');
    }
}
