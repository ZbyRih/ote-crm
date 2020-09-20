{$ajaxEdIt=null}
{$dTpl=null}
{$id=null}
{foreach $rows as $row}
	{$id=$row['id']}
	{$lineLink=$LIST.scope->getDynLink($id)}
	<li class="{list_row_class}" id="{$LIST.listUID}_{$id}" rec="{$id}" reclink="?{$lineLink}" role="row">
		<span class="name">
			{$odsazeni}{include './tree.col.padding.tpl' total=$row@total id=$id item=$row first=$row@first last=$row@last}
			{if not $LIST.mode}
				<a href="?{$LIST.scope->getDynLink($id, 'select')}" title="Vybrat položku" class="txt sel">[{$id}]&nbsp;{$row.name}</a>
			{elseif $LIST.mode == 'select'}
				<a href="?{$lineLink}" title="Vybrat položku" class="txt sel">[{$id}]&nbsp;{$row.name}</a>
			{elseif $LIST.mode == 'check'}
				<span class="txt selm">[<span class="id">{$id}</span>]&nbsp;<span class="name" id="name_{$id}">{$row.name}</span></span>
			{/if}
		</span>
{foreach $row.data as $colid => $col}
	{if $col@index != 0}
		{$ajax_element=$LIST.ajaxEditItems[$colid]}
		<span{list_col_typ}>
		{include './list.col.tpl'}
		</span>
	{/if}
{/foreach}
		{if $row@total > 1}
			{if $row@first}{$frs='first'}{elseif $row@last}{$frs='last'}{else}{$frs=''}{/if}
		{else}
			{$frs='none'}
		{/if}		

		{include './list.line.actions.tpl' elm='span'}

		</li>
{if isset($row.sub) && !empty($row.sub)}
	{capture name=tab assign=new_tab}
		{$odsazeni}{list_tree_add_pad last=$row@last}
	{/capture}
	{include file='./tree.lines.tpl' rows=$row.sub odsazeni=$new_tab parent=$id}
{/if}
{if not $LIST.mode}
	{if ( $LIST.scope->recordId == $id )&&( $parent != $LIST.scope->recordId )&&( not isset($row.sub) ) }
		<li>
			<span class="add" colspan="0">
				{$odsazeni}{list_tree_add_pad last=$row@last}{list_tree_add parent=$id}
			</span>
		</li>
	{/if}
{/if}		
{/foreach}
{if not $LIST.mode}
		<li>
			<span class="add">
				{$odsazeni}{list_tree_add parent=$parent}
			</span>
		</li>
{/if}