<h3>{$element->name}</h3>
{if $element->format == 'pre'}
<pre>{$element->data}
</pre>
{else if $element->format == 'html'}
<pre>{$element->data.head}</pre>
<div id="mail-html">
</div>
<script type="text/javascript">
	$(document).ready(function(){
	var html = $('#mail-src')[0].textContent;
	var ifrm = $('<iframe frameborder="0" tabindex="0" allowtransparency="true" style="width: 100%; height: 100%;" scrolling="no">')
		.attr('src', 'about:blank')
		.appendTo($('#mail-html'));
	iDoc = (ifrm[0].contentWindow? ifrm[0].contentWindow.document: ifrm[0].contentDocument);
	iDoc.open('text/html','replace');
    iDoc.write(html);
    iDoc.close();
    ifrm.height(iDoc.body.scrollHeight);
	});
</script>
<textarea style="display:none" id="mail-src">{$element->data.body}</textarea>
{else}
{$element->data}
{/if}