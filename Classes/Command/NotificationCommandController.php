<?php
declare(strict_types=1);

namespace fucodo\Notification\Command;

use fucodo\Notification\Service\NotificationService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Security\AccountRepository;

#[Flow\Scope('singleton')]
class NotificationCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var NotificationService
     */
    protected NotificationService $notificationService;

    /**
     * @Flow\Inject
     * @var AccountRepository
     */
    protected AccountRepository $accountRepository;

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

        $send = 0;
        foreach ($userList as $user) {
            $account = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($user, 'DefaultProvider');
            $this->outputLine('Trying to send notification to user "%s".', [$account->getAccountIdentifier()]);
            if ($account instanceof \Neos\Flow\Security\Account) {
                $this->notificationService->sendToUser(
                    $account,
                    $app,
                    $subject,
                    $message,
                    $objectType,
                    $objectId !== '' ? $objectId : null,
                    $link !== '' ? $link : null,
                    $icon !== '' ? $icon : null
                );
                $this->outputLine('Notification sent to user "%s".', [$user]);
                $send++;
            }
        }


        $this->outputLine('Notification sent to %d user(s) of %d.', [$send, \count($userList)]);
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
