<?php

namespace App\Models\Orm\TagsToObjects;

use Nextras\Orm\Entity\Entity;
use App\Models\Orm\Tags\TagEntity;

/**
 *

 * @property int $id {primary}
 * @property TagEntity $tagId {m:1 TagEntity::$objects}
 * @property string $type
 * @property int $oId
 */
class TagToObjectEntity extends Entity{
}