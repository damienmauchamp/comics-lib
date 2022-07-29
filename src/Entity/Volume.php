<?php

namespace App\Entity;

use App\Repository\IssueRepository;
use App\Repository\VolumeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolumeRepository::class)]
class Volume {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $idc;

	#[ORM\Column(type: 'string', length: 255)]
	private $name;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private $description;

	#[ORM\Column(type: 'string', length: 255)]
	private $image;

	#[ORM\Column(type: 'integer')]
	private $number_issues;

	#[ORM\ManyToOne(targetEntity: Publisher::class, fetch: 'EAGER', inversedBy: 'volumes')]
	private $publisher;

	#[ORM\Column(type: 'integer', nullable: true)]
	private $start_year;

	#[ORM\OneToMany(mappedBy: 'volume', targetEntity: Issue::class)]
	private $issues;

	private ?Issue $last_read_issue = null;
	private ?Issue $next_to_read_issue = null;

	private array $issues_read = [];
	private int $number_of_issues_read = 0;

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

	public function getNumberIssues(): ?int {
		return $this->number_issues;
	}

	public function setNumberIssues(int $number_issues): self {
		$this->number_issues = $number_issues;

		return $this;
	}

	public function getPublisher(): ?Publisher {
		return $this->publisher;
	}

	public function setPublisher(?Publisher $publisher): self {
		$this->publisher = $publisher;

		return $this;
	}

	public function getStartYear(): ?int {
		return $this->start_year;
	}

	public function setStartYear(?int $start_year): self {
		$this->start_year = $start_year;

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
			$issue->setVolume($this);
		}

		return $this;
	}

	public function removeIssue(Issue $issue): self {
		if($this->issues->removeElement($issue)) {
			// set the owning side to null (unless already changed)
			if($issue->getVolume() === $this) {
				$issue->setVolume(null);
			}
		}

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

	/**
	 * @return Issue|null
	 */
	public function getNextToreadissue(): ?Issue {
		return $this->next_to_read_issue;
	}

	/**
	 * @param Issue|null $next_to_read_issue
	 */
	public function setNextToReadIssue(?Issue $next_to_read_issue): void {
		$this->next_to_read_issue = $next_to_read_issue;
	}

	/**
	 * @return array
	 */
	public function getIssuesRead(): array {
		return $this->issues_read;
	}

	/**
	 * @param array $issues_read
	 */
	public function setIssuesRead(array $issues_read): void {
		$this->issues_read = $issues_read;
		$this->number_of_issues_read = count($this->issues_read);
	}

	/**
	 * @return int
	 */
	public function getNumberOfIssuesRead(): int {
		return $this->number_of_issues_read;
	}

	/**
	 * @return Issue|null
	 */
	public function getLastReadIssue(): ?Issue {
		if($this->last_read_issue) {
			return $this->last_read_issue;
		}
		return $this->issues_read[0] ?? null;
	}

	/**
	 * @param Issue|null $last_read_issue
	 */
	public function setLastReadIssue(?Issue $last_read_issue): void {
		$this->last_read_issue = $last_read_issue;
	}

	public function getRemainingIssues(int $sub = 0): int {
		return $this->number_issues - $this->number_of_issues_read - $sub;
	}

	public function getRemainingIssuesToString(int $sub = 0): string {
		$count = $this->getRemainingIssues($sub);
		return $count > 0 ? ($sub ? "+{$count} more" : "{$count} left") : '';
	}

	public function setIssuesProgress(IssueRepository $issueRepo): void {
		$issuesRead = $issueRepo->findByVolume($this, true, 'date_read', 'DESC');
		$this->setIssuesRead($issuesRead);

		$issueLastRead = $this->getLastReadIssue();
		$issueNextToRead = $issueRepo->findVolumeNextToReadIssue($this, $issueLastRead);
		$this->setLastreadissue($issueLastRead);
		$this->setNextToReadIssue($issueNextToRead);
	}

	public function isDone(): bool {
		return $this->getRemainingIssues() === 0;
	}

	public function getProgress(): float {
		if($this->isDone()) {
			return 100;
		}
		return $this->number_of_issues_read / $this->number_issues * 100;
	}

}
