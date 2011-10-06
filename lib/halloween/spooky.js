(function(jQuery){jQuery.each(['backgroundColor','borderBottomColor','borderLeftColor','borderRightColor','borderTopColor','color','outlineColor'],function(i,attr){jQuery.fx.step[attr]=function(fx){if(fx.state==0){fx.start=getColor(fx.elem,attr);fx.end=getRGB(fx.end);}
fx.elem.style[attr]="rgb("+[Math.max(Math.min(parseInt((fx.pos*(fx.end[0]-fx.start[0]))+fx.start[0]),255),0),Math.max(Math.min(parseInt((fx.pos*(fx.end[1]-fx.start[1]))+fx.start[1]),255),0),Math.max(Math.min(parseInt((fx.pos*(fx.end[2]-fx.start[2]))+fx.start[2]),255),0)].join(",")+")";}});function getRGB(color){var result;if(color&&color.constructor==Array&&color.length==3)
return color;if(result=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
return[parseInt(result[1]),parseInt(result[2]),parseInt(result[3])];if(result=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
return[parseFloat(result[1])*2.55,parseFloat(result[2])*2.55,parseFloat(result[3])*2.55];if(result=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
return[parseInt(result[1],16),parseInt(result[2],16),parseInt(result[3],16)];if(result=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
return[parseInt(result[1]+result[1],16),parseInt(result[2]+result[2],16),parseInt(result[3]+result[3],16)];return colors[jQuery.trim(color).toLowerCase()];}
function getColor(elem,attr){var color;do{color=jQuery.curCSS(elem,attr);if(color!=''&&color!='transparent'||jQuery.nodeName(elem,"body"))
break;attr="backgroundColor";}while(elem=elem.parentNode);return getRGB(color);};var colors={orange:[255,165,0],red:[255,0,0]};})(jQuery);
jQuery.fn.pulse=function(prop,speed,times,easing,callback){if(isNaN(times)){callback=easing;easing=times;times=1;}
var optall=jQuery.speed(speed,easing,callback),queue=optall.queue!==false,largest=0;for(var p in prop){largest=Math.max(prop[p].length,largest);}
optall.times=optall.times||times;return this[queue?'queue':'each'](function(){var counts={},opt=jQuery.extend({},optall),self=jQuery(this);pulse();function pulse(){var propsSingle={},doAnimate=false;for(var p in prop){counts[p]=counts[p]||{runs:0,cur:-1};if(counts[p].cur<prop[p].length-1){++counts[p].cur;}else{counts[p].cur=0;++counts[p].runs;}
if(prop[p].length===largest){doAnimate=opt.times>counts[p].runs;}
propsSingle[p]=prop[p][counts[p].cur];}
opt.complete=pulse;opt.queue=false;if(doAnimate){self.animate(propsSingle,opt);}else{optall.complete.call(self[0]);}}});};
$(document).ready(function()
{
  $('body').prepend('<div style="margin-bottom:-69px;height:69px;background:transparent url(/lib/halloween/birds.gif);"></div>');
  if(location.href.indexOf('view') == -1) $('.title').html($('.title').html()+' <span class="pulse">redrum.</span>');
  $('.pulse').pulse({color:['red', 'orange']},1000,100000,'linear',function(){});
  $('sup').after('<img src="/lib/halloween/spider.gif" onclick="scream()">');
});
function scream(){$('body').append('<embed src="/lib/halloween/scream.wav" autostart="true" hidden="true" loop="false">');}
