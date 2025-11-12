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
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 * @ORM\Table(
 *     name="fucodo_notification_domain_model_notification",
 *     indexes={
 *         @ORM\Index(name="idx_fucodo_notification_account", columns={"account"}),
 *         @ORM\Index(name="idx_fucodo_notification_expirationDate", columns={"expirationDate"}),
 *         @ORM\Index(name="idx_fucodo_notification_createdAt", columns={"createdAt"})
 *     })
 */
class Notification implements JsonSerializable
{
    /**
     * @var Bootstrap
     * @Flow\Inject
     */
    protected $bootstrap;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @ORM\Id
     * @ORM\Column (type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="app", type="string", length=128)
     * @var string $app
     */
    protected string $app;

    /**
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="Neos\Flow\Security\Account", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="account", referencedColumnName="persistence_object_identifier", onDelete="CASCADE", nullable=false, unique=false, options={"unsigned"=true})})
     * @var Account
     */
    protected $account;

    /**
     * @var \DateTimeImmutable $createdAt
     */
    protected \DateTimeImmutable $createdAt;

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

    /**
     * @var \DateTimeImmutable
     */
    protected \DateTimeImmutable $expirationDate;

    // ─── Getters/Setters ─────────────────────────────────────────────────────────

    public function __construct()
    {
        $this->expirationDate = new \DateTimeImmutable('+3 months');
        $this->createdAt = new \DateTimeImmutable('now');
    }

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
        $this->app = substr($app, 0, 128);
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $dateTimeImmutable): void
    {
        $this->createdAt = $dateTimeImmutable;
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

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): Notification
    {
        $this->account = $account;
        return $this;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeImmutable $expirationDate): Notification
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    // ─── Helper ─────────────────────────────────────────

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
            'account' => $this->persistenceManager->getIdentifierByObject($this->account),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'object_type' => $this->objectType,
            'object_id' => $this->objectId,
            'subject' => $this->subject,
            'subject_parameters' => $this->subjectParameters,
            'message' => $this->message,
            'message_parameters' => $this->messageParameters,
            'link' => $this->link,
            'icon' => $this->icon,
            'expirationDate' => $this->expirationDate->format('Y-m-d H:i:s'),
            'actions' => $this->actions,
        ];
    }
}
