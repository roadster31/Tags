<?php

namespace Tags\Api\Extension;

use ApiPlatform\Metadata\Operation;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Tags\Model\TagsQuery;
use Tags\Tags;
use Thelia\Api\Bridge\Propel\Extension\QueryCollectionExtensionInterface;

class TagsFilter implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        ModelCriteria $query,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!isset(Tags::SUPPORTED_RESOURCES_SOURCES[$resourceClass])) {
            return;
        }

        if (!$this->isSupportedOperation($operation)) {
            return;
        }

        $filters = $context['filters'] ?? [];

        $tags = $this->normalizeTags($filters['tag'] ?? null);
        $excludedTags = $this->normalizeTags($filters['exclude_tag'] ?? null);

        if ([] === $tags && [] === $excludedTags) {
            return;
        }

        $source = Tags::SUPPORTED_RESOURCES_SOURCES[$resourceClass];

        if ([] !== $tags) {
            $this->applyTagFilter($query, $source, $tags);
        }

        if ([] !== $excludedTags) {
            $this->applyExcludeTagFilter($query, $source, $excludedTags);
        }

        if (\is_callable([$query, 'groupById'])) {
            $query->groupById();
        }
    }

    private function isSupportedOperation(?Operation $operation): bool
    {
        return \in_array($operation?->getUriTemplate(), array_values(Tags::SUPPORTED_RESOURCES_URI), true);
    }

    private function applyTagFilter(ModelCriteria $query, string $source, array $tags): void
    {
        $sourceIds = TagsQuery::create()
            ->filterBySource($source)
            ->filterByTag($tags, Criteria::IN)
            ->select('SourceId')
            ->find()
            ->toArray();

        if ([] === $sourceIds) {
            $query->filterById([0], Criteria::IN);

            return;
        }

        $query->filterById(array_unique($sourceIds), Criteria::IN);
    }

    private function applyExcludeTagFilter(ModelCriteria $query, string $source, array $excludedTags): void
    {
        $sourceIds = TagsQuery::create()
            ->filterBySource($source)
            ->filterByTag($excludedTags, Criteria::IN)
            ->select('SourceId')
            ->find()
            ->toArray();

        if ([] === $sourceIds) {
            return;
        }

        $query->filterById(array_unique($sourceIds), Criteria::NOT_IN);
    }

    private function normalizeTags(mixed $value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (\is_array($value)) {
            $values = $value;
        } else {
            $values = explode(',', (string) $value);
        }

        $values = array_map(
            static fn (mixed $tag): string => trim((string) $tag),
            $values
        );

        return array_values(array_filter(
            array_unique($values),
            static fn (string $tag): bool => '' !== $tag
        ));
    }
}
