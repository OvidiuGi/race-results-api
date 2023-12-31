<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RaceDto;
use App\Exception\DuplicateRaceException;
use App\Importer\RaceResultsImporter;
use App\Repository\RaceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
class ImportAction extends AbstractController
{
    public function __construct(
        private readonly RaceRepository $raceRepository,
        private readonly RaceResultsImporter $raceResultsImporter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(#[MapRequestPayload] RaceDto $raceDto, Request $request): Response
    {
        $file = $request->files->get('file');

        try {
            if ($file === null) {
                throw new FileNotFoundException(message: 'No file provided', code: Response::HTTP_BAD_REQUEST);
            }

            $race = $this->raceRepository->findOneBy([
                'title' => $raceDto->title,
                'date' => $raceDto->date,
            ]);

            if ($race !== null) {
                $this->logger->info('Duplicate Race import attempt', [
                    'title' => $race->title,
                    'date' => $race->getDate(),
                ]);

                throw new DuplicateRaceException(
                    title:$race->title,
                    date: $race->getDate(),
                    code: Response::HTTP_CONFLICT
                );
            }

            return $this->raceResultsImporter->import(['file' => $file, 'raceDto' => $raceDto]);
        } catch (\Exception $e) {
            return new Response(json_encode(['message' => $e->getMessage()]), $e->getCode());
        }
    }
}
