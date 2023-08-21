<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Race;
use App\Repository\RaceRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method        Race|Proxy create(array|callable $attributes = [])
 * @method static Race|Proxy createOne(array $attributes = [])
 * @method static Race|Proxy find(object|array|mixed $criteria)
 * @method static Race|Proxy findOrCreate(array $attributes)
 * @method static Race|Proxy first(string $sortedField = 'id')
 * @method static Race|Proxy last(string $sortedField = 'id')
 * @method static Race|Proxy random(array $attributes = [])
 * @method static Race|Proxy randomOrCreate(array $attributes = []))
 * @method static RaceRepository|RepositoryProxy repository()
 * @method static Race[]|Proxy[] all()
 * @method static Race[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Race[]&Proxy[] createSequence(iterable|callable $sequence)
 * @method static Race[]|Proxy[] findBy(array $attributes)
 * @method static Race[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 * @method static Race[]|Proxy[] randomSet(int $number, array $attributes = []))
 */
class RaceFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return Race::class;
    }

    protected function getDefaults(): array
    {
        return [
            'title' => self::faker()->sentence(),
            'date' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime())
        ];
    }
}
