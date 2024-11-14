<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\Command;

use SaschaSchieferdecker\Instagram\Domain\Repository\FeedRepository;
use SaschaSchieferdecker\Instagram\Domain\Service\PrepareFeed;
use SaschaSchieferdecker\Instagram\Domain\Service\NotificationMail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImportFeedCommand
 */
class ImportFeedCommand extends Command
{
    /**
     * @var PrepareFeed
     */
    protected $prepareFeed = null;

    /**
     * @var FeedRepository
     */
    protected $feedRepository = null;

    /**
     * @var NotificationMail
     */
    protected $notificationMail = null;

    /**
     * ImportFeedCommand constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->prepareFeed = GeneralUtility::makeInstance(PrepareFeed::class);
        $this->feedRepository = GeneralUtility::makeInstance(FeedRepository::class);
        $this->notificationMail = GeneralUtility::makeInstance(NotificationMail::class);
    }

    /**
     * @return void
     */
    public function configure()
    {
        $this->setDescription('Import instagram feed');
        $this->addArgument('posts-url', InputArgument::REQUIRED, 'API URL for posts');
        $this->addArgument(
            'notify',
            InputArgument::OPTIONAL,
            'Optional: Notify receivers on failures (commaseparated emails)',
            ''
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $feed = $this->prepareFeed->joinAndSort($input->getArgument('posts-url'));
            if (count($feed) > 0) {
                foreach ($feed as $username => $group) {
                    $feedUid = $this->feedRepository->insertFeed($username);
                    foreach ($group as $item) {
                        $postExists = $this->feedRepository->postExists($feedUid, $item['id']);
                        if ($postExists == false) {
                            $this->feedRepository->insertPost($feedUid, $item);
                        }
                        else {
                            $this->feedRepository->updatePost($feedUid, $item);
                        }
                    }
                }
            }
            /*$this->feedRepository->insert($feed);
            $output->writeln(
                count($feed['data']) . ' stories from ' . $input->getArgument('username') . ' stored into database'
            );*/
            return 0;
        } catch (\Exception $exception) {
            $output->writeln('Feed could not be fetched from Instagram');
            $output->writeln('Reason: ' . $exception->getMessage());
            if ($input->getArgument('notify') !== '') {
                $this->notificationMail->send($input->getArgument('notify'), $input->getArguments(), $exception);
            }
            return 1605297993;
        }
    }
}
