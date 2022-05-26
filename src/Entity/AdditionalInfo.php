<?php

namespace App\Entity;

use App\Repository\AdditionalInfoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AdditionalInfoRepository::class)
 */
class AdditionalInfo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="date")
     */
    private $dateUpdate;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $url;

    /**
     * @ORM\Column(type="float")
     */
    private $averageRating;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="additionalInfos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Store::class, inversedBy="additionalInfos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $store;

    /**
     * @ORM\OneToMany(targetEntity=Statistic::class, mappedBy="additionalInfo")
     */
    private $statistics;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="additionalInfo")
     */
    private $ratings;

    /**
     * @ORM\OneToMany(targetEntity=ReportProduct::class, mappedBy="additionalInfo")
     */
    private $reportProducts;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="AdditionalInfo")
     */
    private $comments;

    public function __construct()
    {
        $this->statistics = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->reportProducts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(\DateTimeInterface $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    public function setAverageRating(float $averageRating): self
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return Collection<int, Statistic>
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }

    public function addStatistic(Statistic $statistic): self
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics[] = $statistic;
            $statistic->setAdditionalInfo($this);
        }

        return $this;
    }

    public function removeStatistic(Statistic $statistic): self
    {
        if ($this->statistics->removeElement($statistic)) {
            // set the owning side to null (unless already changed)
            if ($statistic->getAdditionalInfo() === $this) {
                $statistic->setAdditionalInfo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setAdditionalInfo($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getAdditionalInfo() === $this) {
                $rating->setAdditionalInfo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReportProduct>
     */
    public function getReportProducts(): Collection
    {
        return $this->reportProducts;
    }

    public function addReportProduct(ReportProduct $reportProduct): self
    {
        if (!$this->reportProducts->contains($reportProduct)) {
            $this->reportProducts[] = $reportProduct;
            $reportProduct->setAdditionalInfo($this);
        }

        return $this;
    }

    public function removeReportProduct(ReportProduct $reportProduct): self
    {
        if ($this->reportProducts->removeElement($reportProduct)) {
            // set the owning side to null (unless already changed)
            if ($reportProduct->getAdditionalInfo() === $this) {
                $reportProduct->setAdditionalInfo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAdditionalInfo($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAdditionalInfo() === $this) {
                $comment->setAdditionalInfo(null);
            }
        }

        return $this;
    }
}
