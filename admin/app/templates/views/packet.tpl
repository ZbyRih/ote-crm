<div class="row pack">
{foreach $element->elements as $s}
	{if $s instanceof PackElement}
	<div class="col {if $element->float}col-md-{$s->size}{else}col-md-12{/if}">
		{include '../content/module.content.tpl' element=$s->elm uid=$s->elm->uID}
	</div>
	{else if $s instanceof PackBreak}
	</div>
	<div class="row">
		{if $s->title}
		<div class="card-head">
			<header class="text-primary">{$s->title}</header>
		</div>
		{/if}
	{else}
	<p class="text text-danger">
	Neexistuje: {$s|dump}
	</p>
	{/if}
{/foreach}
</div>
<div class="clr"></div>
