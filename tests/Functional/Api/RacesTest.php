<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\Factory\RaceFactory;
use App\DataFixtures\Story\DefaultRaceStory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RacesTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public const COUNT = 10;

    protected function setUp(): void
    {
        DefaultRaceStory::load();
    }

    public function testGetCollection(): void
    {
        static::createClient()->request('GET', '/api/races');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context'         => '/api/contexts/Race',
            '@id'              => '/api/races',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => self::COUNT,
        ]);
    }

    public function testDuplicateRace(): void
    {
        $race = RaceFactory::first();

        $uploadedFile = new UploadedFile(
            __DIR__ . '/fixtures/import.csv',
            'import',
            'text/csv',
        );

        static::createClient()->request('POST', '/api/races', [
            'json' => [
                'title' => $race->title,
                'date' => $race->getDate()->format('Y-m-d'),
            ],
//            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $uploadedFile
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(409);
        $this->assertJsonEquals([
            'message' => sprintf(
                'The race with title: %s and date: %s already exists!',
                $race->title,
                $race->getDate()->format('Y-m-d')
            )
        ]);
    }

    public function testInvalidHeader(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/fixtures/invalidHeader.csv',
            'invalidHeader',
            'text/csv',
        );

        static::createClient()->request('POST', '/api/races', [
            'json' => [
                'title' => 'Race Invalid',
                'date' => '2023-08-20',
            ],
//            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $uploadedFile
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonEquals([
            'message' => 'The header must contain the following fields: fullName, distance, finishTime, ageCategory'
        ]);
    }

    public function testRequestWithoutBody(): void
    {
        static::createClient()->request('POST', '/api/races');

        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonContains([
            "@context" => "/api/contexts/Error",
            "@type" => "hydra:Error",
            "hydra:title" => "An error occurred",
            "hydra:description" => ""
        ]);
    }

    public function testRequestWithInvalidFormatFile(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/fixtures/invalid.xml',
            'invalid',
            'application/xml',
        );

        static::createClient()->request('POST', '/api/races', [
            'json' => [
                'title' => 'Race Invalid',
                'date' => '2023-08-20',
            ],
//            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $uploadedFile
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->assertJsonEquals([
            'message' => 'File Type Not Supported! Only CSV files are allowed'
        ]);
    }

    public function testRequestWithoutFile(): void
    {
        static::createClient()->request('POST', '/api/races', [
            'json' => [
                'title' => 'Race Invalid',
                'date' => '2023-08-20',
            ],
//            'headers' => ['Content-Type' => 'multipart/form-data'],
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->assertJsonEquals([
            'message' => 'No file provided'
        ]);
    }

    public function testCreateRace(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/fixtures/import.csv',
            'import',
            'text/csv',
        );

        static::createClient()->request('POST', '/api/races', [
            'json' => [
                'title' => 'Race Valid',
                'date' => '2023-08-20',
            ],
//            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $uploadedFile
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->assertJsonEquals([
            'race' => [
                'id' => 71,
                'title' => 'Race Valid',
                'date' => '2023-08-20',
                "averageFinishMedium" => "03:10:47",
                "averageFinishLong" => "05:19:58"
            ],
            'message' => [
                'status' => 'Successfully imported the objects',
                'totalNumber' => 10,
                'invalidRows' => 'none',
            ]
        ]);
    }

    public function testCreateRaceWithInvalidRow(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/fixtures/import_invalid_row.csv',
            'import_invalid_row',
            'text/csv',
        );

        static::createClient()->request('POST', '/api/races', [
            'json' => [
                'title' => 'Race Invalid Row',
                'date' => '2023-08-20',
            ],
//            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $uploadedFile
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->assertJsonEquals([
            'race' => [
                'id' => 82,
                'title' => 'Race Invalid Row',
                'date' => '2023-08-20',
                "averageFinishMedium" => "03:10:47",
                "averageFinishLong" => "05:07:19"
            ],
            'message' => [
                'status' => 'Successfully imported the objects',
                'totalNumber' => 10,
                'invalidRows' => '7',
            ]
        ]);
    }
}
