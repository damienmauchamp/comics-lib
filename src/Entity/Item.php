<?php

namespace App\Entity;

use App\Repository\IssueRepository;
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

	private ?Issue $last_read_issue = null;
	private ?Issue $next_to_read_issue = null;
	private array $issues_read = [];
	private int $number_of_issues_read = 0;
	private ?int $number_issues = null;
	private array $next_to_read_issues = [];

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

	public function getCollectionName(): string {
		return $this->getItemCollection()->getName();
	}

	public function getCollectionTypeName(): string {
		return $this->getItemCollection()->getType()->getName();
	}

	public function getFullName(): string {
		$number = $this->getNumber() ? ", tome {$this->getNumber()} " : '';
		return trim($this->getItemCollection()->getName()."{$number} - ".$this->getTitle(), ' -');
	}

	/**
	 * @return Issue|null
	 */
	public function getLastReadIssue(): ?Issue {
		if($this->last_read_issue) {
			return $this->last_read_issue;
		}
//		return $this->issues_read[0] ?? null;
		if(!$this->issues) {
			return null;
		}

//		$readIssues = $this->issues->filter(function ($itemIssue) {
//			/** @var ItemIssue $itemIssue */
//			return $itemIssue->getIssue()->isRead();
//		});
//		if(!$readIssues) {
		if(!$this->issues_read) {
			return null;
		}

//		if(count($this->issues_read) === 1) {
//			$this->last_read_issue = $this->issues_read[0]->getIssue();
//			return $this->last_read_issue;
//		}
//
//		foreach($this->issues_read as $itemIssue) {
//			/** @var ItemIssue $itemIssue */
//			if(!$this->last_read_issue ||
//				$itemIssue->getIssue()->getDateRead() > $this->last_read_issue->getDateRead()) {
//				$this->last_read_issue = $itemIssue->getIssue();
//			}
//		}

		return $this->last_read_issue;


//		dd($readIssues);


	}

	/**
	 * @param Issue|null $last_read_issue
	 */
	public function setLastReadIssue(?Issue $last_read_issue): void {
		$this->last_read_issue = $last_read_issue;
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

	public function setIssuesProgress(): void {

		$this->number_issues = count($this->getIssues());

//		$itemIssues = $this->getIssues()->getValues();

		$issuesRead = $this->issues->filter(function ($itemIssue) {
			/** @var ItemIssue $itemIssue */
			return $itemIssue->getIssue()->isRead();
		});
		$this->setIssuesRead($issuesRead->toArray());

		$issueLastRead = null;
		if(count($this->issues_read) === 1) {
			$issueLastRead = array_values($this->issues_read)[0]->getIssue();
		}
		else {
			foreach($this->issues_read as $itemIssue) {
				/** @var ItemIssue $itemIssue */
				if(!$issueLastRead ||
					$itemIssue->getIssue()->getDateRead() > $issueLastRead->getDateRead()) {
					$issueLastRead = $itemIssue->getIssue();
				}
			}
		}
		$this->setLastreadissue($issueLastRead);

		//
		$issueNextToRead = null;
		if($issueLastRead) {
//			$next = false;
			foreach($this->issues as $itemIssue) {
				/** @var ItemIssue $itemIssue */
				if(!$itemIssue->getIssue()->isRead()) {
					$issueNextToRead = $itemIssue->getIssue();
					break;
				}
//				if($next) {
//					$issueNextToRead = $itemIssue->getIssue();
//					break;
//				}
//				if($itemIssue->getIssue()->getId() === $issueLastRead->getId()) {
//					$next = true;
//				}
			}
		}
		$this->setNextToReadIssue($issueNextToRead);

		$this->setNextToReadIssues();
	}


	public function getNumberIssues(): ?int {
		return $this->number_issues;
	}

	/**
	 * @return int
	 */
	public function getNumberOfIssuesRead(): int {
		return $this->number_of_issues_read;
	}

	public function getRemainingIssues(int $sub = 0): int {
		return $this->number_issues - $this->number_of_issues_read - $sub;
	}

	public function getRemainingIssuesToString(int $sub = 0): string {
		if(!$this->getNumberOfIssuesRead()) {
			return "{$this->getNumberIssues()} issues";
		}
		return "{$this->getNumberOfIssuesRead()}/{$this->getNumberIssues()} issues";
//		return $count > 0 ? ($sub ? "+{$count} more" : "{$count} issues") : '';
	}

	public function isStarted(): bool {
		return $this->getNumberOfIssuesRead() > 0;
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

	public function getIssuesArray(): array {
		$itemIssues = $this->issues->toArray();
		return array_map(function ($itemIssue, $position) {
			/** @var ItemIssue $itemIssue */
			$issue = $itemIssue->getIssue();
			$issue->setPosition($position + 1);
			return $issue;
		}, $itemIssues, array_keys($itemIssues));
	}

	public function getNextToReadIssues(): array {
		return $this->next_to_read_issues;
	}

	public function setNextToReadIssues(): void {

		$ROWS = 3;
		$nextInMiddle = true;
		$sub = $nextInMiddle ? 0 : 1;
		$issues = $this->getIssuesArray();
//		dd($issues);
		$this->next_to_read_issues = [];

		if($ROWS >= $this->getNumberIssues()) {
			$this->next_to_read_issues = $issues;
		}
		else if($this->isDone()) {
			// last three
			$this->next_to_read_issues = array_slice($issues, -$ROWS, $ROWS, true);
		}
		else if(!$this->isStarted()) {
			// first three
			$this->next_to_read_issues = array_slice($issues, 0, $ROWS, false);
		}
		else {
			/**
			 * @var Issue $issue
			 */
			$indexFirst = 0;
			$indexNext = 0;
			$nextToReadIssue = $this->getNextToreadissue();
			foreach($issues as $index => $issue) {
				if($issue->getId() === $nextToReadIssue->getId()) {
					$indexNext = $index;
					break;
				}

			}

//			$from = $indexNext - floor($ROWS / 2);
//			$to = $indexNext + ceil($ROWS / 2) - $sub;
			$to = $indexNext + ($ROWS / 2) - $sub;
			$from = $to - $ROWS;
			if($from < 0) {
				$from = 0;
				$to = $ROWS - 1;
			}
			else if($to - $from > $ROWS) {
				$to = $indexNext + ceil($ROWS / 2) - $sub;
			}

//			dump(['from' => $from, 'to' => $to]);

//			if(!isset($issues[$to])) {
//				// last three
//				$this->next_to_read_issues = array_slice($issues, -$ROWS, $ROWS, true);
//			}
//			else {
			foreach($issues as $index => $issue) {
				if($from <= $index && $index <= $to && count($this->next_to_read_issues) < $ROWS) {
					$this->next_to_read_issues[] = $issue;
				}
				else if($index > $to) {
					break;
				}
			}
//			}
//			dump($issues, [
//				'$nextToReadIssue' => $nextToReadIssue,
//				'$nextToReadIssue->id' => $nextToReadIssue->getId(),
//				'$indexNext' => $indexNext,
//				'from' => $from,
//				'to' => $to,
//			], $this->next_to_read_issues);


//			if($this->getRemainingIssues() === 1) {
//				// three with next at the end
//			}
//			else {
			// three with next in the middle
//			$from = $indexFirst;
//			}
		}

//		foreach($this->issues as $itemIssue) {
//			if($this->isDone()) {
//				// last three
//			}
//			else if($this->isStarted()) {
//				if($this->getRemainingIssues() === 1) {
//					// three with next at the end
//				}
//				else {
//					// three with next in the middle
//				}
//			}
//			else {
//				// first three
//			}
//
//		}
	}
}
