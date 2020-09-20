<?php
namespace App\Extensions\App\User\Mails;

class ForgotPassword extends BaseMail{

	public function send($token, $name, $email, $presenter){
		$t = $this->createTemplate('forgotPassword.latte', $presenter);
		$t->token = $token;
		$t->name = $name;

		$this->sendMail($t, $email, $name, 'app.mail.forgot-subject');
	}
}