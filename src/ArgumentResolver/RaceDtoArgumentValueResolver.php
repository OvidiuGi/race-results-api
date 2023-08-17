<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Dto\RaceDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

class RaceDtoArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return RaceDto::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = $request->getContent();

        yield $this->serializer->deserialize(
            data: $data,
            type: RaceDto::class,
            format: 'json'
        );
    }
}
