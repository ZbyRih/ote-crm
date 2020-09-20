<?php

namespace App\Extensions\App\User;

use Nette\Security\Identity;

/**
 *
 * @property string $role
 * @property string $name
 * @property string $login
 * @property string $email
 * @property string $home
 * @property int $overUser
 * @property string $overRole
 * @property array $rPerms
 * @property array $uPerms
 * @property ActiveRow $row
 *
 */
class UserIdentity extends Identity{
}