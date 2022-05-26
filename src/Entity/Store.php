<?php

namespace App\Entity;

use App\Repository\StoreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StoreRepository::class)
 */
class Store
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nameStore;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $urlStore;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="stores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity=SourceGoods::class, mappedBy="store")
     */
    private $sourceGoods;

    /**
     * @ORM\OneToMany(targetEntity=AdditionalInfo::class, mappedBy="store")
     */
    private $additionalInfos;

    public function __construct()
    {
        $this->sourceGoods = new ArrayCollection();
        $this->additionalInfos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameStore(): ?string
    {
        return $this->nameStore;
    }

    public function setNameStore(string $nameStore): self
    {
        $this->nameStore = $nameStore;

        return $this;
    }

    public function getUrlStore(): ?string
    {
        return $this->urlStore;
    }

    public function setUrlStore(string $urlStore): self
    {
        $this->urlStore = $urlStore;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCustomer(): ?Users
    {
        return $this->customer;
    }

    public function setCustomer(?Users $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, SourceGoods>
     */
    public function getSourceGoods(): Collection
    {
        return $this->sourceGoods;
    }

    public function addSourceGood(SourceGoods $sourceGood): self
    {
        if (!$this->sourceGoods->contains($sourceGood)) {
            $this->sourceGoods[] = $sourceGood;
            $sourceGood->setStore($this);
        }

        return $this;
    }

    public function removeSourceGood(SourceGoods $sourceGood): self
    {
        if ($this->sourceGoods->removeElement($sourceGood)) {
            // set the owning side to null (unless already changed)
            if ($sourceGood->getStore() === $this) {
                $sourceGood->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AdditionalInfo>
     */
    public function getAdditionalInfos(): Collection
    {
        return $this->additionalInfos;
    }

    public function addAdditionalInfo(AdditionalInfo $additionalInfo): self
    {
        if (!$this->additionalInfos->contains($additionalInfo)) {
            $this->additionalInfos[] = $additionalInfo;
            $additionalInfo->setStore($this);
        }

        return $this;
    }

    public function removeAdditionalInfo(AdditionalInfo $additionalInfo): self
    {
        if ($this->additionalInfos->removeElement($additionalInfo)) {
            // set the owning side to null (unless already changed)
            if ($additionalInfo->getStore() === $this) {
                $additionalInfo->setStore(null);
            }
        }

        return $this;
    }
}
