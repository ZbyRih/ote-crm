<h1>{$data.label}</h1>
<h2>Parametry CSV</h2>
<p>
	<ul>
		<li>CSV je standartně v kódování UTF-8</li>
		<li>jako oddělovač je použit středník ;</li>
		<li>texty uzavřeny ve dvojitých uvozovkách "</li>
	</ul> 
</p>
<form name="import" enctype="multipart/form-data" method="post">
	{if $data.imp}
	<input type="file" name="csv_file" /><br />
	{/if}
	<input type="submit" name="_action" value="{$data.action}">
</form>
{if $data.aft_imp}
<h3>Výpis chyb</h3>
<p>{$data.message}</p>
{foreach from=$data.messages item=m}
<p>Line {$m.line}: {$m.message}</p>
{/foreach}
<h3>Konec Výpisu</h3>
{/if}