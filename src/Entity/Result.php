<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Repository\ResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ORM\Table(name: 'result')]
#[
    ApiResource(
        operations: [
            new GetCollection(
                uriTemplate: '/races/{race_id}/results',
                uriVariables: ['race_id' => new Link(toProperty: 'race', fromClass: Race::class)]
            ),
            new Patch(),
        ],
        normalizationContext: ['groups' => 'read'],
        denormalizationContext: ['groups' => 'write'],
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'fullName',
            'distance',
            'finishTime',
            'ageCategory',
            'overallPlacement',
            'ageCategoryPlacement'
        ]
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'fullName' => 'partial',
            'distance' => 'exact',
            'ageCategory' => 'exact',
        ]
    ),
]
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
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['read', 'write'])]
    public string $fullName = '';

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: self::DISTANCES), Assert\NotBlank]
    #[Groups(['read', 'write'])]
    public string $distance = '';

    #[ORM\Column(type: 'time_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['read', 'write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i:s'])]
    private \DateTimeImmutable $finishTime;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['read', 'write'])]
    public string $ageCategory = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    #[Groups(['read'])]
    public ?int $overallPlacement = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    #[Groups(['read'])]
    public ?int $ageCategoryPlacement = null;

    #[ORM\ManyToOne(targetEntity: Race::class)]
    #[ORM\JoinColumn(name: 'race_id', referencedColumnName: 'id')]
    #[Groups(['read'])]
    private Race $race;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFinishTime(\DateTimeImmutable $finishTime): self
    {
        $this->finishTime = $finishTime;

        return $this;
    }

    public function getFinishTime(): \DateTimeImmutable
    {
        return $this->finishTime;
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

    /**
     * @throws \Exception
     */
    public static function createFromArray(array $data): self
    {
        $result = new self();

        $result->fullName = $data['fullName'];
        $result->distance = $data['distance'];
        $result->finishTime = new \DateTimeImmutable($data['finishTime']);
        $result->ageCategory = $data['ageCategory'];

        return $result;
    }
}
