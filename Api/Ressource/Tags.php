<?php

namespace Tags\Api\Ressource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Tags\Api\State\TagsProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/tags',
            provider: TagsProvider::class,
        )
    ],
)]
class Tags
{
}
