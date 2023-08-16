<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Repository\ResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ORM\Table(name: 'result')]
#[
    ApiResource(
        uriTemplate: '/races/{raceId}/results',
        operations: [
            new GetCollection()
        ],
        uriVariables: [
            'raceId' => new Link(toProperty: 'race', fromClass: Race::class)
        ]
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
    )
]
#[
    ApiResource(
        operations: [
            new Patch()
        ],
        denormalizationContext: [
            'groups' => [
                'edit'
            ]
        ]
    )
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
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['edit'])]
    public string $fullName = '';

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: self::DISTANCES), Assert\NotBlank]
    #[Groups(['edit'])]
    public string $distance = '';

    #[ORM\Column(type: 'time_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['edit'])]
    private \DateTimeImmutable $finishTime;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['edit'])]
    public string $ageCategory = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    public ?int $overallPlacement = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    public ?int $ageCategoryPlacement = null;

    #[ORM\ManyToOne(targetEntity: Race::class, inversedBy: 'results')]
    #[ORM\JoinColumn(name: 'race_id', referencedColumnName: 'id')]
    #[Groups('edit')]
    private ?Race $race;

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

    public function setRace(?Race $race): self
    {
        $this->race = $race;

        return $this;
    }

    public function getRace(): ?Race
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
