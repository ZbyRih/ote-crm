<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\IFactory;

interface ICreateABOPrikazCommand extends IFactory{

	/**
	 * @return CreateABOPrikazCommand
	 */
	public function create();
}

interface IDownloadRozpisZalohCommand extends IFactory{

	/**
	 * @return DownloadRozpisZalohCommand
	 */
	public function create();
}

interface IFakturaStornoCommand extends IFactory{

	/**
	 * @return FakturaStornoCommand
	 */
	public function create();
}

interface IFakturaOdeslanoCommand extends IFactory{

	/**
	 * @return FakturaOdeslanoCommand
	 */
	public function create();
}

interface IFakturaDeleteCommand extends IFactory{

	/**
	 * @return FakturaDeleteCommand
	 */
	public function create();
}

interface IFakturaRecreateCommand extends IFactory{

	/**
	 * @return FakturaRecreateCommand
	 */
	public function create();
}

interface IFakturaParPlatbaCommand extends IFactory{

	/**
	 * @return FakturaParPlatbaCommand
	 */
	public function create();
}

interface IGenerateZalohyCommand extends IFactory{

	/**
	 * @return GenerateZalohyCommand
	 */
	public function create();
}

interface IOteGP6DeleteCommnad extends IFactory{

	/**
	 * @return OteGP6DeleteCommand
	 */
	public function create();
}

interface IOteGP6UndeleteCommnad extends IFactory{

	/**
	 * @return OteGP6UndeleteCommand
	 */
	public function create();
}

interface IOteGP6FakturovatCommand extends IFactory{

	/**
	 * @return OteGP6FakturovatCommand
	 */
	public function create();
}

interface IBankaDownloadCommand extends IFactory{

	/**
	 * @return BankaDownloadCommand
	 */
	public function create();
}

interface IBankaTextUploadCommand extends IFactory{

	/**
	 * @return BankaTextUploadCommand
	 */
	public function create();
}

interface IBankaGPCUploadCommand extends IFactory{

	/**
	 * @return BankaGPCUploadCommand
	 */
	public function create();
}

interface IDownloadOteCommand extends IFactory{

	/**
	 * @return DownloadOTECommand
	 */
	public function create();
}

interface IChangedCertificateCommand extends IFactory{

	/**
	 * @return ChangedCertificateCommand
	 */
	public function create();
}

interface ILegacyInitCommand extends IFactory{

	/**
	 * @return LegacyInitCommand
	 */
	public function create();
}

interface IOteUnprocessedCommand extends IFactory{

	/**
	 * @return OteUnprocessedCommand
	 */
	public function create();
}

interface IOteUndecryptedCommand extends IFactory{

	/**
	 * @return OteUndecryptedCommand
	 */
	public function create();
}

interface IHelpDeleteCommand extends IFactory{

	/**
	 * @return HelpDeleteCommand
	 */
	public function create();
}

interface IDokladCreateCommand extends IFactory{

	/**
	 * @return DokladCreateCommand
	 */
	public function create();
}

interface IStatsLogCommand extends IFactory{

	/**
	 * @return StatsLogCommand
	 */
	public function create();
}

interface ITagDeleteCommand extends IFactory{

	/**
	 * @return TagDeleteCommand
	 */
	public function create();
}

interface IFakturaDownloadCommand extends IFactory{

	/**
	 * @return FakturaDownloadCommand
	 */
	public function create();
}

interface IOteGP6YearExportCommand extends IFactory{

	/**
	 * @return OteGP6YearExportCommand
	 */
	public function create();
}