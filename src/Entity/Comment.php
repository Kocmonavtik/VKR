<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity=AdditionalInfo::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $AdditionalInfo;

    /**
     * @ORM\ManyToOne(targetEntity=Comment::class, inversedBy="comments")
     */
    private $response;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="response")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=RatingComment::class, mappedBy="comment")
     */
    private $ratingComments;

    /**
     * @ORM\OneToMany(targetEntity=ReportComment::class, mappedBy="comment")
     */
    private $reportComments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->ratingComments = new ArrayCollection();
        $this->reportComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getAdditionalInfo(): ?AdditionalInfo
    {
        return $this->AdditionalInfo;
    }

    public function setAdditionalInfo(?AdditionalInfo $AdditionalInfo): self
    {
        $this->AdditionalInfo = $AdditionalInfo;

        return $this;
    }

    public function getResponse(): ?self
    {
        return $this->response;
    }

    public function setResponse(?self $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(self $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setResponse($this);
        }

        return $this;
    }

    public function removeComment(self $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getResponse() === $this) {
                $comment->setResponse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RatingComment>
     */
    public function getRatingComments(): Collection
    {
        return $this->ratingComments;
    }

    public function addRatingComment(RatingComment $ratingComment): self
    {
        if (!$this->ratingComments->contains($ratingComment)) {
            $this->ratingComments[] = $ratingComment;
            $ratingComment->setComment($this);
        }

        return $this;
    }

    public function removeRatingComment(RatingComment $ratingComment): self
    {
        if ($this->ratingComments->removeElement($ratingComment)) {
            // set the owning side to null (unless already changed)
            if ($ratingComment->getComment() === $this) {
                $ratingComment->setComment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReportComment>
     */
    public function getReportComments(): Collection
    {
        return $this->reportComments;
    }

    public function addReportComment(ReportComment $reportComment): self
    {
        if (!$this->reportComments->contains($reportComment)) {
            $this->reportComments[] = $reportComment;
            $reportComment->setComment($this);
        }

        return $this;
    }

    public function removeReportComment(ReportComment $reportComment): self
    {
        if ($this->reportComments->removeElement($reportComment)) {
            // set the owning side to null (unless already changed)
            if ($reportComment->getComment() === $this) {
                $reportComment->setComment(null);
            }
        }

        return $this;
    }
}
