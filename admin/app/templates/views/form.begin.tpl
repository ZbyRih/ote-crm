{$image_prev='form'}
{if $FORM.errors}
	<div class="alert alert-danger" role="alert">{foreach from=$FORM.errors item=item}
		<p>{$item}</p>
	{/foreach}</div>
{/if}
<form action="index.php?{$FORM.scope->getLink()}" enctype="multipart/form-data" method="post"{if $FORM.formName} name="{$FORM.formName}" id="{$FORM.formName}"{/if} class="form-horizontal">
	<input type="hidden" name="_uid" value="{$FORM._uid}" />
{if $FORM.formName}	<input type="hidden" name="form_name" value="{$FORM.formName}"/>{/if}