<?php

namespace App\Entity;

use App\Repository\IssueBackupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueBackupRepository::class)]
class IssueBackup {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\ManyToOne(targetEntity: Issue::class, inversedBy: 'backups')]
	#[ORM\JoinColumn(nullable: false)]
	private $issue;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $number;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $name;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $image;

	#[ORM\Column(type: 'text', nullable: true)]
	private $notes;

	public function getId(): ?int {
		return $this->id;
	}

	public function getIssue(): ?Issue {
		return $this->issue;
	}

	public function setIssue(?Issue $issue): self {
		$this->issue = $issue;

		return $this;
	}

	public function getNumber(): ?int {
		return $this->number;
	}

	public function setNumber(?int $number): self {
		$this->number = $number;

		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(?string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getImage(): ?string {
		return $this->image;
	}

	public function setImage(?string $image): self {
		$this->image = $image;

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
