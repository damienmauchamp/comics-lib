<?php

namespace App\Entity;

use App\Repository\ImprintRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImprintRepository::class)]
class Imprint {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'string', length: 255)]
	private $name;

	#[ORM\ManyToOne(targetEntity: Publisher::class, inversedBy: 'imprints')]
	private $publisher;

	#[ORM\OneToMany(mappedBy: 'imprint', targetEntity: ItemCollection::class)]
	private $collections;

	public function __construct() {
		$this->collections = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getPublisher(): ?Publisher {
		return $this->publisher;
	}

	public function setPublisher(?Publisher $publisher): self {
		$this->publisher = $publisher;

		return $this;
	}

	/**
	 * @return ItemCollection<int, ItemCollection>
	 */
	public function getCollections(): Collection {
		return $this->collections;
	}

	public function addCollection(Collection $collection): self {
		if(!$this->collections->contains($collection)) {
			$this->collections[] = $collection;
			$collection->setImprint($this);
		}

		return $this;
	}

	public function removeCollection(Collection $collection): self {
		if($this->collections->removeElement($collection)) {
			// set the owning side to null (unless already changed)
			if($collection->getImprint() === $this) {
				$collection->setImprint(null);
			}
		}

		return $this;
	}
}
