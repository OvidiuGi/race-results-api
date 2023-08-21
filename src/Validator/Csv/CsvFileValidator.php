<?php

namespace App\Validator\Csv;

use App\Entity\Result;
use App\Validator\AbstractFileValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CsvFileValidator extends AbstractFileValidator
{
    public function __construct(readonly ValidatorInterface $validator, readonly LoggerInterface $logger)
    {
        $this->rowConstraints = new Assert\Collection([
            'fullName' => new Assert\NotBlank(),
            'distance' => new Assert\Choice(options: Result::DISTANCES),
            'finishTime' => [
                new Assert\Regex(
                    pattern: '/^([0-9]|[0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',
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
                ],
                'mimeTypesMessage' => 'File Type Not Supported! Only CSV files are allowed',
            ]),
        ];

        $this->requiredFields = ['fullName', 'distance', 'finishTime', 'ageCategory'];

        parent::__construct(
            $validator,
            $logger,
            $this->rowConstraints,
            $this->fileConstraints,
            $this->requiredFields
        );
    }
}
