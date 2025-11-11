<?php
declare(strict_types=1);

namespace fucodo\Notification\Service;

use fucodo\Notification\Domain\Model\Notification;
use fucodo\Notification\Domain\Repository\NotificationRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context;

#[Flow\Scope('singleton')]
class NotificationService
{
    #[Flow\Inject]
    protected NotificationRepository $notificationRepository;

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
            $trimmedUser = trim($user);
            if ($trimmedUser === '') {
                continue;
            }

            $notification = new Notification();
            $notification->setUser($trimmedUser);
            $notification->setApp($app);
            $notification->setSubject($subject);
            $notification->setMessage($message);
            $notification->setObjectType($objectType);
            $notification->setObjectId($objectId ?? uniqid('cli_', true));
            $notification->setLink($link);
            $notification->setIcon($icon);
            $notification->setTimestamp($timestamp);

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
            // Signal emission happens inside the repository.
        }
    }
}
