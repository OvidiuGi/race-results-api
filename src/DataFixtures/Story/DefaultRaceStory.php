<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\RaceFactory;
use Zenstruck\Foundry\Story;

class DefaultRaceStory extends Story
{
    public function build(): void
    {
        RaceFactory::createMany(10);
    }
}
