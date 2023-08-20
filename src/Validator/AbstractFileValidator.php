<?php

declare(strict_types=1);

namespace App\Validator;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractFileValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
        protected Collection $rowConstraints,
        protected array $fileConstraints = [],
        protected array $requiredFields = []
    ) {
    }

    public function validateFile(File $file): void
    {
        $violations = $this->validator->validate($file, $this->fileConstraints);

        if ($violations->count() > 0) {
            $this->logger->error(
                'Invalid file',
                [
                    'errors' => $violations->get(0)->getMessage(),
                    'file' => $file->getFilename(),
                ]
            );

            throw new \InvalidArgumentException($violations->get(0)->getMessage());
        }
    }

    public function validateHeader(array $header): void
    {
        $diff = array_diff($this->requiredFields, $header);

        if (count($diff) > 0) {
            $this->logger->error(
                'Required fields missing',
                [
                    'requiredFields' => $this->requiredFields,
                    'header' => $header,
                ]
            );

            throw new \InvalidArgumentException(
                sprintf(
                    'The header must contain the following fields: %s',
                    implode(', ', $this->requiredFields)
                )
            );
        }
    }

    public function validateRow(array $row, int $rowNumber): bool
    {
        $violations = $this->validator->validate($row, $this->rowConstraints);
        $errors = [];

        if ($violations->count() > 0) {
            $errors[] = "{$violations->count()} violations on row {$rowNumber}";

            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            $this->logger->error(
                'Invalid row',
                [
                    'errors' => $errors,
                    'row' => $row,
                    'rowNumber' => $rowNumber,
                ]
            );
            return false;
        }

        return true;
    }
}
