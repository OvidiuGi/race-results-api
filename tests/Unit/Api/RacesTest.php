<?php

namespace App\Tests\Unit\Api;

use App\Dto\RaceDto;
use App\Entity\Race;
use Monolog\Test\TestCase;

class RacesTest extends TestCase
{
    public function testCreateFromDto(): void
    {
        $dto = new RaceDto('Race 1', new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));

        $race = Race::createFromDto($dto);

        $this->assertInstanceOf(Race::class, $race);
        $this->assertEquals($dto->title, $race->title);
        $this->assertEquals($dto->date, $race->getDate());
    }
}
