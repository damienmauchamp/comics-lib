<?php

namespace App\Entity;

use App\Repository\ItemCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemCollectionRepository::class)]
class ItemCollection {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'string', length: 255)]
	private $name;

	#[ORM\ManyToOne(targetEntity: Imprint::class, inversedBy: 'collections')]
	private $imprint;

	#[ORM\ManyToOne(targetEntity: Type::class, inversedBy: 'collections')]
	private $type;

	#[ORM\Column(type: 'boolean')]
	private $official;

	#[ORM\OneToMany(mappedBy: 'collection', targetEntity: Item::class)]
	private $items;

	public function __construct() {
		$this->items = new ArrayCollection();
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

	public function getImprint(): ?Imprint {
		return $this->imprint;
	}

	public function setImprint(?Imprint $imprint): self {
		$this->imprint = $imprint;

		return $this;
	}

	public function getType(): ?Type {
		return $this->type;
	}

	public function setType(?Type $type): self {
		$this->type = $type;

		return $this;
	}

	public function isOfficial(): ?bool {
		return $this->official;
	}

	public function setOfficial(bool $official): self {
		$this->official = $official;

		return $this;
	}

	/**
	 * @return Collection<int, Item>
	 */
	public function getItems(): Collection {
		return $this->items;
	}

	public function addItem(Item $item): self {
		if(!$this->items->contains($item)) {
			$this->items[] = $item;
			$item->setItemCollection($this);
		}

		return $this;
	}

	public function removeItem(Item $item): self {
		if($this->items->removeElement($item)) {
			// set the owning side to null (unless already changed)
			if($item->getItemCollection() === $this) {
				$item->setItemCollection(null);
			}
		}

		return $this;
	}
}
