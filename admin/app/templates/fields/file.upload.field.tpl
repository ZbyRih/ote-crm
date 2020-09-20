{if !$d.value || $c.upload}
	{if isset($d.file_info)}
	<p class="alert alert-{$d.file_info.type}" role="alert">{$d.file_info.name} {$d.file_info.size}</p>
	{/if}
<div class="input-group">
    <span class="input-group-btn">
		<span class="btn btn-default btn-file btn-primary">
		    Vybrat... <input type="file" name="{$c.key}" id="{$c.key}"{if $class} class="{$class}"{/if} />
		</span>
    </span>
    <input type="text" class="form-control" readonly="" name="{$c.key}_name">
</div>
	{if isset($d.info)}<p>{foreach $d.info as $i}{$i}<br />{/foreach}</p>{/if}
{else}
	<input type="hidden" name="{$c.key}" id="{$c.key}" value="{$d.value}" />
	{if isset($d.img)}
	{include file='fields/variable.media.tpl' i=$d.img}
	{else}
	{if isset($d.file_info)}
	<p class="alert alert-{$d.file_info.type}" role="alert">{$d.file_info.name} {$d.file_info.size}</p>
	{/if}	
	{/if}
{/if}