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

    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_en = null;

    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label_en = null;

    private ?string $default_value = null;

    #[ORM\Column]
    private ?bool $mapped = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column]
    private ?bool $required = null;

    private ?array $options = null;

    #[ORM\ManyToOne(inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Form $parent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $default_value_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $default_value_en = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $options_hu = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $options_en = null;

    #[ORM\Column]
    private ?int $order_num = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modified_at = null;

    #[ORM\ManyToOne]
    private ?FormType $type = null;

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

    public function getNameHu(): ?string
    {
        return $this->name_hu;
    }

    public function setNameHu(?string $name_hu): static
    {
        $this->name_hu = $name_hu;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->name_en;
    }

    public function setNameEn(?string $name_en): static
    {
        $this->name_en = $name_en;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLabelHu(): ?string
    {
        return $this->label_hu;
    }

    public function setLabelHu(?string $label_hu): static
    {
        $this->label_hu = $label_hu;

        return $this;
    }

    public function getLabelEn(): ?string
    {
        return $this->label_en;
    }

    public function setLabelEn(?string $label_en): static
    {
        $this->label_en = $label_en;

        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->default_value;
    }

    public function setDefaultValue(?string $default_value): static
    {
        $this->default_value = $default_value;

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

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

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

    public function getDefaultValueHu(): ?string
    {
        return $this->default_value_hu;
    }

    public function setDefaultValueHu(?string $default_value_hu): static
    {
        $this->default_value_hu = $default_value_hu;

        return $this;
    }

    public function getDefaultValueEn(): ?string
    {
        return $this->default_value_en;
    }

    public function setDefaultValueEn(?string $default_value_en): static
    {
        $this->default_value_en = $default_value_en;

        return $this;
    }

    public function getOptionsHu(): ?array
    {
        return $this->options_hu;
    }

    public function setOptionsHu(?array $options_hu): static
    {
        $this->options_hu = $options_hu;

        return $this;
    }

    public function getOptionsEn(): ?array
    {
        return $this->options_en;
    }

    public function setOptionsEn(?array $options_en): static
    {
        $this->options_en = $options_en;

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

    public function __toString(): string
    {
        return $this->getName() ?? '';
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
}
