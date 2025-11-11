# fucodo/notification

Lightweight notifications package for Neos Flow applications. It provides:

- A `Notification` domain model persisted via Doctrine DBAL
- A `NotificationRepository` with convenience methods (find by user, delete by id(s), cleanup old)
- A `NotificationService` to send notifications to one or many users
- A CLI command to send notifications from the terminal
- A small Web Component to render notifications in the backend/top bar

## Requirements
- Neos Flow (see `composer.json`, version managed by your distribution)
- A database configured for Flow/Doctrine

## Installation

```
composer require fucodo/notification
```

Then update the database schema:

```
./flow doctrine:migrate
# or, if you prefer schema generation
./flow doctrine:update
```

The table name used is `fucodo_notification_domain_model_notification`.

## Usage

### Send notifications from PHP
Use the `NotificationService` to create notifications for one or multiple users. Parameters like `subjectParameters`, `messageParameters`, and `actions` are stored as JSON.

```php
use fucodo\Notification\Service\NotificationService;

class SomeService
{
    public function __construct(private NotificationService $notificationService) {}

    public function doSomething(): void
    {
        $this->notificationService->sendToUsers(
            users: ['jane.doe', 'john.doe'],
            app: 'fucodo.notification',
            subject: 'New report is ready',
            message: 'Click to view the latest monthly report.',
            objectType: 'report',
            objectId: 'report-2025-11',
            link: '/backend/reports/2025-11',
            icon: 'icon-report',
            subjectParameters: ['month' => 'Nov 2025'],
            messageParameters: ['severity' => 'info'],
            actions: [
                ['label' => 'Open', 'href' => '/backend/reports/2025-11'],
            ],
        );
    }
}
```

### Send notifications via CLI

```
./flow notification:sendmessage \
  --app "fucodo.notification" \
  --subject "Info" \
  --message "Hello from CLI" \
  --users "user1,user2" \
  --object-type "cli" \
  --object-id "optional-id" \
  --link "/some/target" \
  --icon "icon-name"
```

- `--users` is a comma-separated list and empties/spaces are ignored.
- The command reports how many users were notified.

### Reading and managing notifications
Use `NotificationRepository` in your code to fetch or delete entries:

```php
use fucodo\Notification\Domain\Repository\NotificationRepository;

// find last 20 notifications for a user
$result = $notificationRepository->findByUserIdentifier('jane.doe', 20, 0);

// delete a single notification for a user (mark as read)
$deleted = $notificationRepository->deleteByIdForUser(123, 'jane.doe');

// delete many for a user
$deletedTotal = $notificationRepository->deleteByIdsForUser([123,124,125], 'jane.doe');

// housekeeping: delete older than timestamp
$removed = $notificationRepository->deleteOlderThan(strtotime('-90 days'));
```

### Rendering in the UI (Web Component)
A minimal Web Component is bundled in `Resources/Public/WebComponents/fucodo-notifications/` and is used by the backend top bar include in
`Resources/Private/Backend/TopBar/Notification.html`.

Basic usage example in HTML:

```html
<fucodo-notifications
  fetch-url="/your/endpoint/that/returns/json"
  no-notifications-label="No notifications"
></fucodo-notifications>
```

The component expects a JSON list with objects similar to what `Notification::jsonSerialize()` produces.

## Data model
Main fields stored for each notification:

- `id` (auto-increment)
- `user` (string)
- `app` (string)
- `timestamp` (int, Unix time)
- `objectType`/`objectId` (strings)
- `subject`/`message` (strings)
- `subjectParameters`/`messageParameters` (JSON text, nullable)
- `link`/`icon` (nullable strings)
- `actions` (JSON text, nullable)

Note: inserts are performed via `NotificationRepository::add()` using DBAL and the entity is updated with the inserted `id`. A Flow signal `notificationAdded` is emitted and can be connected via AOP.

## Configuration notes
- Package key: `fucodo.notification`
- PHP namespace: `fucodo\\Notification`
- Autoload: PSR-4 from `Classes/`

No special settings are required. You can add controllers/endpoints to expose notification lists as needed by your project.

## Development
- Code style and patterns follow Neos Flow conventions.
- Run tests and linters of your distribution as usual.

## License
This package is released under the terms of the MIT License. See the repository root `LICENSE` if available, or include your own license file for distribution.
