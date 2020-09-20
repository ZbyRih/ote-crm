<p class="{$class}-static"{if $c.num == 3}dt="currency"{/if}{if $c.num == 2}dt="float"{/if}{if $c.num == 4}dt="numeric"{/if}>{$d.value}</p>
<input type="hidden" name="{$c.key}" value="{$d.value}" />