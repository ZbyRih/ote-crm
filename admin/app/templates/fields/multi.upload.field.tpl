{if !$d.value}
<div class="input-group dropzone" max-size={$d.maxSize}>
    <span class="input-group-btn">
		<span class="btn btn-default btn-file btn-primary">
		    Vybrat... <input type="file" name="{$c.key}" id="{$c.key}"{if $class} class="{$class}"{/if} />
		</span>
    </span>
    <input type="text" class="form-control" readonly="" name="{$c.key}_name">
</div>
	{if isset($d.info)}<p>{foreach from=$d.info item=iline}{$iline}<br />{/foreach}</p>{/if}
{else}
	{include file='fields/variable.media.tpl' data=$d.value}
{/if}