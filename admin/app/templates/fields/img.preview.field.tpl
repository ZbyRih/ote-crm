{if $d}
	{if $d->type == 'image'}
	<div class="img-preview">
	{if $d->src}
		{if $link}<a href="?{$link}">{/if}
			<img src="{$d->src}" width="{$d->size.w}px" height="{$d->size.h}px"/>
		{if $link}</a>{/if}
	{else}
		{if $link}<a href="?{$link}">{/if}
		{$d->reason}
		{if $link}</a>{/if}
	{/if}
	{if $d->big}<div class=""><a href="{$d->big}" title="Stáhnout" download>{$d->file}</a></div>{/if}
	<div class="img-desc">[{$d->org.w} x {$d->org.h}]</div>
	<div class="clr"></div>
	</div>
	{elseif $d->type == 'flash'}
		{call icon icon="fa fa-file-pdf-o"} <a href="{$d->src}" title="Stáhnout" download>{$d->file}</a> [{$d->fsize} B]
	{elseif $d->type == 'video'}
		{call icon icon="fa fa-file-movie-o"} <a href="{$d->src}" title="Stáhnout" download>{$d->file}</a> [{$d->fsize} B]
	{elseif $d->type == 'other'}
		{call icon icon="md md-attach-file"} <a href="{$d->src}" title="Stáhnout" download>{$d->file}</a> [{$d->fsize} B]
	{else}
		{call icon icon="fa fa-file-o"} {$d->file} Missing
	{/if}
{/if}