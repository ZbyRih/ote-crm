<?php

namespace App\Extensions\App;

use Nette;
use Nette\Application\IResponse;

final class StringResponse implements IResponse{

	/** @var string */
	private $content;

	/**
	 * Name of downloading file.
	 * @var string
	 */
	private $name;

	/**
	 * Content-Type of the contents.
	 * @var string
	 */
	private $contentType;

	/**
	 * Contents as http attachment?
	 * @var bool
	 */
	private $attachment = false;

	public function __construct(
		$content,
		$name,
		$contentType = 'text/plain')
	{
		$this->content = $content;
		$this->name = $name;
		$this->contentType = $contentType;
	}

	public function setAttachment(
		$attachment = true)

	{
		$this->attachment = $attachment;
		return $this;
	}

	public function send(
		Nette\Http\IRequest $httpRequest,
		Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->addHeader('Content-Type', $this->contentType);
		$httpResponse->addHeader('Content-Disposition', ($this->attachment ? 'attachment;' : '') . 'filename="' . $this->name . '"');
		$httpResponse->addHeader('Content-Length', strlen($this->content));
		echo $this->content;
	}
}