{include './form.begin.tpl'}
{$image_prev='form'}
{foreach $FORM.elements as $i}
	{if $i.type == FormUITypes::SEPARATOR}
	<span class="col-sm-1"></span><h3 class="col-sm-11 text-primary">{$i.title}</h3>	
	{else}
	{if $i.type != FormUITypes::HIDDEN && $i.access != 0}
	{if $i.inline == 'first'}<div class="form-group form-inline form-multiline"><label for="{$i.key}" class="col-sm-2 control-label">{$i.title}</label>{/if}
	<div class="form-group {cycle values="odd,even"}">
		{if $i.inline != 'first'}<label for="{$i.key}" class="{if !$i.inline}col-sm-2{/if} control-label">{$i.title}</label>{/if}
		<div class="{if !$i.inline}col-sm-10{else}inline{/if}{*if $i.type == FormUITypes::CHECKBOX} checkbox-styled{/if*}"{if $i.hide} hide="{$i.hide}"{/if}>
			{$atrib = ''}
			{if $i.atrib}{capture assign=atrib}{foreach $i.atrib as $k => $v} {$k}="{$v}"{/foreach}{/capture}{/if}
			{if $i.tpl}{include $i.tpl c=$i d=$i.data class="form-control" attributes=$atrib}{/if}
			{if isset($FORM.actions)}{include "./`$FORM.actions.Tpl`" id=$i.key sc=$FORM.sc a=$FORM.actions spec=$i.spec first_row='none'}{/if}
			{if isset($FORM.fieldStatuses)}
			<div class="stats">
			{foreach from=$FORM.fieldStatuses item=status}
				{if isset($i.statuses[$status])}{call name=icon icon=$status}{else}{call name=icon icon=none}{/if}
			{/foreach}
			</div>
			{/if}
		</div>
	</div>
	{else}
		{if $i.tpl}{include $i.tpl c=$i d=$i.data}{/if}
	{/if}
	{if $i.inline == 'last'}</div>{/if}
	{/if}
{/foreach}
{include './form.end.tpl'}