<?php

namespace App\Models\Orm\KlientDetails;

use App\Extensions\Abstracts\TArrayAccessOrmEntity;
use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary-proxy}
 * @property int $klientDetailId {primary}
 * @property string|NULL $email
 * @property string|NULL $telnumber
 * @property string|NULL $tel2
 * @property string|NULL $firstname
 * @property string|NULL $lastname
 * @property string|NULL $title
 * @property string|NULL $ico
 * @property string|NULL $dico
 * @property string|NULL $firmName
 * @property string|NULL $korespondName
 * @property string|NULL $cu
 * @property \DateTime|NULL $birthDate
 * @property bool $kind {default 0}
 * @property string|NULL $organ
 * @property bool $zasilatMailem {default 0}
 * @property string|NULL $cisSmluv
 * @property \DateTime|NULL $platSmluvOd
 * @property int $datSplaDnu {default 14}
 */
class KlientDetailEntity extends Entity implements \ArrayAccess{
	use TArrayAccessOrmEntity;

	const KIND_FO = false;

	const KIND_PO = true;

	public function getSalutationKorespondence()
	{
		if($this->kind === self::KIND_PO && $this->korespondName){
			return trim($this->korespondName);
		}

		return $this->getSalutation();
	}

	public function getSalutation()
	{
		if($this->kind === self::KIND_PO){
			return trim($this->firmName);
		}

		if($this->kind === self::KIND_FO){
			return trim(implode(' ', [
				$this->title,
				$this->firstname,
				$this->lastname
			]));
		}
	}

	public function getIdentity()
	{
		if($this->kind === self::KIND_PO){
			return 'IČ: ' . $this->ico . ' DIČ: ' . $this->dico;
		}

		if($this->kind === self::KIND_FO){
			return 'Datum narození: ' . ($this->birthDate ? $this->birthDate->format('j.n Y') : 'neuveden');
		}
	}
}

// Field             Type                 Collation        Null    Key     Default  Extra           Privileges                       Comment
// ----------------  -------------------  ---------------  ------  ------  -------  --------------  -------------------------------  ---------
// klient_detail_id  bigint(20) unsigned  (NULL)           NO      PRI     (NULL)   auto_increment  select,insert,update,references
// email             varchar(255)         utf8_general_ci  YES             (NULL)                   select,insert,update,references
// telnumber         varchar(20)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// tel2              varchar(20)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// firstname         varchar(50)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// lastname          varchar(50)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// title             varchar(20)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// ico               varchar(14)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// dico              varchar(16)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// firm_name         varchar(100)         utf8_general_ci  YES             (NULL)                   select,insert,update,references
// korespond_name    varchar(100)         utf8_general_ci  YES             (NULL)                   select,insert,update,references
// description       mediumtext           utf8_general_ci  YES             (NULL)                   select,insert,update,references
// cu                varchar(30)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// birth_date        date                 (NULL)           YES             (NULL)                   select,insert,update,references
// kind              tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
// organ             varchar(100)         utf8_general_ci  YES             (NULL)                   select,insert,update,references
// zasilat_mailem    tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
// cis_smluv         varchar(20)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// plat_smluv_od     date                 (NULL)           YES             (NULL)                   select,insert,update,references
// dat_spla_dnu      int(5) unsigned      (NULL)           NO              14                       select,insert,update,references