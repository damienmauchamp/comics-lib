<?php

namespace App\Entity;

use App\Repository\ItemIssueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemIssueRepository::class)]
class ItemIssue {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'string', length: 10, nullable: true)]
	private $number;

	#[ORM\ManyToOne(targetEntity: Item::class)]
	#[ORM\JoinColumn(nullable: false)]
	private $item;

	#[ORM\ManyToOne(targetEntity: Issue::class)]
	#[ORM\JoinColumn(nullable: false)]
	private $issue;

	#[ORM\Column(type: 'text', nullable: true)]
	private $notes;

	public function getId(): ?int {
		return $this->id;
	}

	public function getNumber(): ?string {
		return $this->number;
	}

	public function setNumber(?string $number): self {
		$this->number = $number;

		return $this;
	}

	public function getItem(): ?Item {
		return $this->item;
	}

	public function setItem(?Item $item): self {
		$this->item = $item;

		return $this;
	}

	public function getIssue(): ?Issue {
		return $this->issue;
	}

	public function setIssue(?Issue $issue): self {
		$this->issue = $issue;

		return $this;
	}

	public function getNotes(): ?string {
		return $this->notes;
	}

	public function setNotes(?string $notes): self {
		$this->notes = $notes;

		return $this;
	}
}
