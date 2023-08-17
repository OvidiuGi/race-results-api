<?php

namespace App\Validator\Csv;

use App\Entity\Result;
use App\Validator\AbstractFileValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CsvFileValidator extends AbstractFileValidator
{
    public function __construct(
        readonly ValidatorInterface $validator
    ) {
        $this->rowConstraints = new Assert\Collection([
            'fullName' => new Assert\NotBlank(),
            'distance' => new Assert\Choice(options: Result::DISTANCES),
            'finishTime' => [
                new Assert\Type(
                    \DateTimeImmutable::class,
                    message: 'This value should be written in this format: HH:MM:SS'
                ),
                new Assert\NotBlank(),
            ],
            'ageCategory' => new Assert\NotBlank(),
        ]);

        $this->fileConstraints = [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'text/csv',
                    'text/plain',
                ],
                'mimeTypesMessage' => 'Please upload a valid CSV document',
            ]),
        ];

        $this->requiredFields = ['fullName', 'distance', 'finishTime', 'ageCategory'];

        parent::__construct($validator, $this->rowConstraints, $this->fileConstraints, $this->requiredFields);
    }
}
