{if isset($LIST.pages)}
{if !$LIST.pages.bHide && !empty($LIST.pages.pages)}
<div class="dataTables_paginate paging_simple_numbers" id="list_pager_{$LIST.listUID}">
{$pLink="`$LIST.scope->getStaticLink()`&amp;`$LIST.pages.paramKey`="}
	{call btn link="?`$pLink`1" icon="fa fa-angle-left" title="První" more="paginate_button previous"}
	<span>
	{foreach $LIST.pages.pages as $page}
		<a class="paginate_button{if $page} current{/if}" href="?{$pLink}{$page@key}">{$page@key}</a>
	{/foreach}
	</span>
	{call btn link="?`$pLink``$LIST.pages.last`" icon="fa fa-angle-right" title="Poslední" more="paginate_button next"}
</div>
{/if}
{/if}