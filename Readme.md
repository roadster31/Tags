==en_US==

# Tags

This module allows to add one or multiple tags to your products, categories, contents, folders, images and documents.
You can find easily the object associated with a tag using the loop of this module.

For example, instead of using in your templates : 

```
{loop type="content" name="my-specific-content" id=12} ... {/loop}
```

You can tagged the content that has the ID 12 with 'my-special-content' and use :

```
{loop type="tags" tag="my-special-content"}
    {loop type="content" name="my-specific-content" id=$SOURCE_ID} ... {/loop}
{/loop}    
```
This way you avoid putting hard IDs in your code. This is one of the possible uses of these tags, but it there are surely many more.

A Smarty extension allows you to determine if an object has a tag in a template :

```
{if {has_tag id=12 source='content' tag="my-special-content"}}
    The content ID=12 has the "my-special-content" tag}
{/if}
``` 

## Standard loops extension

Since the version 1.1 of the module, you can directly use the parameter tag in the loops `product`, `content`, `folder`, `category` and `brand`. That's one request saved !

```
    {loop type="content" name="my-specific-content" tag='my-special-content'} ... {/loop}
```

The parameter tag can take one or multiple values, so you can find the objects designed with several tags: 

```
    {loop type="product" name="my-specific-product" tag='my-tag-1,mytag-2,...'} ... {/loop}
```

You can specify the type of comparison that will be performed on the tags with the parameter `tag_match_mode`, which can take the following values:
- `exact`(by default) : the loop search for the tags which are exactly identical to the tags requested.
- `partial` : the loop look for the objects which contained all or a part of the tags requested, for example if the tag 'rou' is asked, the loop will find the objects with the tags 'rou', 'rough' or 'trouble'.

## Back-office

The module adds an entry 'Tags' in the Tools menu in back office, which give you access to the list of all the tags defined on the products, categories, contents, folders, images and documents. 

## The loop tags

### Parameters

|Argument |Description |
|---      |--- |
|**id** | Return tag with this ID |
|**source** | Source of associated objects. The possibles values are `product`, `category`, `content`, `folder`, `brand`, `product_image`, `product_document`, `category_image`, `category_document`, `content_image`, `content_document`, `folder_image`, `folder_document`, `brand_image`, `brand_document`. Giving a value missing from this list will not cause an error. |
|**exclude_source** | Source of associated objects to exclude from the result. See `source` for possible values. |
|**source_id** | ID of associated objects |
|**exclude_source_id** | ID of associated objects to exclude from the result |
|**tag** | Tags to search |
|**exclude_tag** | Tags to exclude from the result |
|**tag_match_mode** | Comparison mod of tags. Can take the following values:<ul><li> `exact` (by default) : the loop look for the tags which are exactly identical to the tags selected</li><li> `partial` : the loop look for the objects which contained all or a part of the tags asked, for example if the tag 'rou' is asked, the loop will find the objects with the tags 'rou', 'rough' or 'trouble'</li></ul>|
|**order** | Ordering the result. The possible values are : `id`, `id-reverse`, `alpha`, `alpha-reverse`, `source`, `source-reverse`, `source-id`, `source-id-reverse`, `random`|

### Output variables

|Variable   |Description |
|---        |--- |
|ID    | ID of the tag |
|SOURCE | The type of objects which this tag is associated. The possible values are `product`, `category`, `content`, `folder`, `brand`, `product_image`, `product_document`, `category_image`, `category_document`, `content_image`, `content_document`, `folder_image`, `folder_document`, `brand_image` or `brand_document` |
|SOURCE_ID | ID of the source object |
|TAG    | Value of the tag, as it has been set in back-office  |
|CREATED_AT| Creation date of the tag  |
|UPDATED_AT| Date of the last update of the tag |

### Exemple

To get the content with the type `my-special-content` :

```
{loop type="tags" tag="my-special-content"}
    {loop type="content" name="my-specific-content" id=$SOURCE_ID} ... {/loop}
{/loop}    
```

## The Smarty extension `has_tag`

This Smlarty extension return `true` if an object has a specifioed tag, or `false` otherwise.

```
{has_tag id=12 source='product' tag='a_tag,another_tag'}
```

|Argument |Description |
|---      |--- |
|**id** | ID of the source object (ex. a product ID) |
|**source** | The type of the source object. The possible values are `product`, `category`, `content`, `folder`, `brand`, `product_image`, `product_document`, `category_image`, `category_document`, `content_image`, `content_document`, `folder_image`, `folder_document`, `brand_image` or `brand_document` |
|**tag** | Tag values, at least one, separated by commas |

==fr_FR==

# Tags

Ce module permet d'ajouter un ou plusieurs tags à vos produits, catégories, contenus, dossiers, marques, images et documents.
Vous pouvez alors retrouver facilement l'objet associé à un tag à l'aide de la boucle du module.

Typiquement, au lieu d'utiliser dans vos templates :

```
{loop type="content" name="my-specific-content" id=12} ... {/loop}
```

vous pouvez tagger le contenu qui a l'ID 12 avec 'mon-contenu-special' et utiliser :

```
{loop type="tags" tag="mon-contenu-special"}
    {loop type="content" name="my-specific-content" id=$SOURCE_ID} ... {/loop}
{/loop}    
```

Vous évitez ainsi de mettre des ID en dur dans votre code. C'est l'une des utilisations possible de ces tags, mais il
en existe surement bien d'autres.

Une extension Smarty permet de déterminer si un objet possède un tag :

```
{if {has_tag id=12 source='content' tag="mon-contenu-special"}}
    Le contenu ID=12 possède le tag "mon-contenu-special"}
{/if}
``` 

## Extension des boucles standard

À partir de la version 1.1 du module, vous pouvez utiliser directement le paramètre `tag` dans les boucles `product`, 
`content`, `folder`, `category` et `brand`. C'est une requête d'économisée !

```
    {loop type="content" name="my-specific-content" tag='mon-contenu-special'} ... {/loop}
```

Le paramètre tag peut prendre un ou plusieurs valeurs, vous pouvez donc remonter les objets désignés par plusieurs tags :

```
    {loop type="product" name="my-specific-product" tag='mon-tag-1,montag-2,...'} ... {/loop}
```

Vous pouvez indiquer le type de comparaison qui sera effectuée sur les tags avec le paramètre
`tag_match_mode`, qui peut prendre les valeurs suivantes :
- `exact` (par défaut) : la boucle recherche les tags qui sont exactement identiques aux tags demandés
- `partial` : la boucle recherche les objets qui contiennent tout ou une partie des tags demandés, par exemple si le tag 'rou' est demandé, la boucle remontera les objets possédant les tags 'rou', 'rouge' ou 'trou'


## Back-office

Le module ajoute un entrée 'Tags' dans le menu Outils du back-office, qui vous donne accès à la liste de tous les tags
définis sur les produits, catégories, contenus, dossiers, images et documents.

## La boucle tags

### Paramètres

|Argument |Description |
|---      |--- |
|**id** | Retourne le tag ayant cet ID |
|**source** | Source des objets associés. Les valeurs possibles ne sont pas limitées, mais doivent permettre de construire une Query propel à partir du nom. Exemple : 'product' => ProductQuery, 'product_image' => ProductImageQuery. Les valeurs supportées par defaut sont `product`, `category`, `content`, `folder`, `brand`, `product_image`, `product_document`, `category_image`, `category_document`, `content_image`, `content_document`, `folder_image`, `folder_document`, `brand_image`, `brand_document`. Une valeur absente de cette liste ne provoquera pas d'erreur.  |
|**exclude_source** | Source des objets associés à exclure des résultats. Voir le paramètre `source` pour les valeurs possibles. |
|**source_id** | Identifiants des objets associés |
|**exclude_source_id** | Identifiants des objets associés à exclure des résultats |
|**tag** | Tags à rechercher |
|**exclude_tag** | Tags à exclure des résultats |
|**tag_match_mode** | Mode de comparaison des tags. Peut prendre les valeurs suivantes:<ul><li> `exact` (par défaut) : la boucle recherche les tags qui sont exactement identiques aux tags demandés</li><li> `partial` : la boucle recherche les objets qui contiennent tout ou une partie des tags demandés, par exemple si le tag 'rou' est demandé, la boucle remontera les objets possédant les tags 'rou', 'rouge' ou 'trou'</li></ul>|
|**order** | Classement des résulats. Les valeurs possibles sont : `id`, `id-reverse`, `alpha`, `alpha-reverse`, `source`, `source-reverse`, `source-id`, `source-id-reverse`, `random`|

### Variables retournées

|Variable   |Description |
|---        |--- |
|ID    | Identifiant du tag |
|SOURCE | le type d'objet auquel ce tag est associé.
|SOURCE_ID | Identifiant de l'objet source |
|TAG    | valeur du tag, telle qu'elle a été indiquée dans le back-office  |
|CREATED_AT| Date de creation du tag  |
|UPDATED_AT| Date de dernière mise à jour du tag  |

### Exemple

Pour remonter le contenu ayant le tag `mon-contenu-special` :

```
{loop type="tags" tag="mon-contenu-special"}
    {loop type="content" name="my-specific-content" id=$SOURCE_ID} ... {/loop}
{/loop}    
```

## L'extension Smarty `has_tag`

Cette extension Smarty permet de déterminet si un objet possède un des tags demandés. Elle retourne `true` si c'est
le cas, `false sinon.

```
{has_tag id=12 source='product' tag='un_tag,un_autre_tag'}
```

|Argument |Description |
|---      |--- |
|**id** | Identifiant de l'objet concerné (ex. ID d'un produit) |
|**source** | Source de l'objet concerné. Les valeurs possibles sont `product`, `category`, `content`, `folder`, `brand`, `product_image`, `product_document`, `category_image`, `category_document`, `content_image`, `content_document`, `folder_image`, `folder_document`, `brand_image`, `brand_document` |
|**tag** | Tags à rechercher, au moins un, séparés par des virgules |
