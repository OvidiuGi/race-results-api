<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use App\Controller\ImportAction;
use App\Dto\RaceDto;
use App\Repository\RaceRepository;
use ApiPlatform\OpenApi\Model;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RaceRepository::class, readOnly: true)]
#[ORM\Table(name: 'race')]
#[
    ApiResource(
        operations: [
            new GetCollection(),
            new Post(
                controller: ImportAction::class,
                openapi: new Model\Operation(
                    responses: [
                        200 =>
                            new OpenApiResponse(
                                description: 'Imported results',
                                content: new \ArrayObject([
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'string',
                                            'example' => 'Imported 100 results',
                                        ],
                                    ],
                                ])
                            ),
                    ],
                    summary: 'Create a Race resource and import results from a CSV file',
                    description: 'Create a Race resource and import results from a CSV file',
                    requestBody: new Model\RequestBody(
                        content: new \ArrayObject([
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => [
                                            'type' => 'string',
                                            'example' => 'Race 1'
                                        ],
                                        'date' => [
                                            'type' => 'string',
                                            'format' => 'date-time',
                                            'example' => '2021-01-01T00:00:00+00:00'
                                        ],
                                        'file' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                            'description' => 'Upload the CSV file',
                                            'example' => 'import.csv'
                                        ],
                                    ]
                                ]
                            ]
                        ])
                    )
                ),
                deserialize: false,
                name: 'import',
            )
        ],
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
    private int $id;

    #[ORM\Column(type: 'string')]
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

    public static function createFromDto(RaceDto $dto): self
    {
        $race = new self();

        $race->title = $dto->title;
        $race->date = $dto->date;

        return $race;
    }
}
