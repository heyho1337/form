<?php

namespace App\Entity;

use App\Repository\FormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormRepository::class)]
class Form
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    private static string $currentLang = 'en';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    // JSON translation storage
    #[ORM\Column(type: Types::JSON)]
    private array $subject = [];

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\ManyToOne]
    private ?Menu $redirrect = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modified_at = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, FormInput>
     */
    #[ORM\OneToMany(targetEntity: FormInput::class, mappedBy: 'parent', orphanRemoval: true)]
    private Collection $children;

    #[ORM\Column]
    private ?bool $captcha = null;

    #[ORM\Column]
    private ?bool $aszf = null;

    #[ORM\Column]
    private ?bool $any = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->subject = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    // Smart getters/setters
    public function getSubject(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->subject[$lang] ?? $this->subject['en'] ?? null;
    }

    public function setSubject(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->subject[$lang] = $value;
        return $this;
    }

    // Methods to get/set all translations
    public function getSubjectTranslations(): array
    {
        return $this->subject;
    }

    public function setSubjectTranslations(array $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getRedirrect(): ?Menu
    {
        return $this->redirrect;
    }

    public function setRedirrect(?Menu $redirrect): static
    {
        $this->redirrect = $redirrect;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modified_at;
    }

    public function setModifiedAt(\DateTimeImmutable $modified_at): static
    {
        $this->modified_at = $modified_at;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return Collection<int, FormInput>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(FormInput $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(FormInput $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    public function isCaptcha(): ?bool
    {
        return $this->captcha;
    }

    public function setCaptcha(bool $captcha): static
    {
        $this->captcha = $captcha;
        return $this;
    }

    public function isAszf(): ?bool
    {
        return $this->aszf;
    }

    public function setAszf(bool $aszf): static
    {
        $this->aszf = $aszf;
        return $this;
    }

    public function isAny(): ?bool
    {
        return $this->any;
    }

    public function setAny(bool $any): static
    {
        $this->any = $any;
        return $this;
    }

    public static function setCurrentLang(string $lang): void
    {
        self::$currentLang = $lang;
    }

    public static function getCurrentLang(): string
    {
        return self::$currentLang;
    }
}
