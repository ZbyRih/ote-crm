{include './list.fces.tpl'}
<div class="table-responsive">
{if isset($LIST.caption)}<h3 class="caption">{$LIST.caption}</h3>{/if}
{if $LIST.errors}
	<div class="alert alert-danger" role="alert">{foreach from=$LIST.errors item=item}
		<p>{$item}</p>
	{/foreach}</div>
{/if}
	<div class="dataTables_wrapper no-footer{if $LIST.static} static{/if}">
		{if isset($LIST.filter)}{include './list.filter.tpl'}{/if}
		{if isset($LIST.caption)}<div class="caption">{$LIST.caption}</div>{/if}
		<table class="table table-striped table-hover dataTable no-footer" role="grid" uid="{$LIST.listUID}" model="{$LIST.scope->module}">
		{if isset($LIST.header)}
			<thead>
				<tr role="row" sort_url="?{$LIST.scope->getStaticLink()}&amp;sort=">
					{include './list.head.3.tpl' data=$LIST.header elm='th'}
				</tr>
			</thead>
		{/if}
			<tbody class="list-body">
			{if isset($LIST.data) && !empty($LIST.data)}
				{foreach $LIST.data as $group_id => $group}
				<tr role="row" class="group-name"><td colspan="100">{$group.name}</td></tr>
				{include './list.lines.3.tpl' rows=$group.rows}
				{if $group.adds}
				<tr role="row" class="">{include './line.form.tpl' FORM=$group.adds}</tr>
				<tr role="row" class="empty"></tr>
				{/if}
				{/foreach}
			{else}
				<tr role="row"><td colspan="{count($LIST.header)+$LIST.actions.colsnum}">Seznam neobsahuje žádné položky</td></tr>
			{/if}
			</tbody>
		</table>
		{include './list.pages.tpl'}
		{include './list.ajax.tpl'}
	</div>
</div>