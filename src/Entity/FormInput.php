<?php

namespace App\Entity;

use App\Repository\FormInputRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormInputRepository::class)]
class FormInput
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    private static string $currentLang = 'en';

    // JSON translation storage
    #[ORM\Column(type: Types::JSON)]
    private array $name = [];

    #[ORM\Column(type: Types::JSON)]
    private array $label = [];

    #[ORM\Column(type: Types::JSON)]
    private array $default_value = [];

    #[ORM\Column(type: Types::JSON)]
    private array $options = [];

    #[ORM\Column]
    private ?bool $mapped = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column]
    private ?bool $required = null;

    #[ORM\ManyToOne(inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Form $parent = null;

    #[ORM\Column]
    private ?int $order_num = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modified_at = null;

    #[ORM\ManyToOne]
    private ?FormType $type = null;

    public function __construct()
    {
        $this->name = [];
        $this->label = [];
        $this->default_value = [];
        $this->options = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Smart getters/setters
    public function getName(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->name[$lang] ?? $this->name['en'] ?? null;
    }

    public function setName(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->name[$lang] = $value;
        return $this;
    }

    public function getLabel(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->label[$lang] ?? $this->label['en'] ?? null;
    }

    public function setLabel(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->label[$lang] = $value;
        return $this;
    }

    public function getDefaultValue(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->default_value[$lang] ?? $this->default_value['en'] ?? null;
    }

    public function setDefaultValue(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->default_value[$lang] = $value;
        return $this;
    }

    public function getOptions(?string $lang = null): ?array
    {
        $lang = $lang ?? self::$currentLang;
        return $this->options[$lang] ?? $this->options['en'] ?? null;
    }

    public function setOptions(?array $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->options[$lang] = $value;
        return $this;
    }

    // Methods to get/set all translations
    public function getNameTranslations(): array
    {
        return $this->name;
    }

    public function setNameTranslations(array $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLabelTranslations(): array
    {
        return $this->label;
    }

    public function setLabelTranslations(array $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getDefaultValueTranslations(): array
    {
        return $this->default_value;
    }

    public function setDefaultValueTranslations(array $default_value): static
    {
        $this->default_value = $default_value;
        return $this;
    }

    public function getOptionsTranslations(): array
    {
        return $this->options;
    }

    public function setOptionsTranslations(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function isMapped(): ?bool
    {
        return $this->mapped;
    }

    public function setMapped(bool $mapped): static
    {
        $this->mapped = $mapped;
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

    public function isRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;
        return $this;
    }

    public function getParent(): ?Form
    {
        return $this->parent;
    }

    public function setParent(?Form $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getOrderNum(): ?int
    {
        return $this->order_num;
    }

    public function setOrderNum(int $order_num): static
    {
        $this->order_num = $order_num;
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

    public function getType(): ?FormType
    {
        return $this->type;
    }

    public function setType(?FormType $type): static
    {
        $this->type = $type;
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

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }
}
