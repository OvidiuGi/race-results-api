<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Result;
use App\Repository\ResultRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

use function Zenstruck\Foundry\lazy;

/**
 * @method        Result|Proxy create(array|callable $attributes = [])
 * @method static Result|Proxy createOne(array $attributes = [])
 * @method static Result|Proxy find(object|array|mixed $criteria)
 * @method static Result|Proxy findOrCreate(array $attributes)
 * @method static Result|Proxy first(string $sortedField = 'id')
 * @method static Result|Proxy last(string $sortedField = 'id')
 * @method static Result|Proxy random(array $attributes = [])
 * @method static Result|Proxy randomOrCreate(array $attributes = []))
 * @method static ResultRepository|RepositoryProxy repository()
 * @method static Result[]|Proxy[] all()
 * @method static Result[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Result[]&Proxy[] createSequence(iterable|callable $sequence)
 * @method static Result[]|Proxy[] findBy(array $attributes)
 * @method static Result[]|Proxy[] randomRange(int $min, int $max, array $attributes = []))
 * @method static Result[]|Proxy[] randomSet(int $number, array $attributes = []))
 */
class ResultFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return Result::class;
    }

    protected function getDefaults(): array
    {
        return [
            'fullName' => self::faker()->name(),
            'distance' => self::faker()->randomElement([Result::DISTANCE_LONG, Result::DISTANCE_MEDIUM]),
            'ageCategory' => self::faker()->randomElement(['M20-25', 'F20-25', 'M30-35', 'F30-35']),
            'finishTime' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'race' => lazy(fn() => RaceFactory::randomOrCreate())
        ];
    }
}
