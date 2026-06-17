<?php

namespace Tags\Api\Metadata;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use Tags\Tags;

readonly class TagsResourceMetadataCollectionFactory implements ResourceMetadataCollectionFactoryInterface
{
    public function __construct(
        private ResourceMetadataCollectionFactoryInterface $decorated
    ) {
    }

    public function create(string $resourceClass): ResourceMetadataCollection
    {

        $collection = $this->decorated->create($resourceClass);

        foreach ($collection as $resourceKey => $resource) {
            if (!$resource instanceof ApiResource) {
                continue;
            }

            $operations = $resource->getOperations();

            if (null === $operations) {
                continue;
            }

            foreach ($operations as $operationName => $operation) {
                if (!$operation instanceof GetCollection) {
                    continue;
                }

                if (!in_array($operation->getUriTemplate(), array_values(Tags::SUPPORTED_RESOURCES_URI), true)) {
                    continue;
                }

                $parameters = $operation->getParameters();

                if (!$parameters instanceof Parameters) {
                    $parameters = new Parameters();
                }

                $parameters->add(
                    'tag',
                    new QueryParameter(
                        key: 'tag',
                        schema: [
                            'type' => 'string',
                            'example' => 'tag-1,tag-2',
                        ],
                        property: 'tag',
                        description: 'Filtre les résultats possédant un ou plusieurs tags. Valeurs séparées par des virgules.',
                        required: false
                    )
                );

                $parameters->add(
                    'exclude_tag',
                    new QueryParameter(
                        key: 'exclude_tag',
                        schema: [
                            'type' => 'string',
                            'example' => 'tag-1,tag-2',
                        ],
                        property: 'exclude_tag',
                        description: 'Exclut les résultats possédant un ou plusieurs tags. Valeurs séparées par des virgules.',
                        required: false
                    )
                );

                $operations->add(
                    $operationName,
                    $operation->withParameters($parameters)
                );
            }

            $collection[$resourceKey] = $resource->withOperations($operations);
        }

        return $collection;
    }
}
