<?php
declare(strict_types=1);

namespace fucodo\Notification\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\UriBuilder;

/**
 * @Flow\Entity
 */
class Notification implements JsonSerializable
{
    /**
     * @var Bootstrap
     * @Flow\Inject
     */
    protected $bootstrap;


    /**
     * @ORM\Id
     * @ORM\Column (type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="app", type="string", length=32)
     * @var string $app
     */
    protected string $app;

    /**
     * @ORM\Column(name="user", type="string", length=64)
     * @var string $user
     */
    protected string $user;

    /**
     * @ORM\Column(name="timestamp", type="integer")
     * @var int $timestamp
     */
    protected int $timestamp;

    /**
     * @ORM\Column(name="object_type", type="string", length=64)
     * @var string $objectType
     */
    protected string $objectType;

    /**
     * @ORM\Column(name="object_id", type="string", length=64)
     * @var string $objectId
     */
    protected string $objectId;

    /**
     * @ORM\Column(name="subject", type="string", length=64)
     * @var string $subject
     */
    protected string $subject;

    /**
     * @ORM\Column(name="subject_parameters", type="text", nullable=true)
     * @var string|null $subjectParameters
     */
    protected ?string $subjectParameters = null;

    /**
     * @ORM\Column(name="message", type="string", length=64, nullable=true)
     * @var string|null $message
     */
    protected ?string $message = null;

    /**
     * @ORM\Column(name="message_parameters", type="text", nullable=true)
     * @var string|null $messageParameters
     */
    protected ?string $messageParameters = null;

    /**
     * @ORM\Column(name="link", type="string", length=4000, nullable=true)
     * @var string|null $link
     */
    protected ?string $link = null;

    /**
     * @ORM\Column(name="icon", type="string", length=4000, nullable=true)
     * @var string|null $icon
     */
    protected ?string $icon = null;

    /**
     * @ORM\Column(name="actions", type="text", nullable=true)
     * @var string|null $actions
     */
    protected ?string $actions = null;

    // ─── Getters/Setters ─────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApp(): string
    {
        return $this->app;
    }

    public function setApp(string $app): void
    {
        $this->app = $app;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubjectParameters(): ?string
    {
        return $this->subjectParameters;
    }

    public function setSubjectParameters(?string $subjectParameters): void
    {
        $this->subjectParameters = $subjectParameters;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getMessageParameters(): ?string
    {
        return $this->messageParameters;
    }

    public function setMessageParameters(?string $messageParameters): void
    {
        $this->messageParameters = $messageParameters;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getActions(): ?string
    {
        return $this->actions;
    }

    public function setActions(?string $actions): void
    {
        $this->actions = $actions;
    }

    protected function getUriBuilder($absolute = true): UriBuilder
    {
        $requestHandler = $this->bootstrap->getActiveRequestHandler();
        if (!$requestHandler instanceof HttpRequestHandlerInterface) {
            throw new \RuntimeException('Could not access the HttpRequestHandler');
        }
        $actionRequest = ActionRequest::fromHttpRequest($requestHandler->getHttpRequest());
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($actionRequest);
        $uriBuilder->setCreateAbsoluteUri($absolute);
        return $uriBuilder;
    }

    // ─── JsonSerializable implementation ─────────────────────────────────────────

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'app' => $this->app,
            'user' => $this->user,
            'timestamp' => $this->timestamp,
            'object_type' => $this->objectType,
            'object_id' => $this->objectId,
            'subject' => $this->subject,
            'subject_parameters' => $this->subjectParameters,
            'message' => $this->message,
            'message_parameters' => $this->messageParameters,
            'link' => $this->link,
            'icon' => $this->icon,
            'actions' => $this->actions,
        ];
    }
}
