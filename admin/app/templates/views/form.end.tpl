	<div class="form-group">
{if isset($FORM.buttons)}{foreach from=$FORM.buttons item=button key=key}
	<button type="submit" class="{#btn_primary_ink#}" name="{$key}" value="{$button}">{$button}</button>
{/foreach}{/if}
	</div>
</form>