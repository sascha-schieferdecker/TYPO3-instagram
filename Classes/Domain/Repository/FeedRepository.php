<?php
declare(strict_types=1);

namespace SaschaSchieferdecker\Instagram\Domain\Repository;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Driver\Exception;
use SaschaSchieferdecker\Instagram\Utility\ArrayUtility;
use SaschaSchieferdecker\Instagram\Utility\DatabaseUtility;

/**
 * Class FeedRepository
 * to read and write feed values to and from storage
 */
class FeedRepository
{
    private const TABLE_FEEDS = 'tx_instagram_feed';
    private const TABLE_POSTS = 'tx_instagram_post';
    private const DEFAULT_LIMIT = 10;

    public function findDataByMultipleUsernames(array $usernames, int $limit = self::DEFAULT_LIMIT): array
    {
        $quotedUsernames = $this->quoteUsernames($usernames);

        $feedUids = $this->getFeedUidsByUsernames($quotedUsernames);
        if (empty($feedUids)) {
            return [];
        }

        return $this->getPostsByFeedUids($feedUids, $limit);
    }

    private function quoteUsernames(array $usernames): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        return array_map(static function ($username) use ($queryBuilder) {
            return $queryBuilder->getConnection()->quote($username);
        }, $usernames);
    }

    private function getFeedUidsByUsernames(array $quotedUsernames): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        return $queryBuilder
            ->select('uid')
            ->from(self::TABLE_FEEDS)
            ->where($queryBuilder->expr()->in('username', $quotedUsernames))
            ->orderBy('uid')
            ->executeQuery()
            ->fetchFirstColumn();
    }

    private function getPostsByFeedUids(array $feedUids, int $limit): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_POSTS);
        $data = $queryBuilder
            ->select('content')
            ->from(self::TABLE_POSTS)
            ->where($queryBuilder->expr()->in('feed_uid', $feedUids))
            ->orderBy('tstamp', 'desc')
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();

        return array_filter(array_map([$this, 'decodeJsonContent'], $data));
    }

    private function decodeJsonContent(array $item): ?array
    {
        if (ArrayUtility::isJsonArray($item['content'])) {
            return json_decode($item['content'], true);
        }

        return null;
    }

    public function findDataByUsername(string $username, int $limit = self::DEFAULT_LIMIT): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        $feedUid = $queryBuilder
            ->select('uid')
            ->from(self::TABLE_FEEDS)
            ->where($queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)))
            ->setMaxResults(1)
            ->orderBy('uid', 'desc')
            ->executeQuery()
            ->fetchOne();

        return $feedUid ? $this->findDataByFeedUid((int)$feedUid, $limit) : [];
    }

    public function findDataByFeedUid(int $feedUid, int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->getPostsByFeedUids([$feedUid], $limit);
    }

    public function cleanupFeeds(int $limit): array
    {
        $feeds = $this->getAllFeedsOrderedByImportDate();
        $uidsToKeep = $this->getUidsToKeepFromFeeds($feeds, $limit);

        $this->deletePostsNotInUids($uidsToKeep);
        return $uidsToKeep;
    }

    private function getAllFeedsOrderedByImportDate(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        return $queryBuilder
            ->select('uid')
            ->from(self::TABLE_FEEDS)
            ->orderBy('import_date', 'asc')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function getUidsToKeepFromFeeds(array $feeds, int $limit): array
    {
        $uidsToKeep = [];
        foreach ($feeds as $feed) {
            $feedUid = (int)$feed['uid'];
            $posts = $this->findDataByFeedUid($feedUid, $limit);
            foreach ($posts as $post) {
                $uidsToKeep[] = (int)$post['id'];
            }
        }
        return $uidsToKeep;
    }

    private function deletePostsNotInUids(array $uidsToKeep): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_POSTS);
        $queryBuilder
            ->delete(self::TABLE_POSTS)
            ->where($queryBuilder->expr()->notIn('uid', $uidsToKeep))
            ->executeStatement();
    }

    /**
     * @param string $username
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function insertFeed(string $username): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        $existingFeedUid = $queryBuilder
            ->select('uid')
            ->from(self::TABLE_FEEDS)
            ->where($queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)))
            ->setMaxResults(1)
            ->orderBy('uid', 'desc')
            ->executeQuery()
            ->fetchOne();

        if ($existingFeedUid === false) {
            $queryBuilder
                ->insert(self::TABLE_FEEDS)
                ->values(['username' => $username, 'import_date' => time()])
                ->executeStatement();
            return (int)$queryBuilder->getConnection()->lastInsertId();
        }

        return (int)$existingFeedUid;
    }

    public function insertPost(int $feedUid, array $post): void
    {
        if ($this->postExists($feedUid, (int) $post['id']) === false) {
            $this->performPostInsert($feedUid, $post);
        } else {
            $this->updatePost($feedUid, $post);
        }
    }

    private function performPostInsert(int $feedUid, array $post): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_POSTS);
        $queryBuilder
            ->insert(self::TABLE_POSTS)
            ->values([
                'feed_uid' => $feedUid,
                'uid' => $post['id'],
                'content' => json_encode($post),
                'tstamp' => strtotime($post['timestamp'])
            ])
            ->executeStatement();
    }

    public function updatePost(int $feedUid, array $post): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_POSTS);
        $queryBuilder
            ->update(self::TABLE_POSTS)
            ->set('content', json_encode($post))
            ->set('tstamp', strtotime($post['timestamp']))
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($post['id'])),
                $queryBuilder->expr()->eq('feed_uid', $queryBuilder->createNamedParameter($feedUid))
            )
            ->executeStatement();
    }

    public function postExists(int $feedUid, int $postUid)
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_POSTS);
        return $queryBuilder
            ->select('uid')
            ->from(self::TABLE_POSTS)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($postUid)),
                $queryBuilder->expr()->eq('feed_uid', $queryBuilder->createNamedParameter($feedUid))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }
}
