<?php

namespace App\Service;

use App\Controller\ImageCache\ImageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UrlImageCacheService
{
    protected $cacheDir;
    protected $container;
    private $imageManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cacheDir = $this->container->getParameter('kernel.project_dir').'/public/cache/images';
        $this->imageManager = new ImageManager($this->container);
    }

    public function getCachedImage(string $url): string
    {
        $cacheKey = md5($url);
        $cachePath = $this->cacheDir . '/' . $cacheKey . '.png';

        if ($this->isCacheExpired($cachePath)) {
            if (file_exists($cachePath)) {
                unlink($cachePath); // Delete the file
            }

            $this->cacheImage($url, $cachePath);
        }

        return $cachePath;
    }

    private function isCacheExpired(string $cachePath): bool
    {
        if (!file_exists($cachePath)) {
            return true;
        }

        $cacheLifetime = 7 * 24 * 60 * 60; // 1 week in seconds

        return (time() - filemtime($cachePath)) > $cacheLifetime;
    }

    private function cacheImage(string $url, string $cachePath): void
    {
        $image = $this->imageManager->make($url);
        $image->save($cachePath);
    }
}
