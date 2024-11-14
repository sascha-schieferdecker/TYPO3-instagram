<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\Domain\Repository;

use SaschaSchieferdecker\Instagram\Utility\ArrayUtility;
use SaschaSchieferdecker\Instagram\Utility\DatabaseUtility;

/**
 * Class FeedRepository
 * to read and write feed values to and from storage
 */
class FeedRepository
{
    const TABLE_FEEDS = 'tx_instagram_feed';
    const TABLE_POSTS = 'tx_instagram_post';

    /**
     * @param string $username
     * @return array
     */
    public function findDataByUsername(string $username): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        $data = (string)$queryBuilder
            ->select('uid')
            ->from(self::TABLE_FEEDS)
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
            )
            ->setMaxResults(1)
            ->orderBy('uid', 'desc')
            ->executeQuery()
            ->fetchOne();
        dump($data);
        if (ArrayUtility::isJsonArray($data)) {
            return json_decode($data, true);
        }
        return [];
    }

    /**
     * @param string $username
     * @param array $feed
     * @return void
     */
    public function insertFeed(string $username): int
    {
        $feedUid = 0;
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_FEEDS);
        $data = $queryBuilder
            ->select('uid')
            ->from(self::TABLE_FEEDS)
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
            )
            ->setMaxResults(1)
            ->orderBy('uid', 'desc')
            ->executeQuery()
            ->fetchOne();
        if ($data === false) {
            $queryBuilder
                ->insert(self::TABLE_FEEDS)
                ->values([
                    'username' => $username,
                    'import_date' => time()
                ])
                ->executeStatement();
            $feedUid = (int) $queryBuilder->getConnection()->lastInsertId();
        }
        else {
            $feedUid = (int) $data;
        }
        return $feedUid;
    }

    public function insertPost(int $feedUid, array $post): void
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
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($post['id'])),
                $queryBuilder->expr()->eq('feed_uid', $queryBuilder->createNamedParameter($feedUid))
            )
            ->set('content', json_encode($post))
            ->set('tstamp', strtotime($post['timestamp']))
            ->executeStatement();
    }

    public function postExists($feedUid, $postUid)
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_POSTS);
        $data = $queryBuilder
            ->select('uid')
            ->from(self::TABLE_POSTS)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($postUid)),
                $queryBuilder->expr()->eq('feed_uid', $queryBuilder->createNamedParameter($feedUid))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
        return $data;
    }
}
