<?php

namespace App\Entity;

use App\Repository\FormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormRepository::class)]
class Form
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    private ?string $subject = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subject_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subject_en = null;

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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubjectHu(): ?string
    {
        return $this->subject_hu;
    }

    public function setSubjectHu(?string $subject_hu): static
    {
        $this->subject_hu = $subject_hu;

        return $this;
    }

    public function getSubjectEn(): ?string
    {
        return $this->subject_en;
    }

    public function setSubjectEn(?string $subject_en): static
    {
        $this->subject_en = $subject_en;

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
            // set the owning side to null (unless already changed)
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
}
