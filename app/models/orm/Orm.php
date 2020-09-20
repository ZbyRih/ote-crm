<?php

namespace App\Models\Orm;

use App\Models\Orm\Address\AddressRepository;
use App\Models\Orm\Cert\CertsRepository;
use App\Models\Orm\FakSkups\FakSkupsRepository;
use App\Models\Orm\Faktury\FakturyRepository;
use App\Models\Orm\Helps\HelpsRepository;
use App\Models\Orm\Info\InfoRepository;
use App\Models\Orm\KlientDetails\KlientDetailsRepository;
use App\Models\Orm\Klients\KlientsRepository;
use App\Models\Orm\OdberMists\OdberMistRepository;
use App\Models\Orm\OteGP6Body\OteGP6BodyRepository;
use App\Models\Orm\OteGP6Head\OteGP6HeadRepository;
use App\Models\Orm\OteMessages\OteMessagesRepository;
use App\Models\Orm\Platby\PlatbyRepository;
use App\Models\Orm\PlatbyParZalohy\PlatbyParZalohyRepository;
use App\Models\Orm\PlatbyZarazeni\PlatbyZarazeniRepository;
use App\Models\Orm\Settings\SettingsRepository;
use App\Models\Orm\SmlOMs\SmlOMsRepository;
use App\Models\Orm\SmlOMFlags\SmlOMFlagsRepository;
use App\Models\Orm\Users\UsersRepository;
use App\Models\Orm\Zalohy\ZalohyRepository;
use App\Models\Orm\Tags\TagsRepository;
use App\Models\Orm\TagsToObjects\TagsToObjectsRepository;
use App\Models\Orm\Doklad\DokladyRepository;
use App\Models\Orm\Roles\RolesRepository;

/**
 * @property-read AddressRepository $address
 * @property-read CertsRepository $certs
 * @property-read DokladyRepository $doklady
 * @property-read FakSkupsRepository $fakSkups
 * @property-read FakturyRepository $faktury
 * @property-read HelpsRepository $helps
 * @property-read InfoRepository $info
 * @property-read KlientsRepository $klients
 * @property-read KlientDetailsRepository $klientDetails
 * @property-read OdberMistRepository $odberMist
 * @property-read OteGP6HeadRepository $oteGP6Head
 * @property-read OteGP6BodyRepository $oteGP6Body
 * @property-read OteMessagesRepository $oteMessages
 * @property-read PlatbyRepository $platby
 * @property-read PlatbyZarazeniRepository $platbyZarazeni
 * @property-read PlatbyParZalohyRepository $ppz
 * @property-read RolesRepository $roles
 * @property-read SettingsRepository $settings
 * @property-read SmlOMsRepository $smlOm
 * @property-read SmlOMFlagsRepository $smlOmFlags
 * @property-read TagsRepository $tags
 * @property-read TagsToObjectsRepository $tagsToObjects
 * @property-read UsersRepository $users
 * @property-read ZalohyRepository $zalohy
 */
class Orm extends \Nextras\Orm\Model\Model{
}