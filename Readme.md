# Tags

Ce module permet d'ajouter un ou plusieurs tags à vos produits, catégories, contenus, dossiers et marques.
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

## Extension des boucles standard

Vous évitez ainsi de mettre des ID en dur dans votre code. C'est l'une des utilisations possible de ces tags, mais il
en existe surement bien d'autres.

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
- `partial` : la boucle recherche les tags qui sont contiennent tout ou partie des tags demandés, par exemple si le tag 'rou' est demandé, la boucle remontera les objets possédant les tags 'rouge', 'rouge' ou 'trou'


## Back-office

Le module ajoute un entrée 'Tags' dans le menu Outils du back-office, qui vous donne accès à la liste de tous les tags
définis sur les produits, catégories, contenus et dossiers.

## La boucle tags

### Paramètres

|Argument |Description |
|---      |--- |
|**id** | Retourne le tag ayant cet ID |
|**source** | Source des objets associés. Les valeurs possibles sont `product`, `category`, `content`, `folder`, `brand` |
|**exclude_source** | Source des objets associés à exclure des résultats. Les valeurs possibles sont `product`, `category`, `content`, `folder`, `brand` |
|**source_id** | Identifiants des objets associés |
|**exclude_source_id** | Identifiants des objets associés à exclure des résultats |
|**tag** | Tags à rechercher |
|**exclude_tag** | Tags à exclure des résultats |
|**tag_match_mode** | Mode de comparaison des tags. Peut prendre les valeurs suivantes:<ul><li>- `exact` (par défaut) : la boucle recherche les tags qui sont exactement identiques aux tags demandés</li><li>- `partial` : la boucle recherche les tags qui sont contiennent tout ou partie des tags demandés, par exemple si le tag 'rou' est demandé, la boucle remontera les objets possédant les tags 'rouge', 'rouge' ou 'trou'</li></ul>|
|**order** | Classement des résulats. Les valeurs possibles sont : `id`, `id-reverse`, `alpha`, `alpha-reverse`, `source`, `source-reverse`, `source-id`, `source-id-reverse`, `random`|

### Variables retournées

|Variable   |Description |
|---        |--- |
|ID    | Identifiant du tag |
|SOURCE | le type d'objet auquel ce tag est associé. Les valeurs possible sont `product`, `category`, `content`, `folder` ou `brand` |
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

