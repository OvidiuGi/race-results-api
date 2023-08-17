<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractFileValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        protected Collection $rowConstraints,
        protected array $fileConstraints = [],
        protected array $requiredFields = []
    ) {
    }

    public function validateFile(File $file): void
    {
        $violations = $this->validator->validate($file, $this->fileConstraints);

        if ($violations->count() > 0) {
            throw new \InvalidArgumentException($violations->get(0)->getMessage());
        }
    }

    public function validateHeader(array $header): void
    {
        $diff = array_diff($this->requiredFields, $header);

        if (count($diff) > 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The header must contain the following fields: %s',
                    implode(', ', $this->requiredFields)
                )
            );
        }
    }

    public function validateRow(array $row, int $rowNumber): void
    {
        $violations = $this->validator->validate($row, $this->rowConstraints);
        $errors = [];

        if ($violations->count() > 0) {
            $errors[] = "{$violations->count()} violations on row {$rowNumber}";

            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            throw new \InvalidArgumentException(implode(PHP_EOL, $errors));
        }
    }
}
