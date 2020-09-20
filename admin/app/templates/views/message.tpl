{if !empty($element->data)}
{foreach $element->data as $m}
<div class="alert alert-{$m.type}">
	{$m.text}
</div>
{/foreach}
{/if}