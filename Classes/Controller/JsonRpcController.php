<?php
declare(strict_types=1);

namespace fucodo\Notification\Controller;

use fucodo\Notification\Domain\Model\Notification;
use fucodo\Notification\Domain\Repository\NotificationRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Context as SecurityContext;

class JsonRpcController extends ActionController
{
    /**
     * @Flow\Inject
     * @var NotificationRepository
     */
    protected NotificationRepository $notificationRepository;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected Context $securityContext;

    /**
     * A list of IANA media types which are supported by this controller
     *
     * @var array
     * @see http://www.iana.org/assignments/media-types/index.html
     */
    protected $supportedMediaTypes = ['text/html', 'application/json'];

    public function indexAction()
    {
        $requestBody = (string)$this->request->getHttpRequest()->getBody();
        $payload = json_decode($requestBody, true);

        if (!is_array($payload)) {
            return $this->encodeError(null, -32700, 'Parse error');
        }

        $jsonrpc = $payload['jsonrpc'] ?? null;
        $method = $payload['method'] ?? null;
        $params = $payload['params'] ?? [];
        $id = $payload['id'] ?? null;

        if ($jsonrpc !== '2.0' || !is_string($method)) {
            return $this->encodeError($id, -32600, 'Invalid Request');
        }

        $account = $this->securityContext->getAccount();
        if ($account === null) {
            return $this->encodeError($id, -32600, 'Not authenticated');
        }

        #try {
            switch ($method) {
                case 'notifications.list':
                    $result = $this->handleList($account, is_array($params) ? $params : []);
                    return $this->encodeResult($id, $result);

                case 'notifications.markRead':
                    $result = $this->handleMarkRead($account, is_array($params) ? $params : []);
                    return $this->encodeResult($id, $result);

                default:
                    return $this->encodeError($id, -32601, 'Method not found');
            }
        #} catch (\Throwable $e) {
        #    // You can log $e via Flow's logger here.
        #    return $this->encodeError($id, -32603, 'Internal error');
        #}
    }

    private function handleList(Account $account, array $params): array
    {
        $limit = isset($params['limit']) ? (int)$params['limit'] : 50;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

        $limit = max(1, min($limit, 200));
        $offset = max(0, $offset);

        $rows = $this->notificationRepository->findByUserIdentifier($account, $limit, $offset)->toArray();

        $notifications = array_map(
            static function (Notification $row): array {
                return $row->jsonSerialize();
            },
            $rows
        );

        return [
            'notifications' => $notifications,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    private function handleMarkRead(Account$account, array $params): array
    {
        if (isset($params['ids']) && is_array($params['ids'])) {
            $ids = array_values(
                array_filter(
                    array_map('intval', $params['ids']),
                    static fn(int $id): bool => $id > 0
                )
            );

            if ($ids === []) {
                return ['status' => 'ok', 'affected' => 0];
            }

            $affected = $this->notificationRepository->deleteByIdsForUser($ids, $account);

            return [
                'status' => 'ok',
                'affected' => $affected,
            ];
        }

        if (isset($params['id'])) {
            $id = (int)$params['id'];
            if ($id <= 0) {
                return ['status' => 'error', 'message' => 'Invalid id'];
            }

            $affected = $this->notificationRepository->deleteByIdForUser($id, $userIdentifier);

            return [
                'status' => $affected > 0 ? 'ok' : 'not-found',
                'affected' => $affected,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Missing "id" or "ids" parameter',
        ];
    }

    private function encodeResult(mixed $id, mixed $result): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'result' => $result,
            'id' => $id,
        ], JSON_THROW_ON_ERROR);
    }

    private function encodeError(mixed $id, int $code, string $message, mixed $data = null): string
    {
        $error = ['code' => $code, 'message' => $message];
        if ($data !== null) {
            $error['data'] = $data;
        }

        return json_encode([
            'jsonrpc' => '2.0',
            'error' => $error,
            'id' => $id,
        ], JSON_THROW_ON_ERROR);
    }
}
