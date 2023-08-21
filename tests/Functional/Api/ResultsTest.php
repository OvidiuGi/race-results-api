<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\Factory\ResultFactory;
use App\DataFixtures\Story\DefaultResultStory;
use App\Entity\Result;
use App\Repository\ResultRepository;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ResultsTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    protected function setup(): void
    {
        DefaultResultStory::load();
    }

    public function testGetResultsByRace(): void
    {
        $raceId = ResultFactory::first()->getRace()->getId();

        static::createClient()->request('GET', sprintf('/api/races/%s/results', $raceId));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context'         => '/api/contexts/Result',
            '@id'              => sprintf('/api/races/%s/results', $raceId),
            '@type'            => 'hydra:Collection',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertMatchesResourceItemJsonSchema(Result::class);
    }

    public function testGetResultsByRaceNotFound(): void
    {
        static::createClient()->request('GET', sprintf('/api/races/9999/results'));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context'         => '/api/contexts/Result',
            '@id'              => '/api/races/9999/results',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 0,
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(Result::class);
    }

    public function testPatchResult(): void
    {
        $result = ResultFactory::first();

        self::bootKernel();
        $container = static::getContainer();
        $resultRepository = $container->get(ResultRepository::class);
        $resultRepository->calculateOverallPlacements($result->getRace());
        $resultRepository->calculateAgeCategoryPlacements($result->getRace());

        $result = $resultRepository->find($result->getId());

        self::ensureKernelShutdown();
        $response = static::createClient()->request('PATCH', sprintf('/api/results/%s', $result->getId()), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'finishTime' => '00:00:00',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(Result::class);

        $this->assertNotEquals($result->getFinishTime(), $response->toArray()['finishTime']);
        if ($response->toArray()['distance'] !== Result::DISTANCE_MEDIUM) {
            $this->assertGreaterThanOrEqual(
                $response->toArray()['overallPlacement'],
                $result->overallPlacement
            );
            $this->assertGreaterThanOrEqual(
                $response->toArray()['ageCategoryPlacement'],
                $result->ageCategoryPlacement
            );
        }
    }
}
