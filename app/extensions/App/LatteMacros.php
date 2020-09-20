<?php

namespace App\Extensions\App;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;
use Latte\MacroTokens;

class LatteMacros extends MacroSet{

	public static function install(
		Compiler $compiler)
	{
		$set = new static($compiler);

		$set->addMacro('portlet', [
			$set,
			'portletBegin'
		], [
			$set,
			'portletEnd'
		]);

		$set->addMacro('portlet_break', [
			$set,
			'portletBreak'
		]);

		$set->addMacro('portlet_end', [
			$set,
			'portletEnd'
		]);

		$set->addMacro('caption_title', [
			$set,
			'captionTitle'
		]);

		$set->addMacro('flashes', [
			$set,
			'flashes'
		]);

		$set->addMacro('wpm_script', [
			$set,
			'wpmScript'
		]);

		$set->addMacro('wpm_image', [
			$set,
			'wpmImage'
		]);

		$set->addMacro('wpm_imageBase64', [
			$set,
			'wpmImageBase64'
		]);

		$set->addMacro('wpm_file', [
			$set,
			'wpmFile'
		]);

		return $set;
	}

	public function portletBreak(
		MacroNode $node,
		PhpWriter $writer)
	{
		$node->openingCode = '</div></div>';
		$this->portletBegin($node, $writer);
	}

	public function portletBegin(
		MacroNode $node,
		PhpWriter $writer)
	{
		$class = '';
		if(!empty($node->args)){
			$rest = '';
			$t = new MacroTokens($node->args);
// 			$t->nextUntil('class');
			$rest = $t->joinUntil('class');
			if($t->isNext() && $t->nextValue() == 'class'){
				$t->nextUntil(MacroTokens::T_STRING);
				$class = trim($t->expectNextValue(), '\'"');
				$rest .= $t->joinAll();
				$node->args = $rest;
			}
		}

		$node->openingCode .= '<div class="portlet light bordered ' . $class . '">' . PHP_EOL;

		if(!empty($node->args)){

			$node->openingCode .= '<div class="portlet-title">';
			$this->captionTitle($node, $writer);
			$node->openingCode .= '</div>';
		}

// 		$node->openingCode .= '<div class="portlet-body form clearfix">' . PHP_EOL;
		$node->openingCode .= '<div class="portlet-body clearfix">' . PHP_EOL;
	}

	public function portletEnd(
		MacroNode $node,
		PhpWriter $writer)
	{
		$node->closingCode = '</div></div>';
	}

	public function captionTitle(
		MacroNode $node,
		PhpWriter $writer)
	{
		$node->openingCode .= '<div class="caption">' . PHP_EOL . '	<span class="caption-subject font-green bold uppercase">' . PHP_EOL;
		if($node->modifiers){
			$node->openingCode .= '<?php ' . $writer->write('echo %modify(%node.args);') . '?>';
		}else{
			$node->openingCode .= '<?php ' . $writer->write('echo %escape(%node.args);') . '?>';
		}
		$node->openingCode .= '</span>' . PHP_EOL . '</div>';
	}

	public function flashes(
		MacroNode $node,
		PhpWriter $writer)
	{
		$node->openingCode .= '<?php $flashesUuid = \Nette\Utils\Random::generate(10); ?>' . PHP_EOL;
		$node->openingCode .= '<div class="row" id="<?php echo $flashesUuid ?>">' . PHP_EOL;
		$node->openingCode .= '	<div class="col-md-12 flashes">' . PHP_EOL;
		$node->openingCode .= '		<script>' . PHP_EOL;
		$node->openingCode .= '		(function(){
										var p = document.getElementById(\'<?php echo $flashesUuid ?>\');
										p.style.display = \'none\';
										p.style.visibility = \'hidden\';
									}).call();' . PHP_EOL;
		$node->openingCode .= '		</script>' . PHP_EOL;
		$node->openingCode .= '<?php ' . $writer->write('foreach(%node.word as $f){') . ' ?>' . PHP_EOL;
		$node->openingCode .= '		<div n:foreach="$flashes as $flash" class="alert alert-<?php ' . $writer->write('echo $f->type;') . '?>">' . PHP_EOL;
		$node->openingCode .= '<?php echo ' . $writer->write('$f->message;') . ' ?>' . PHP_EOL;
		$node->openingCode .= '		</div>' . PHP_EOL;
		$node->openingCode .= '<?php ' . $writer->write('}') . '?></div></div>' . PHP_EOL;
	}

	public function wpmScript(
		MacroNode $node,
		PhpWriter $writer)
	{
		return $writer->write('echo \App\Extensions\App\WebPackManifest::script(%node.word, %node.array);');
	}

	public function wpmImage(
		MacroNode $node,
		PhpWriter $writer)
	{
		return $writer->write('echo \App\Extensions\App\WebPackManifest::image(%node.word, %node.array);');
	}

	public function wpmImageBase64(
		MacroNode $node,
		PhpWriter $writer)
	{
		return $writer->write('echo \App\Extensions\App\WebPackManifest::imageBase64(%node.word, %node.array);');
	}

	public function wpmFile(
		MacroNode $node,
		PhpWriter $writer)
	{
		return $writer->write('echo \App\Extensions\App\WebPackManifest::file(%node.word, %node.array);');
	}
}