<?php

namespace App\Entity;

use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublisherRepository::class)]
class Publisher {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $idc;

	#[ORM\Column(type: 'string', length: 255)]
	private $name;

	#[ORM\Column(type: 'text', nullable: true)]
	private $description;

	#[ORM\Column(type: 'string', length: 255)]
	private $image;

	#[ORM\Column(type: 'datetime')]
	private $date_added;

	#[ORM\Column(type: 'datetime')]
	private $date_updated;

	#[ORM\OneToMany(mappedBy: 'publisher', targetEntity: Volume::class)]
	private $volumes;

	#[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'affiliations')]
	private $affiliated;

	#[ORM\OneToMany(mappedBy: 'affiliated', targetEntity: self::class)]
	private $affiliations;

	#[ORM\OneToMany(mappedBy: 'publisher', targetEntity: Imprint::class)]
	private $imprints;

	public function __construct() {
		$this->volumes = new ArrayCollection();
		$this->affiliations = new ArrayCollection();
		$this->imprints = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getIdc(): ?int {
		return $this->idc;
	}

	public function setIdc(?int $idc): self {
		$this->idc = $idc;

		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(?string $description): self {
		$this->description = $description;

		return $this;
	}

	public function getImage(): ?string {
		return $this->image;
	}

	public function setImage(string $image): self {
		$this->image = $image;

		return $this;
	}

	public function getDateAdded(): ?\DateTimeInterface {
		return $this->date_added;
	}

	public function setDateAdded(\DateTimeInterface $date_added): self {
		$this->date_added = $date_added;

		return $this;
	}

	public function getDateUpdated(): ?\DateTimeInterface {
		return $this->date_updated;
	}

	public function setDateUpdated(\DateTimeInterface $date_updated): self {
		$this->date_updated = $date_updated;

		return $this;
	}

	/**
	 * @return ItemCollection<int, Volume>
	 */
	public function getVolumes(): Collection {
		return $this->volumes;
	}

	public function addVolume(Volume $volume): self {
		if(!$this->volumes->contains($volume)) {
			$this->volumes[] = $volume;
			$volume->setPublisher($this);
		}

		return $this;
	}

	public function removeVolume(Volume $volume): self {
		if($this->volumes->removeElement($volume)) {
			// set the owning side to null (unless already changed)
			if($volume->getPublisher() === $this) {
				$volume->setPublisher(null);
			}
		}

		return $this;
	}

	public function getAffiliated(): ?self {
		return $this->affiliated;
	}

	public function setAffiliated(?self $affiliated): self {
		$this->affiliated = $affiliated;

		return $this;
	}

	/**
	 * @return ItemCollection<int, self>
	 */
	public function getAffiliations(): Collection {
		return $this->affiliations;
	}

	public function addAffiliation(self $affiliation): self {
		if(!$this->affiliations->contains($affiliation)) {
			$this->affiliations[] = $affiliation;
			$affiliation->setAffiliated($this);
		}

		return $this;
	}

	public function removeAffiliation(self $affiliation): self {
		if($this->affiliations->removeElement($affiliation)) {
			// set the owning side to null (unless already changed)
			if($affiliation->getAffiliated() === $this) {
				$affiliation->setAffiliated(null);
			}
		}

		return $this;
	}

	/**
	 * @return ItemCollection<int, Imprint>
	 */
	public function getImprints(): Collection {
		return $this->imprints;
	}

	public function addImprint(Imprint $imprint): self {
		if(!$this->imprints->contains($imprint)) {
			$this->imprints[] = $imprint;
			$imprint->setPublisher($this);
		}

		return $this;
	}

	public function removeImprint(Imprint $imprint): self {
		if($this->imprints->removeElement($imprint)) {
			// set the owning side to null (unless already changed)
			if($imprint->getPublisher() === $this) {
				$imprint->setPublisher(null);
			}
		}

		return $this;
	}
}
