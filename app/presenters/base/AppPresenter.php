<?php

namespace App\Presenters;

use App\Models\Repositories\ParametersRepository;
use App\Models\Repositories\SettingsRepository;
use Kdyby\Translation\Translator;
use Nette\Bridges\ApplicationLatte\Template;

class AppPresenter extends CorePresenter{

	/** @var string @persistent */
	public $locale;

	/** @var Translator @inject */
	public $translator;

	/** @var SettingsRepository @inject */
	public $settings;

	/** @var ParametersRepository @inject */
	public $options;

	protected function startup()
	{
		parent::startup();
		$this->translator->setLocale('cs_CZ');
	}

	public function checkRequirements(
		$element)
	{
		$this->getUser()
			->getStorage()
			->setNamespace($this->options->namespace);
		parent::checkRequirements($element);
	}

	public function translate(
		$message,
		array $parameters = [],
		$domain = NULL,
		$locale = NULL)
	{
		return $this->translator->trans($message, $parameters, $domain, $locale);
	}

	/**
	 *
	 * @return Template
	 */
	public function createTemplate()
	{
		$t = parent::createTemplate();
		$t->setTranslator($this->translator);

		$t->setParameters(
			[
				'name' => $this->getResource(),
				'params' => $this->options,
				'settings' => $this->settings,
				'basePath' => $this->options->basePath,
				'backlink' => $this->storeRequest(),
				'subfolder' => '',
				'userColision' => false
			]);

		return $t;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Application\UI\Presenter::formatLayoutTemplateFiles()
	 */
	public function formatLayoutTemplateFiles()
	{
		$list = parent::formatLayoutTemplateFiles();
		$layout = $this->layout ? $this->layout : 'layout';
		$list[] = APP_DIR . '/templates/@' . $layout . '.latte';
		return $list;
	}
}