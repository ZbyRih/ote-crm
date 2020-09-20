<?php

require_once 'bootstrap.php';

// require_once 'mocks/platby-spojeni-mock.php';

OBE_Cli::writeBr('');
OBE_Cli::writeBr('	begin ->>');
OBE_Cli::writeBr('');

OBE_App::$db->startTransaction();

(new PlatbyParZalohy('2018'))->load()->projit()->getResultList();
(new PlatbyParZalohy('2018'))->load()->projit()->getMidResult();

OBE_Cli::writeBr('	<<- end');

OBE_App::$db->finishTransaction(false);