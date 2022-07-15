<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\ManyToOne(targetEntity: ItemCollection::class, inversedBy: 'items')]
	private $collection;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $number;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $title;

	#[ORM\Column(type: 'date', nullable: true)]
	private $release_date;

	#[ORM\Column(type: 'string', length: 20, nullable: true)]
	private $isbn;

	#[ORM\Column(type: 'string', length: 255)]
	private $image;

	#[ORM\Column(type: 'boolean')]
	private $special;

	#[ORM\Column(type: 'text')]
	private $notes;

	#[ORM\OneToMany(mappedBy: 'item', targetEntity: ItemIssue::class)]
	private $issues;

	#[ORM\Column(type: 'datetime')]
	private $date_added;

	#[ORM\Column(type: 'datetime')]
	private $date_updated;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private $date_ignored;

	public function __construct() {
		$this->issues = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getItemCollection(): ?ItemCollection {
		return $this->collection;
	}

	public function setItemCollection(?ItemCollection $collection): self {
		$this->collection = $collection;

		return $this;
	}

	public function getNumber(): ?int {
		return $this->number;
	}

	public function setNumber(?int $number): self {
		$this->number = $number;

		return $this;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle(?string $title): self {
		$this->title = $title;

		return $this;
	}

	public function getReleaseDate(): ?\DateTimeInterface {
		return $this->release_date;
	}

	public function setReleaseDate(?\DateTimeInterface $release_date): self {
		$this->release_date = $release_date;

		return $this;
	}

	public function getIsbn(): ?string {
		return $this->isbn;
	}

	public function setIsbn(?string $isbn): self {
		$this->isbn = $isbn;

		return $this;
	}

	public function getImage(): ?string {
		return $this->image;
	}

	public function setImage(string $image): self {
		$this->image = $image;

		return $this;
	}

	public function isSpecial(): ?bool {
		return $this->special;
	}

	public function setSpecial(bool $special): self {
		$this->special = $special;

		return $this;
	}

	public function getNotes(): ?string {
		return $this->notes;
	}

	public function setNotes(string $notes): self {
		$this->notes = $notes;

		return $this;
	}

	/**
	 * @return Collection<int, Issue>
	 */
	public function getIssues(): Collection {
		return $this->issues;
	}

	public function addIssue(Issue $issue): self {
		if(!$this->issues->contains($issue)) {
			$this->issues[] = $issue;
		}

		return $this;
	}

	public function removeIssue(Issue $issue): self {
		$this->issues->removeElement($issue);

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

	public function getDateIgnored(): ?\DateTimeInterface {
		return $this->date_ignored;
	}

	public function setDateIgnored(?\DateTimeInterface $date_ignored): self {
		$this->date_ignored = $date_ignored;

		return $this;
	}

	public function createImageName(string $ext = 'jpg'): string {
		$unique_id = uniqid();
		$collectionName = $this->getItemCollection()->getName() ?? '';
		$name = preg_replace('/[^a-z\d]+/i', '-', "{$collectionName} {$this->getNumber()} {$this->getTitle()}");
		$name = substr($name, 0, 90)."-$unique_id";
		return "$name.{$ext}";
	}

	public function getFullName(): string {
		$number = $this->getNumber() ? ", tome {$this->getNumber()} " : '';
		return trim($this->getItemCollection()->getName()."{$number} - ".$this->getTitle(), ' -');
	}
}
