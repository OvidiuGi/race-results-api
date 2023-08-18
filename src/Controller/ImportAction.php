<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RaceDto;
use App\Handler\RaceResultsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ImportAction extends AbstractController
{
    public function __construct(private readonly RaceResultsHandler $raceResultsHandler)
    {
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/import', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] RaceDto $raceDto, Request $request): Response
    {
        $file = $request->files->get('file');

        try {
            return $this->raceResultsHandler->handle(['file' => $file, 'raceDto' => $raceDto]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
