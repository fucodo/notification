<?php
declare(strict_types=1);

namespace fucodo\Notification\Command;

use fucodo\Notification\Service\NotificationService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

#[Flow\Scope('singleton')]
class NotificationCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var NotificationService
     */
    protected NotificationService $notificationService;

    /**
     * Send a notification to a comma-separated list of users.
     *
     * Example:
     *  ./flow notification:sendmessage \
     *      --app "fucodo.notification" \
     *      --subject "Info" \
     *      --message "Hello from CLI" \
     *      --users "user1,user2"
     */
    public function sendMessageCommand(
        string $app,
        string $subject,
        string $message,
        string $users,
        string $objectType = 'cli',
        string $objectId = '',
        string $link = '',
        string $icon = ''
    ): void {
        $userList = array_values(
            array_filter(
                array_map('trim', explode(',', $users)),
                static fn(string $u): bool => $u !== ''
            )
        );

        if ($userList === []) {
            $this->outputLine('No valid users given.');
            $this->quit(1);
        }

        $this->notificationService->sendToUsers(
            $userList,
            $app,
            $subject,
            $message,
            $objectType,
            $objectId !== '' ? $objectId : null,
            $link !== '' ? $link : null,
            $icon !== '' ? $icon : null
        );

        $this->outputLine('Notification sent to %d user(s).', [\count($userList)]);
    }

    /**
     * Send a notification to a comma-separated list of users.
     *
     * Example:
     *  ./flow notification:sendmessage \
     *      --app "fucodo.notification" \
     *      --subject "Info" \
     *      --message "Hello from CLI" \
     *      --users "user1,user2"
     */
    public function sendMessageToRoleCommand(
        string $app,
        string $subject,
        string $message,
        string $role,
        string $objectType = 'cli',
        string $objectId = '',
        string $link = '',
        string $icon = ''
    ): void {
        $this->notificationService->sendToUsersWithRole(
            $role,
            $app,
            $subject,
            $message,
            $objectType,
            $objectId !== '' ? $objectId : null,
            $link !== '' ? $link : null,
            $icon !== '' ? $icon : null
        );
    }
}
