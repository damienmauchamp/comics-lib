<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'string', length: 255)]
	private $name;

	#[ORM\OneToMany(mappedBy: 'type', targetEntity: ItemCollection::class)]
	private $collections;

	public function __construct() {
		$this->collections = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setId(string $id): self {
		$this->id = $id;

		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return Collection<int, ItemCollection>
	 */
	public function getCollections(): Collection {
		return $this->collections;
	}

	public function addCollection(ItemCollection $collection): self {
		if(!$this->collections->contains($collection)) {
			$this->collections[] = $collection;
			$collection->setType($this);
		}

		return $this;
	}

	public function removeCollection(ItemCollection $collection): self {
		if($this->collections->removeElement($collection)) {
			// set the owning side to null (unless already changed)
			if($collection->getType() === $this) {
				$collection->setType(null);
			}
		}

		return $this;
	}
}
