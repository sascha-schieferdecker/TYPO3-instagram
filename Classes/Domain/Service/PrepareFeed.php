<?php
declare(strict_types=1);

namespace SaschaSchieferdecker\Instagram\Domain\Service;

use SaschaSchieferdecker\Instagram\Exception\ApiConnectionException;
use SaschaSchieferdecker\Instagram\Utility\FileUtility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PrepareFeed
 */
class PrepareFeed
{
    protected const IMAGE_FOLDER = 'typo3temp/assets/tx_instagram/';
    protected const STORE_IMAGES = true;

    protected RequestFactory $requestFactory;

    /**
     * Asset download failures collected during the last joinAndSort() call.
     * A single unreachable asset must not discard an otherwise successful import.
     *
     * @var string[]
     */
    protected array $assetErrors = [];

    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
    }

    public function joinAndSort(string $postsUrl): array
    {
        $this->assetErrors = [];
        $posts = $this->fetchPosts($postsUrl);
        $sortedPosts = $this->sortPostsByOwnerAndTimestamp($posts);

        if (!empty($sortedPosts)) {
            $this->persistImages($sortedPosts);
        }

        return $sortedPosts;
    }

    /**
     * @return string[]
     */
    public function getAssetErrors(): array
    {
        return $this->assetErrors;
    }

    protected function fetchPosts(string $url): array
    {
        $response = $this->requestFactory->request($url);
        if ($response->getStatusCode() !== 200) {
            throw new ApiConnectionException('Could not refresh token', 1615754880);
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    protected function sortPostsByOwnerAndTimestamp(array $posts): array
    {
        $result = [];
        foreach ($posts as $post) {
            $post['dataType'] = 'post';
            $result[$post['ownerUsername']][] = $post;
        }
        foreach ($result as &$group) {
            usort($group, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        }
        return $result;
    }

    public function cleanUp(array $postIds): void
    {
        $path = GeneralUtility::getFileAbsFileName(self::IMAGE_FOLDER);
        $this->removeObsoleteFiles($path, $postIds);
    }

    protected function removeObsoleteFiles(string $path, array $postIds): void
    {
        try {
            $files = array_diff(scandir($path), ['..', '.']);
            foreach ($files as $file) {
                if (!in_array((int)str_replace(['.jpg', '.mp4'], ['', ''], $file), $postIds)) {
                    unlink($path . $file);
                }
            }
        } catch (\Exception $e) {
            throw new ApiConnectionException($e->getMessage(), 1615754880);
        }
    }

    protected function persistImages(array $feed): void
    {
        if (self::STORE_IMAGES) {
            $path = GeneralUtility::getFileAbsFileName(self::IMAGE_FOLDER);
            FileUtility::createFolderIfNotExists($path);

            foreach ($feed as $group) {
                foreach ($group as $item) {
                    try {
                        $this->storeImage($path, $item);
                        if ($item['type'] === 'Video') {
                            $this->storeVideo($path, $item);
                        }
                    } catch (\Exception $exception) {
                        $this->assetErrors[] = sprintf(
                            'Post %s (%s): %s',
                            (string)($item['id'] ?? 'unknown'),
                            (string)($item['type'] ?? 'unknown'),
                            $exception->getMessage()
                        );
                    }
                }
            }
        }
    }

    protected function storeImage(string $path, array $item): void
    {
        $pathAndName = $path . (int)$item['id'] . '.jpg';
        if (!file_exists($pathAndName)) {
            $imageContent = $this->fetchContent($item['displayUrl']);
            if ($imageContent !== '') {
                GeneralUtility::writeFile($pathAndName, $imageContent, true);
            }
        }
    }

    protected function storeVideo(string $path, array $item): void
    {
        $pathAndName = $path . (int)$item['id'] . '.mp4';
        if (!file_exists($pathAndName)) {
            $videoContent = $this->fetchContent($item['videoUrl']);
            if ($videoContent !== '') {
                GeneralUtility::writeFile($pathAndName, $videoContent, true);
            }
        }
    }

    protected function fetchContent(string $url): string
    {
        try {
            $response = $this->requestFactory->request($url);
            if ($response->getStatusCode() === 200) {
                return $response->getBody()->getContents();
            } else {
                throw new ApiConnectionException('Image could not be fetched from ' . $url, 1615759345);
            }
        } catch (\Exception $exception) {
            throw new ApiConnectionException($exception->getMessage(), 1615759354);
        }
    }
}
