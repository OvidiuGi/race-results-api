<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private readonly OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $paths = $openApi->getPaths();

        $paths->addPath(
            '/import',
            (new PathItem())
                ->withPost(
                    (new OpenApiOperation())
                        ->withOperationId('api_import_results')
                        ->withTags(['Import'])
                        ->withRequestBody(
                            (new RequestBody())
                                ->withContent(
                                    new \ArrayObject([
                                        'multipart/form-data' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'title' => [
                                                        'type' => 'string',
                                                        'example' => 'Race 1'
                                                    ],
                                                    'date' => [
                                                        'type' => 'string',
                                                        'format' => 'date-time',
                                                        'example' => '2021-01-01T00:00:00+00:00'
                                                    ],
                                                    'file' => [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                        'description' => 'Upload the CSV file',
                                                        'example' => 'import.csv'
                                                    ],
                                                ],
                                                'required' => [
                                                    'title',
                                                    'date',
                                                    'file'
                                                ],
                                            ],
                                        ],
                                    ])
                                )
                        )
                        ->withResponse(
                            Response::HTTP_OK,
                            (new OpenApiResponse())
                                ->withContent(new \ArrayObject([
                                    'application/ld+json' => [

                                    ],
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'string',
                                            'example' => 'Imported 100 results',
                                        ],
                                    ],
                                ]))
                        )
                        ->withSummary('Imports race results from a CSV file.')
                )
        );

        return $openApi;
    }
}
