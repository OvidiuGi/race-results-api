<?php

namespace App\Tests\Unit\Api;

use App\Entity\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $array = [
            'fullName' => 'John Doe',
            'distance' => '10km',
            'finishTime' => '01:01:01',
            'ageCategory' => 'M',
        ];

        $result = Result::createFromArray($array);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($array['fullName'], $result->fullName);
        $this->assertEquals($array['distance'], $result->distance);
        $this->assertEquals($array['finishTime'], $result->getFinishTime()->format('H:i:s'));
        $this->assertEquals($array['ageCategory'], $result->ageCategory);
    }
}
