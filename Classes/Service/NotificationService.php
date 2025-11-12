<?php
declare(strict_types=1);

namespace fucodo\Notification\Service;

use Doctrine\DBAL\Connection;
use fucodo\Notification\Domain\Model\Notification;
use fucodo\Notification\Domain\Repository\NotificationRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Context;

#[Flow\Scope('singleton')]
class NotificationService
{
    #[Flow\Inject]
    protected NotificationRepository $notificationRepository;

    #[Flow\Inject]
    protected AccountRepository $accountRepository;

    #[Flow\Inject]
    protected Connection $connection;

    /**
     * Send notifications to multiple users.
     *
     * @param string[] $users
     */
    public function sendToUsers(
        array $users,
        string $app,
        string $subject,
        ?string $message = null,
        string $objectType = 'cli',
        ?string $objectId = null,
        ?string $link = null,
        ?string $icon = null,
        array $subjectParameters = [],
        array $messageParameters = [],
        array $actions = []
    ): void {
        $timestamp = time();

        foreach ($users as $user) {
            if ($user instanceof Account) {
                $this->sendToUsers($user, $app, $subject, $message, $objectType, $objectId, $link, $icon, $subjectParameters, $messageParameters, $actions);
            }
            // Signal emission happens inside the repository.
        }
    }

    public function sendToUser(
        Account $account,
        string $app,
        string $subject,
        ?string $message = null,
        string $objectType = 'cli',
        ?string $objectId = null,
        ?string $link = null,
        ?string $icon = null,
        array $subjectParameters = [],
        array $messageParameters = [],
        array $actions = []
    ): void {
        $notification = new Notification();
        $notification->setAccount($account);
        $notification->setApp($app);
        $notification->setSubject($subject);
        $notification->setMessage($message);
        $notification->setObjectType($objectType);
        $notification->setObjectId($objectId ?? '');
        $notification->setLink($link);
        $notification->setIcon($icon);

        $notification->setSubjectParameters(
            $subjectParameters !== []
                ? json_encode($subjectParameters, JSON_THROW_ON_ERROR)
                : null
        );
        $notification->setMessageParameters(
            $messageParameters !== []
                ? json_encode($messageParameters, JSON_THROW_ON_ERROR)
                : null
        );
        $notification->setActions(
            $actions !== []
                ? json_encode($actions, JSON_THROW_ON_ERROR)
                : null
        );

        $this->notificationRepository->add($notification);
    }

    public function sendToUsersWithRole(
        string $role,
        string $app,
        string $subject,
        ?string $message = null,
        string $objectType = 'cli',
        ?string $objectId = null,
        ?string $link = null,
        ?string $icon = null,
        array $subjectParameters = [],
        array $messageParameters = [],
        array $actions = []
    ) {
        $users = $this->connection->executeQuery(
            'SELECT * FROM neos_flow_security_account WHERE roleidentifiers LIKE :role',
            [
                'role' => '%' . $role . '%'
            ]
        )->fetchAllAssociative();
        foreach ($users as $user) {
            $this->sendToUsers(
                [$user['accountidentifier']],
                $app,
                $subject,
                $message,
                $objectType,
                $objectId,
                $link,
                $icon,
                $subjectParameters,
                $messageParameters,
                $actions
            );
        }
    }
}
