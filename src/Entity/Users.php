<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity=SourceGoods::class, mappedBy="customer")
     */
    private $sourceGoods;

    /**
     * @ORM\OneToMany(targetEntity=Application::class, mappedBy="customer")
     */
    private $applications;

    /**
     * @ORM\OneToMany(targetEntity=Store::class, mappedBy="customer")
     */
    private $stores;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="customer")
     */
    private $ratings;

    /**
     * @ORM\OneToMany(targetEntity=ReportProduct::class, mappedBy="customer")
     */
    private $reportProducts;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="customer")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=ReportComment::class, mappedBy="customer")
     */
    private $reportComments;

    public function __construct()
    {
        $this->sourceGoods = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->stores = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->reportProducts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reportComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

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
            $sourceGood->setCustomer($this);
        }

        return $this;
    }

    public function removeSourceGood(SourceGoods $sourceGood): self
    {
        if ($this->sourceGoods->removeElement($sourceGood)) {
            // set the owning side to null (unless already changed)
            if ($sourceGood->getCustomer() === $this) {
                $sourceGood->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setCustomer($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getCustomer() === $this) {
                $application->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Store>
     */
    public function getStores(): Collection
    {
        return $this->stores;
    }

    public function addStore(Store $store): self
    {
        if (!$this->stores->contains($store)) {
            $this->stores[] = $store;
            $store->setCustomer($this);
        }

        return $this;
    }

    public function removeStore(Store $store): self
    {
        if ($this->stores->removeElement($store)) {
            // set the owning side to null (unless already changed)
            if ($store->getCustomer() === $this) {
                $store->setCustomer(null);
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
            $rating->setCustomer($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getCustomer() === $this) {
                $rating->setCustomer(null);
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
            $reportProduct->setCustomer($this);
        }

        return $this;
    }

    public function removeReportProduct(ReportProduct $reportProduct): self
    {
        if ($this->reportProducts->removeElement($reportProduct)) {
            // set the owning side to null (unless already changed)
            if ($reportProduct->getCustomer() === $this) {
                $reportProduct->setCustomer(null);
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
            $comment->setCustomer($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCustomer() === $this) {
                $comment->setCustomer(null);
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
            $reportComment->setCustomer($this);
        }

        return $this;
    }

    public function removeReportComment(ReportComment $reportComment): self
    {
        if ($this->reportComments->removeElement($reportComment)) {
            // set the owning side to null (unless already changed)
            if ($reportComment->getCustomer() === $this) {
                $reportComment->setCustomer(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->email;
    }
}
