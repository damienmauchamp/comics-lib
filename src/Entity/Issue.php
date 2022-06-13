<?php

namespace App\Entity;

use App\Repository\IssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
class Issue {
	#[ORM\Id]
               	#[ORM\GeneratedValue]
               	#[ORM\Column(type: 'integer')]
               	private $id;

	#[ORM\Column(type: 'integer', nullable: true)]
               	private $idc;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
               	private $name;

	#[ORM\Column(type: 'string', length: 10, nullable: true)]
               	private $number;

	#[ORM\Column(type: 'string', length: 255)]
               	private $image;

	#[ORM\Column(type: 'datetime', nullable: true)]
               	private $date_released;

	#[ORM\Column(type: 'datetime')]
               	private $date_added;

	#[ORM\Column(type: 'datetime')]
               	private $date_updated;

	#[ORM\ManyToOne(targetEntity: Volume::class, inversedBy: 'issues')]
               	private $volume;

	#[ORM\OneToMany(mappedBy: 'issue', targetEntity: ItemIssue::class)]
               	private $items;

    #[ORM\OneToMany(mappedBy: 'issue', targetEntity: IssueBackup::class)]
    private $backups;

	public function __construct() {
               		$this->items = new ArrayCollection();
                 $this->backups = new ArrayCollection();
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

	public function setName(?string $name): self {
               		$this->name = $name;
               
               		return $this;
               	}

	public function getNumber(): ?string {
               		return $this->number;
               	}

	public function setNumber(?string $number): self {
               		$this->number = $number;
               
               		return $this;
               	}

	public function getImage(): ?string {
               		return $this->image;
               	}

	public function setImage(string $image): self {
               		$this->image = $image;
               
               		return $this;
               	}

	public function getDateReleased(): ?\DateTimeInterface {
               		return $this->date_released;
               	}

	public function setDateReleased(?\DateTimeInterface $date_released): self {
               		$this->date_released = $date_released;
               
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

	public function getVolume(): ?Volume {
               		return $this->volume;
               	}

	public function setVolume(?Volume $volume): self {
               		$this->volume = $volume;
               
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
               			$item->addIssue($this);
               		}
               
               		return $this;
               	}

	public function removeItem(Item $item): self {
               		if($this->items->removeElement($item)) {
               			$item->removeIssue($this);
               		}
               
               		return $this;
               	}

    /**
     * @return Collection<int, IssueBackup>
     */
    public function getBackups(): Collection
    {
        return $this->backups;
    }

    public function addBackup(IssueBackup $backup): self
    {
        if (!$this->backups->contains($backup)) {
            $this->backups[] = $backup;
            $backup->setIssue($this);
        }

        return $this;
    }

    public function removeBackup(IssueBackup $backup): self
    {
        if ($this->backups->removeElement($backup)) {
            // set the owning side to null (unless already changed)
            if ($backup->getIssue() === $this) {
                $backup->setIssue(null);
            }
        }

        return $this;
    }
}
