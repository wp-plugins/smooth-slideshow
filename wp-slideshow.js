jQuery(document).ready(function(){
  start_fade_slide();
});

var slider_index = 0;
var current_slider_index = 0;

function start_fade_slide() {
    ++slider_index;
    if(slider_index == total_slide) {
     slider_index = 0; 
    }
   slide_motion(slider_index);

}

var text_obj = [];
function slide_motion(slider_index) {
  if(text_obj.length > 0) {
   var obj = text_obj.shift();
   obj.css('opacity',0);
  }
  
    jQuery("#slide_"+slider_index).animate({ 
        opacity: 1
  }, 500,function() {
    var pos = jQuery("#slideshowcontainer").position();
    var left = pos.left;
    left = left + 600;
    var top = pos.top;
    top = top + 50;
    jQuery("#text_"+slider_index).css("left",left+"px");
    jQuery("#text_"+slider_index).css("top",top+"px");
    jQuery("#text_"+slider_index).css('opacity',0.8);
    
    jQuery("#text_"+slider_index).animate({ 
        left: "-=550"
    }, 500,function() {
      text_obj.push(jQuery(this))
    }
    );
   
  });
  
  jQuery("#slide_"+current_slider_index).animate({ 
        opacity: 0
  }, 500);
  
    
  jQuery('#slide_'+slider_index).css("z-index",999);
  
  jQuery('#slide_'+current_slider_index).fadeOut(1000, function () {
    
    jQuery('#slide_'+slider_index).css("z-index",1000);
    jQuery(this).css("z-index",900);
    jQuery(this).fadeIn('fast');
    current_slider_index = slider_index;
    window.setTimeout("start_fade_slide()",3000);
  });
}