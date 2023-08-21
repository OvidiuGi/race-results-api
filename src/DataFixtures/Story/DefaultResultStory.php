<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\ResultFactory;
use Zenstruck\Foundry\Story;

class DefaultResultStory extends Story
{
    public const COUNT = 300;

    public function build(): void
    {
        ResultFactory::createMany(self::COUNT);
    }
}