{if $ajax_element}
	{if $ajax_element.tpl}
		{include $ajax_element.tpl c=$ajax_element link=$lineLink bList=true}
	{else}
		<a href="#" field="{$ajax_element.key}" class="ajax" title="Změnit">{if ($col === NULL || empty($col))}Změnit{else}{$col}{/if}</a>
	{/if}
{else}
	{$defA=null}
	{if $LIST.actions.default->action != 'none'}
		{if $LIST.actions.default}
			{$defA=$LIST.scope->getDynLink($id, $LIST.actions.default->action)}
		{else}
			{$defA=$lineLink}
		{/if}
	{/if}
	{if $LIST.tplToField[$colid]}
		{include $LIST.tplToField[$colid] c=$col d=$col link=$lineLink bList=true}
	{elseif $defA}
		<a href="?{$defA}" title="{$LIST.actions.default->title}" class="sel">{$col}</a>
	{else}
		{$col}
	{/if}
{/if}