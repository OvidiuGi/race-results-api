<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ORM\Table(name: 'result')]
class Result
{
    public const DISTANCE_MEDIUM = 'medium';

    public const DISTANCE_LONG = 'long';

    public const DISTANCES = [
        self::DISTANCE_MEDIUM,
        self::DISTANCE_LONG,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    public string $fullName = '';

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: self::DISTANCES)]
    #[Assert\NotBlank]
    public string $distance = '';

    #[ORM\Column(type: 'time_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    private \DateTimeImmutable $time;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    public string $ageCategory = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    public ?int $overallPlacement = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    public ?int $ageCategoryPlacement = null;

    #[ORM\ManyToOne(targetEntity: Race::class)]
    #[ORM\JoinColumn(name: 'race_id', referencedColumnName: 'id')]
    private Race $race;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTime(\DateTimeImmutable $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
    }

    public function setRace(Race $race): self
    {
        $this->race = $race;

        return $this;
    }

    public function getRace(): Race
    {
        return $this->race;
    }
}
