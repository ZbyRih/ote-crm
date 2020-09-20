<?php

namespace App\Models\Orm\OteMessages;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property bool $decrypted
 * @property bool $processed
 * @property \DateTime|NULL $received
 * @property string $msgUid
 * @property string $fileEml
 * @property string $fileXml
 * @property string $oteKod
 * @property string $oteId
 * @property string $subject
 */
class OteMessageEntity extends Entity{
}