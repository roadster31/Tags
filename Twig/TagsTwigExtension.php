<?php

namespace Tags\Twig;

use Propel\Runtime\ActiveQuery\Criteria;
use Tags\Model\TagsQuery;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TagsTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('hasTag', [$this, 'hasTag']),
        ];
    }

    public function hasTag(string $tags, int $sourceId, string $source): bool
    {
        $tagArray = explode(',', $tags);

        array_walk($tagArray, function (&$value, $key) {
            $value = trim($value);
        });

        return TagsQuery::create()
                ->filterBySourceId($sourceId)
                ->filterBySource($source)
                ->filterByTag($tagArray, Criteria::IN)
                ->count() > 0
            ;
    }

}
