<?php
declare(strict_types=1);

namespace SaschaSchieferdecker\Instagram\Controller;

use Psr\Http\Message\ResponseInterface;
use SaschaSchieferdecker\Instagram\Domain\Repository\FeedRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ProfileController
 */
class ProfileController extends ActionController
{
    /**
     * @var string
     */
    private $imageFolder = 'typo3temp/assets/tx_instagram/';

    /**
     * ProfileController constructor.
     * @param FeedRepository $feedRepository
     */
    public function __construct(private readonly FeedRepository $feedRepository)
    {
    }

    public function showAction(): ResponseInterface
    {
        $feedPosts = $this->getFeedPosts();
        $this->view->assignMultiple([
            'feedposts' => $feedPosts,
        ]);
        return $this->htmlResponse();
    }

    public function jsonAction(): ResponseInterface
    {
        $feedPosts = $this->getFeedPosts();
        foreach ($feedPosts as &$item) {
            $this->updateMediaUrls($item);
        }
        $response = $this->jsonResponse(json_encode($feedPosts));
        throw new \TYPO3\CMS\Core\Http\PropagateResponseException($response, 1604505304);
    }

    private function getFeedPosts(): array
    {
        if (str_contains((string) $this->settings['usernames'], "\n")) {
            $usernames = explode("\n", (string) $this->settings['usernames']);
            return $this->feedRepository->findDataByMultipleUsernames($usernames);
        }
        return $this->feedRepository->findDataByUsername((string)$this->settings['usernames'], (int)$this->settings['limit']);
    }

    private function updateMediaUrls(array &$item): void
    {
        $absPath = GeneralUtility::getFileAbsFileName($this->imageFolder);
        $domainPrefix = rtrim((string) $this->settings['domainprefix'], '/');

        $imagePath = $absPath . $item['id'] . '.jpg';
        if (file_exists($imagePath)) {
            $item['displayUrl'] = $domainPrefix . '/' . $this->imageFolder . $item['id'] . '.jpg';
        }

        $videoPath = $absPath . $item['id'] . '.mp4';
        if (file_exists($videoPath)) {
            $item['videoUrl'] = $domainPrefix . '/' . $this->imageFolder . $item['id'] . '.mp4';
        }
    }
}
