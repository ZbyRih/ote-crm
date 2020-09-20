
{function name=icon icon=none}<i class="{$icon}"></i>{/function}

{function name=btn icon=none kind=flat more=false link=false title=false id=false text=false}<a{if $id} id="{$id}"{/if} href="{if $link}{$link}{else}javascript:void(0);{/if}" class="btn btn-{$kind}{if $more} {$more}{/if}"{if $title} title="{$title}"{/if}>{icon icon=$icon}{if $text}&nbsp;{$text}{/if}</a>{/function}

{function name=inkbtn icon=none kind=flat more=false link=false title=false id=false text=false}<a{if $id} id="{$id}"{/if} href="{if $link}{$link}{else}javascript:void(0);{/if}" class="btn btn-{$kind} ink-reaction{if $more} {$more}{/if}"{if $title} title="{$title}"{/if}>{icon icon=$icon}{if $text}&nbsp;{$text}{/if}</a>{/function}