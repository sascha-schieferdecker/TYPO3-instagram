<?php
declare(strict_types=1);

namespace SaschaSchieferdecker\Instagram\Controller;

use Psr\Http\Message\ResponseInterface;
use SaschaSchieferdecker\Instagram\Domain\Repository\FeedRepository;
use SaschaSchieferdecker\Instagram\Domain\Repository\TokenRepository;
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
    protected $imageFolder = 'typo3temp/assets/tx_instagram/';
    /**
     * @var FeedRepository
     */
    protected $feedRepository = null;
    /**
     * ProfileController constructor.
     * @param FeedRepository $feedRepository
     */
    public function __construct(FeedRepository $feedRepository)
    {
        $this->feedRepository = $feedRepository;
    }

    public function showAction(): ResponseInterface
    {
        if (strpos($this->settings['usernames'], "\n") !== false)
        {
            $usernames = explode("\n", $this->settings['usernames']);
            $feedposts = $this->feedRepository->findDataByMultipleUsernames($usernames);
        }
        else
        {
            $feedposts = $this->feedRepository->findDataByUsername((string)$this->settings['usernames'], (int) $this->settings['limit']);
        }
        $this->view->assignMultiple([
            'feedposts' => $feedposts,
        ]);
        return $this->htmlResponse();
    }

    public function jsonAction()
    {
        if (strpos($this->settings['usernames'], "\n") !== false)
        {
            $usernames = explode("\n", $this->settings['usernames']);
            $feedposts = $this->feedRepository->findDataByMultipleUsernames($usernames);
        }
        else
        {
            $feedposts = $this->feedRepository->findDataByUsername((string)$this->settings['usernames'], (int) $this->settings['limit']);
        }
        foreach ($feedposts as &$item) {
            $pathAndName = GeneralUtility::getFileAbsFileName($this->imageFolder) . $item['id'] . '.jpg';
            if (file_exists($pathAndName)) {
                $item['displayUrl'] = rtrim($this->settings['domainprefix'], '/') . '/' . $this->imageFolder . $item['id'] . '.jpg';
            }
            $pathAndName = GeneralUtility::getFileAbsFileName($this->imageFolder) . $item['id'] . '.mp4';
            if (file_exists($pathAndName)) {
                $item['videoUrl'] = rtrim($this->settings['domainprefix'], '/') . '/' . $this->imageFolder . $item['id'] . '.mp4';
            }
        }
        header('Content-Type: application/json');
        echo json_encode($feedposts);
        die();
    }
}
