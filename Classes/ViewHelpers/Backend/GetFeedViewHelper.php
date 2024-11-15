<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\ViewHelpers\Backend;

use SaschaSchieferdecker\Instagram\Domain\Repository\FeedRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetFeedViewHelper
 * @noinspection PhpUnused
 */
class GetFeedViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('flexForm', 'array', 'tt_content.pi_flexform as array', true);
    }

    /**
     * @return array
     */
    public function render(): array
    {
        $limit = (int)$this->arguments['flexForm']['settings']['limit'];
        $usernames = (string)$this->arguments['flexForm']['settings']['usernames'];
        /** @var FeedRepository $instagramRepository */
        $feedRepository = GeneralUtility::makeInstance(FeedRepository::class);
        if (str_contains($usernames, "\n"))
        {
            $usernames = explode("\n", $usernames);
            return $feedRepository->findDataByMultipleUsernames($usernames, $limit);
        }
        else
        {
            return $feedRepository->findDataByUsername($usernames, $limit);
        }
    }
}
