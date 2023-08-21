<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
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
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RaceRepository::class)]
#[ORM\Table(name: 'race')]
#[
    ApiResource(
        operations: [
            new GetCollection(),
            new Post(
                controller: ImportAction::class,
                openapi: new Model\Operation(
                    responses: [
                        201 =>
                            new OpenApiResponse(
                                content: new \ArrayObject([
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'json',
                                            'example' => '{ "race": {
                                                "id" : 100,
                                                "title" : "Race 1",
                                                "date" : "2021-01-01",
                                                "averageFinishTimeMedium" :"00:00:00",
                                                "averageFinishTimeLong" : "00:00:00"
                                            },
                                            "message" : {
                                                "success" : "Successfully imported the results",
                                                "totalNumber": 10,
                                                "invalidRows": "none"
                                            }}',
                                        ],
                                    ],
                                ])
                            ),
                        400 =>
                            new OpenApiResponse(
                                description: 'Bad Request Scenarios: Invalid File, Missing File, Invalid Header',
                                content: new \ArrayObject([
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'json',
                                            'example' => '{ "message": "" }',
                                        ],
                                    ],
                                ])
                            ),
                        409 =>
                            new OpenApiResponse(
                                description: 'Conflict if we are trying to create a race with the same title and date',
                                content: new \ArrayObject([
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'json',
                                            'example' => '{ 
                                            "message": 
                                            "The race with title: Race 1 and date: 2021-01-01 already exists!" }',
                                        ],
                                    ],
                                ])
                            )
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
                                            'example' => 'import.csv',
                                        ],
                                    ]
                                ],
                            ]
                        ])
                    )
                ),
                input: [RaceDto::class, File::class],
                deserialize: false,
                name: 'import',
            )
        ],
        normalizationContext: ['groups' => 'read'],
        denormalizationContext: ['groups' => 'write'],
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'title',
            'date',
            'averageFinishMedium',
            'averageFinishLong'
        ]
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'title' => 'partial',
        ]
    ), UniqueEntity(fields: ['title', 'date'], message: 'A race with this title and date already exists!')
]
class Race
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['read', 'write'])]
    public string $title = '';

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['read', 'write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['read', 'write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i:s'])]
    private ?\DateTimeImmutable $averageFinishMedium;

    #[ORM\Column(type: 'time_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['read', 'write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i:s'])]
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
