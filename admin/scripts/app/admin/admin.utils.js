/* Drag A Div with jQuery */
jQuery.fn.draggit = function (el) {
    var thisdiv = this;
    var thistarget = $(el);
    var relX;
    var relY;
    var targetw = thistarget.width();
    var targeth = thistarget.height();
    var docw;
    var doch;
    var ismousedown = false;

    var pos = localStorage.getItem(thistarget.attr('id'));
    if(pos != undefined){
    	var poss = pos.split(',');
    	thistarget.css({top: poss[0]+'px', left: poss[1]+'px'});
    }else{
    	var p = thistarget.offset();
    	thistarget.css({top: p.top+'px', left: p.left+'px'});
    }
    thistarget.css('position','absolute');

    thistarget.bind('mousedown', function(e){
        var pos = $(el).offset();
        var srcX = pos.left;
        var srcY = pos.top;

        docw = $('body').width();
        doch = $('body').height();

        relX = e.pageX - srcX;
        relY = e.pageY - srcY;

        ismousedown = true;
    });

    $(document).bind('mousemove',function(e){ 
        if(ismousedown)
        {
            targetw = thistarget.width();
            targeth = thistarget.height();

            var maxX = docw - targetw - 10;
            var maxY = doch - targeth - 10;

            var mouseX = e.pageX;
            var mouseY = e.pageY;

            var diffX = mouseX - relX;
            var diffY = mouseY - relY;

            // check if we are beyond document bounds ...
            if(diffX < 0)   diffX = 0;
            if(diffY < 0)   diffY = 0;
            if(diffX > maxX) diffX = maxX;
            if(diffY > maxY) diffY = maxY;

            $(el).css('top', (diffY)+'px');
            $(el).css('left', (diffX)+'px');
        }
    });

    $(window).bind('mouseup', function(e){
        ismousedown = false;
        var p = thistarget.offset();
        var a = [p.top, p.left];
        localStorage.setItem(thistarget.attr('id'), a.join(','));
    });

    return this;
}; // end jQuery draggit function //