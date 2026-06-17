<?php

namespace Tags\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Tags\Model\TagsQuery;

readonly class TagsProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $tags = TagsQuery::create()
            ->orderByTag()
            ->find();

        $availableTags = [];

        foreach ($tags as $tag) {
            $tagValue = trim((string) $tag->getTag());

            if ('' === $tagValue) {
                continue;
            }

            if (!isset($availableTags[$tagValue])) {
                $availableTags[$tagValue] = [
                    'tag' => $tagValue,
                    'count' => 0,
                ];
            }

            $availableTags[$tagValue]['count']++;
        }

        return array_values($availableTags);
    }
}
