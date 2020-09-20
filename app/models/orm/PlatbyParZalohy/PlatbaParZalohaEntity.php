<?php

namespace App\Models\Orm\PlatbyParZalohy;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property int|NULL $platbaId
 * @property int|NULL $pohybId
 * @property int|NULL $fakturaId
 * @property int|NULL $zalohaId
 * @property float|NULL $suma {default 0.0}
 * @property \DateTime|NULL $dne
 */
class PlatbaParZalohaEntity extends Entity{
}

// id          int(20) unsigned  (NULL)     NO      PRI     (NULL)   auto_increment  select,insert,update,references
// platba_id   int(10) unsigned  (NULL)     YES     MUL     (NULL)                   select,insert,update,references
// pohyb_id    int(10) unsigned  (NULL)     YES     MUL     (NULL)                   select,insert,update,references
// faktura_id  int(10) unsigned  (NULL)     YES     MUL     (NULL)                   select,insert,update,references
// zaloha_id   int(10) unsigned  (NULL)     YES     MUL     (NULL)                   select,insert,update,references
// suma        float(14,2)       (NULL)     YES             (NULL)                   select,insert,update,references
// dne         date              (NULL)     YES             (NULL)                   select,insert,update,references