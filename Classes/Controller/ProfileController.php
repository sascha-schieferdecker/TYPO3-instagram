<?php
declare(strict_types=1);

namespace SaschaSchieferdecker\Instagram\Controller;

use Psr\Http\Message\ResponseInterface;
use SaschaSchieferdecker\Instagram\Domain\Repository\FeedRepository;
use SaschaSchieferdecker\Instagram\Domain\Repository\TokenRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ProfileController
 */
class ProfileController extends ActionController
{
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
        $feedposts = $this->feedRepository->findDataByUsername((string)$this->settings['username'], (int) $this->settings['limit']);
        $this->view->assignMultiple([
            'feedposts' => $feedposts,
        ]);
        return $this->htmlResponse();
    }

    public function jsonAction()
    {
        $feedposts = $this->feedRepository->findDataByUsername((string)$this->settings['username'], (int) $this->settings['limit']);
        header('Content-Type: application/json');
        echo json_encode($feedposts);
        die();
    }
}
