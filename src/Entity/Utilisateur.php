<?php
namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephone = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Emprunt::class)]
    private Collection $emprunts;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Critique::class, orphanRemoval: true)]
    private Collection $critiques;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function __construct()
    {
        $this->emprunts = new ArrayCollection();
        $this->critiques = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantir que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getEmprunts(): Collection
    {
        return $this->emprunts;
    }

    public function getEmpruntsActifs(): Collection
    {
        return $this->emprunts->filter(function(Emprunt $emprunt) {
            return $emprunt->getDate_retour() === null;
        });
    }

    public function getHistoriqueEmprunts(): Collection
    {
        return $this->emprunts->filter(function(Emprunt $emprunt) {
            return $emprunt->getDateRetour() !== null;
        });
    }

    public function addEmprunt(Emprunt $emprunt): static
    {
        if (!$this->emprunts->contains($emprunt)) {
            $this->emprunts->add($emprunt);
            $emprunt->setUtilisateur($this);
        }
        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): static
    {
        if ($this->emprunts->removeElement($emprunt)) {
            if ($emprunt->getUtilisateur() === $this) {
                $emprunt->setUtilisateur(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Critique>
     */
    public function getCritiques(): Collection
    {
        return $this->critiques;
    }

    public function addCritique(Critique $critique): static
    {
        if (!$this->critiques->contains($critique)) {
            $this->critiques->add($critique);
            $critique->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCritique(Critique $critique): static
    {
        if ($this->critiques->removeElement($critique)) {
            // set the owning side to null (unless already changed)
            if ($critique->getUtilisateur() === $this) {
                $critique->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function hasEmpruntEnRetard(): bool
    {
        foreach ($this->getEmpruntsActifs() as $emprunt) {
            $dateRetourPrevue = $emprunt->getDate_emprunt()->modify('+14 days');
            if ($dateRetourPrevue < new \DateTime()) {
                return true;
            }
        }
        return false;
    }

    public function hasAlreadyBorrowed(Livre $livre): bool
    {
        foreach ($this->getEmpruntsActifs() as $emprunt) {
            if ($emprunt->getLivre()->getId() === $livre->getId()) {
                return true;
            }
        }
        return false;
    }

    public function canBorrow(): bool
    {
        return $this->getEmpruntsActifs()->count() < 3 && !$this->hasEmpruntEnRetard();
    }
}
