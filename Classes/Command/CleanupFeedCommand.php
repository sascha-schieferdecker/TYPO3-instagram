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
class CleanupFeedCommand extends Command
{

    /**
     * @var FeedRepository
     */
    protected $feedRepository = null;

    /**
     * @var PrepareFeed
     */
    protected $prepareFeed = null;


    /**
     * ImportFeedCommand constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->feedRepository = GeneralUtility::makeInstance(FeedRepository::class);
        $this->prepareFeed = GeneralUtility::makeInstance(PrepareFeed::class);
    }

    /**
     * @return void
     */
    public function configure()
    {
        $this->setDescription('Cleanup instagram feed');
        $this->addArgument('keep', InputArgument::REQUIRED, 'Number of entries to keep for each feed');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $remainingposts = $this->feedRepository->cleanUpFeeds((int) $input->getArgument('keep'));
            // Cleanup unused images
            $this->prepareFeed->CleanUp($remainingposts);
            return 0;
        } catch (\Exception $exception) {
            $output->writeln('Error: ' . $exception->getMessage());
            return 1605297993;
        }
    }
}
