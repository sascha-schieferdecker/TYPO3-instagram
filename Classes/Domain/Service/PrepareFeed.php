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
    /**
     * @var string
     */
    protected $imageFolder = 'typo3temp/assets/tx_instagram/';

    /**
     * @var bool
     */
    protected $storeImages = true;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * GetFeed constructor.
     */
    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
    }

    /**
     * @param string $username
     * @return array
     * @throws ApiConnectionException
     */
    /*public function getByUsername(string $username): array
    {
        $feed = $this->instagramRepository->getFeed($username);
        $this->persistImages($feed);
        return $feed;
    }*/

    public function joinAndSort(string $posts_url)
    {
        $result = [];
        $request = $this->requestFactory->request($posts_url);
        if ($request->getStatusCode() !== 200) {
            throw new ApiConnectionException('Could not refresh token', 1615754880);
        }
        $posts = json_decode($request->getBody()->getContents(), true);
        foreach ($posts as $post) {
            $post['dataType'] = 'post';
            $result[$post['ownerUsername']][] = $post;
        }
        foreach ($result as &$group) {
            usort($group, function($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });
        }
        if (count($result) > 0) {
            $this->persistImages($result);
        }
        return $result;
    }

    /**
     * @param array $feed
     * @return array
     * @throws ApiConnectionException
     */
    protected function persistImages(array $feed): void
    {
        if ($this->storeImages) {
            $path = GeneralUtility::getFileAbsFileName($this->imageFolder);
            FileUtility::createFolderIfNotExists($path);

            foreach ($feed as $group) {
                foreach ($group as $item) {
                    $pathAndName = GeneralUtility::getFileAbsFileName($this->imageFolder) . $item['id'] . '.jpg';
                    if (!file_exists($pathAndName)) {
                        $imageContent = $this->getImageContent($item['displayUrl']);
                        if ($imageContent !== '') {
                            GeneralUtility::writeFile($pathAndName, $imageContent, true);
                        }
                        if ($item['type'] === 'Video') {
                            $imageContent = $this->getImageContent($item['videoUrl']);
                            $pathAndName = GeneralUtility::getFileAbsFileName($this->imageFolder) . $item['id'] . '.mp4';
                            if ($imageContent != '') {
                                GeneralUtility::writeFile($pathAndName, $imageContent, true);;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $url
     * @return string
     * @throws ApiConnectionException
     */
    protected function getImageContent(string $url): string
    {
        try {
            $response = $this->requestFactory->request($url);
            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
            } else {
                throw new ApiConnectionException('Image could not be fetched from ' . $url, 1615759345);
            }
        } catch (\Exception $exception) {
            return  '';
            // Do not throw exception here
            //throw new ApiConnectionException($exception->getMessage(), 1615759354);
        }
        return $content;
    }
}
