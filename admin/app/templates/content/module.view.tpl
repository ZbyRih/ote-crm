<section>
	<div class="section-header">
	{if $Module}{if !empty($Module.breadcrumbs)}
		<ol class="breadcrumb">
			<li class="active"></li>
		</ol>
		{/if}
		{if !empty($Module.menu)}
		<div class="btn-group btn-margin">
		{foreach from=$Module.menu item=m key=a}
			{if $m->confirm}
				{call btn icon=$m->icon title=$m->name text=$m->name kind='primary' more='btn-raised confirm' link="?`$Module.scope->getStaticLink($a)`"}
			{else}			
				{call btn icon=$m->icon title=$m->name text=$m->name kind='primary' more='btn-raised' link="?`$Module.scope->getStaticLink($a)`"}
			{/if}
		{/foreach}
		</div>
		{/if}
	{/if}
	</div>	
	<div class="section-body{* contain-lg*}">
		<div class="row">
{if $Module}
			<div class="card">
				<div class="card-head">
					{if $Module.elements->title}<header>{$Module.elements->title}</header>{/if}
					{if !empty($Module.elements->head)}
						{foreach from=$Module.elements->head item=e}
							{include './module.content.tpl' element=$e uid=$e@iteration}
						{/foreach}
					{/if}
				</div>
				<div class="card-body main">
				{if !empty($Module.elements->main)}
					{foreach from=$Module.elements->main item=e}
						{include './module.content.tpl' element=$e uid=$e@iteration}
					{/foreach}
				{else}
					<h1>Modul '{$Module.name}' nemá žádné views</h1>
				{/if}
				</div>
			</div>
{else}
			<div class="card jine">
				<div class="card-body">
					<h1>Modul {$Module.name} není dostupný</h1>
				</div>
			</div>
{/if}
		</div>
	</div>
</section>