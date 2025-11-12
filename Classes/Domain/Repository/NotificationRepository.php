<?php

declare(strict_types=1);

namespace fucodo\Notification\Domain\Repository;

use Doctrine\DBAL\Connection;
use fucodo\Notification\Domain\Model\Notification;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Security\Account;

#[Flow\Scope('singleton')]
class NotificationRepository extends Repository
{
    /**
     * The database table backing this repository.
     * @internal If you change this, update the ORM mapping on Notification as well.
     */
    public const TABLE_NAME = 'fucodo_notification_domain_model_notification';

    #[Flow\Inject]
    protected Connection $connection;

    protected $defaultOrderings = [
        'createdAt' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * Returns the DB table name used by this repository.
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function findByUserIdentifier(Account $account, int $limit, int $offset): QueryResultInterface
    {
        $q = $this->createQuery();
        $q->matching($q->logicalAnd(
            [
                $q->equals('account', $account)
            ]
        ));
        $q->setLimit($limit);
        $q->setOffset($offset);
        $q->setOrderings($this->defaultOrderings);
        return $q->execute();
    }

    /**
     * Adds a notification using a raw DB insert (no ORM persist/flush).
     */
    public function add($object): void
    {
        if (!$object instanceof Notification) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s, got %s',
                Notification::class,
                is_object($object) ? $object::class : gettype($object)
            ));
        }

        $this->connection->insert($this->getTableName(), $object->jsonSerialize());
        $id = (int)$this->connection->lastInsertId();

        if ($id > 0) {
            $reflection = new \ReflectionObject($object);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($object, $id);
        }

        // Emit a signal after successful insert
        $this->emitNotificationAdded($object);
    }

    /**
     * Delete a single notification for the given user (used as "mark as read").
     */
    public function deleteByIdForUser(int $id, Account $account): int
    {
        return (int)$this->connection->createQueryBuilder()
            ->delete($this->getTableName())
            ->where('id = :id')
            ->andWhere('user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $this->persistenceManager->getIdentifierByObject($account))
            ->execute();
    }

    /**
     * Delete multiple notifications for the given user (used as "mark as read" for many).
     *
     * @param int[] $ids
     */
    public function deleteByIdsForUser(array $ids, Account $account): int
    {
        if ($ids === []) {
            return 0;
        }

        $qb = $this->connection->createQueryBuilder();
        $qb->delete($this->getTableName())
            ->where('user = :user')
            ->andWhere($qb->expr()->in('id', ':ids'))
            ->setParameter('user', $this->persistenceManager->getIdentifierByObject($account))
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return (int)$qb->execute();
    }

    public function deleteOlderThan(int $timestamp): int
    {
        return (int)$this->connection->createQueryBuilder()
            ->delete($this->getTableName())
            ->where('timestamp < :ts')
            ->setParameter('ts', $timestamp)
            ->execute();
    }

    /**
     * Signal emitted after a notification has been added.
     *
     * @param Notification $notification
     */
    #[Flow\Signal]
    protected function emitNotificationAdded(Notification $notification): void
    {
        // Handled via AOP; intentionally empty.
    }
}
