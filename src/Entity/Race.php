<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\RaceDto;
use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RaceRepository::class)]
#[ORM\Table(name: 'race')]
#[
    ApiResource(
        operations: [
            new GetCollection()
        ]
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'name',
            'date',
            'averageFinishMedium',
            'averageFinishLong'
        ]
    )
]
#[UniqueEntity(fields: ['title', 'date'], message: 'A race with this title and date already exists!')]
class Race
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\NotBlank]
    public string $title = '';

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $averageFinishMedium;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $averageFinishLong;

    #[ORM\OneToMany(mappedBy: 'race', targetEntity: Result::class, cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private Collection $results;

    public function __construct()
    {
        $this->results = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setAverageFinishMedium(?\DateTimeImmutable $averageFinishMedium): self
    {
        $this->averageFinishMedium = $averageFinishMedium;

        return $this;
    }

    public function getAverageFinishMedium(): ?\DateTimeImmutable
    {
        return $this->averageFinishMedium;
    }

    public function setAverageFinishLong(?\DateTimeImmutable $averageFinishLong): self
    {
        $this->averageFinishLong = $averageFinishLong;

        return $this;
    }

    public function getAverageFinishLong(): ?\DateTimeImmutable
    {
        return $this->averageFinishLong;
    }

    public function addResult(Result $result): void
    {
        if ($this->results->contains($result)) {
            return;
        }

        $this->results->add($result);
        $result->setRace($this);
    }

    public function removeResult(Result $result): void
    {
        $this->results->removeElement($result);
        $result->setRace(null);
    }

    public static function createFromDto(RaceDto $dto): self
    {
        $race = new self();

        $race->title = $dto->title;
        $race->date = $dto->getDate();

        return $race;
    }
}
