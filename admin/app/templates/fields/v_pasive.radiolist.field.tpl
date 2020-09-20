{if isset($d.list)}
{foreach from=$d.list item=name key=val}
<input type="radio" name="{$c.key}" id="{$c.key}_{$val}" value="{$val}"{if $val==$d.value} checked="checked"{/if} disabled="disabled" class="{$class}" /><label for="{$c.key}_{$val}" class="view">{$name}</label><br />
{/foreach}
{/if}