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
        $feedposts = $this->feedRepository->findDataByApiUrl((string)$this->settings['apiposts']);
        $feedreels = $this->feedRepository->findDataByApiUrl((string)$this->settings['apireels']);
        $this->view->assignMultiple([
            'data' => $this->request->getAttribute('currentContentObject')->data,
            'feedposts' => $feedposts,
            'feedreels' => $feedreels
        ]);
        return $this->htmlResponse();
    }
}
