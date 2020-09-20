{include './list.fces.tpl'}
{$image_prev='list'}
<div class="table-responsive">
{if $LIST.errors}
	<div class="alert alert-danger" role="alert">{foreach $LIST.errors as $item}
		<p>{$item}</p>
	{/foreach}</div>
{/if}
	<div class="dataTables_wrapper no-footer{if $LIST.static &&  isset($LIST.data) && !empty($LIST.data)} static{/if}{if !$LIST.sorting} static-nosort{/if}" data="{if $LIST.ajaxResetView}ajax-reset{/if}"{if is_string($LIST.sorting)} data-default-sort="{$LIST.sorting}"{/if}>
		{if isset($LIST.filter)}{include './list.filter.tpl'}{/if}
		{if isset($LIST.caption)}<div class="caption">{$LIST.caption}</div>{/if}
		<table class="table table-striped table-hover table-condensed dataTable no-footer" role="grid" uid="{$LIST.listUID}" model="{$LIST.scope->module}">
		{if isset($LIST.header)}
			<thead>
				<tr role="row" sort_url="?{$LIST.scope->getStaticLink()}&amp;sort=">
					{include './list.head.3.tpl' data=$LIST.header elm='th'}
				</tr>
			</thead>
		{/if}
			<tbody class="list-body">
		{if isset($LIST.data) && !empty($LIST.data)}
			{include './list.lines.3.tpl' rows=$LIST.data}
		{else}
			<tr role="row"><td colspan="{count($LIST.header)+$LIST.actions.colsnum}">Seznam neobsahuje žádné položky</td></tr>
		{/if}
			</tbody>
		</table>
		{include './list.pages.tpl'}
		{include './list.ajax.tpl'}
	</div>
</div>