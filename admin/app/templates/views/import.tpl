<div class="import">
	<h1>{$data.label}</h1>
	<h2>Parametry CSV</h2>
	<p>
		<ul class="info">
			<li>CSV je standartně v kódování UTF-8</li>
			<li>jako oddělovač je použit středník ;</li>
			<li>texty uzavřeny ve dvojitých uvozovkách "</li>
			<li>Každá položka má na své řádce unikátní ID, pokud ID není uvedeno položka se při importu založí</li>
		</ul> 
	</p>
	
	<form name="import" enctype="multipart/form-data" method="post">
		<input type="file" name="{$data.fieldKey}" /><br />
		<input type="submit" name="import" value="{$data.action_label}">
	</form>
	
	{if $data.bReport}
	<p>{$data.report.message}</p>
	{if empty($data.report.errors)}<h3>Bez chyb</h3>{/if}
	<p>
		<ul class="report">
			<li>Načtených řádek: {$data.report.loadedLines}</li>
			<li>Zpracovaných řádek: {$data.report.processLines}</li>
			<li>Nových záznamů: {$data.report.insertNums}</li>
			<li>Upravených záznamů: {$data.report.updateNums}</li>
			<li>Zápisů do DB: {$data.report.fullNums}</li>
		</ul>
	</p>
	{if !empty($data.report.errors)}	
	<h3>Výpis chyb</h3>
	<p>{$data.report.message}</p>
	{foreach from=$data.report.errors item=m}
	<p>Line {$m.line}: {$m.message}</p>
	{/foreach}
	<h3>Konec Výpisu</h3>
	{/if}
	{/if}
</div>