<select name="{$c.key}" onchange="javascript:document.location='?{if isset($d.link)}{$d.link}{else}{#k_module#}={$Module.id}&amp;{#k_view#}=list{/if}&amp;{$c.key}=' + this.value;" class="{$class}">
{foreach $c.items as $val => $sitem}
<option value="{$val}"{if $val==$d.select} selected="selected"{/if}>{$sitem}</option>
{/foreach}
</select>