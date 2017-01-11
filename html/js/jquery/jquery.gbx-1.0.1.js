/*
 * jQuery Simple Greybox plugin
 * Version 1.0.0 (03/03/2009)
 * @requires jQuery v1.2.1 or later
 *
 * Copyright (c) 2009 Aleksandar Pavic for majlab.com
 * http://acosonic.com/jquery_simple_overlay/

USAGE:
	
	$( #id ).gbxInit(); 	
	$( #id ).gbxShow(); //-usually assigned to click events	
	$( #id ).gbxHide(); // same as above
EXAMPLE:
$("#gbx_create_album").gbxInit({width:451,height:317,top:100});
		
	$("#gbx_create_album_close").click(function () { //close button inside greybox
		$("#gbx_create_album").gbxHide();
		return false;
	});
			
	$("#create_album_link").click(function() {
		$("#gbx_create_album").gbxShow();
		return false;
	});
*/
(function($) {
  
  $.fn.gbxInit = function(options) {
	var opts = $.extend({}, $.fn.gbxInit.defaults, options);
    return this.each(function() {
      $this = $(this);
/*
		grab the settings if any
*/
      var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
     
	 $('body').append('<div id="gbx_overlay"></div>');
      $this.css(o);
	  var left = document.body.scrollLeft+ ($('html,body').width()-o.width)/2;
      $this.css("left",left);
      $this.css("z-index",3000);
      $this.css("position","absolute");
      $this.bind('drag',function( event ){
        $(this).css({
  			 top: event.offsetY,
  			 left: event.offsetX
  			 });
		  });
  	  $this.append('<div class="gbx_drag"></div>');
       $(".gbx_drag").css ({
          "cursor":"move",
          "height":"20px",
          "z-index":"-1",
          "width":o.width,
          "position": "absolute",
          "top":0,
          "float":"left"
        });
		return false;
    });
  };
  
  $.fn.gbxInit.defaults = {
     /* background: 'yellow'*/
  };
  
  $.fn.gbxShow = function() {
	return this.each( function() {
		if($.browser.mozilla) {
			$("#gbx_overlay").fadeIn('slow');
			$(this).fadeIn('slow');
		 }
		  else {
			$(this).show();
			$("#gbx_overlay").show();
		  }
		  return false;
	});
  }
  
  $.fn.gbxHide = function(id) {
    return this.each( function() {
		if($.browser.mozilla) {
			  $("#gbx_overlay").fadeOut('slow');
			 $(this).fadeOut('slow');
			} else {
			  $("#gbx_overlay").hide();
			 $(this).hide();
			}
			return false;
	});
   }
  
})(jQuery);