{$ajaxEdIt = null}
{$dTpl = null}
{foreach $rows as $id => $row}
	{$lineLink = $LIST.scope->getDynLink($id)}
	{if isset($row.data)}
	<tr role="row" class="{list_row_class}" id="{$LIST.listUID}_{$id}" rec="{$id}" reclink="?{$lineLink}">
		{if $LIST.numbered}<td>{$LIST.numStart+$row@index}</td>{/if}
		{foreach $row.data as $colid => $col}
			{$ajax_element=$LIST.ajaxEditItems[$colid]}
			<td{list_col_typ}>{include './list.col.tpl'}</td>
		{/foreach}
		
		{if $row@total > 1}
			{if $row@first}{$frs='first'}{elseif $row@last}{$frs='last'}{else}{$frs=''}{/if}
		{else}
			{$frs='none'}
		{/if}		
		{include './list.line.actions.tpl' elm='td' frs=$frs}
	</tr>
	{/if}
{/foreach}