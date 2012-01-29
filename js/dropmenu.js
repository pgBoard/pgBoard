function menuhandle(ob)
{
  id = $(ob).prev()[0].id;
  dropmenu = ob.parentNode;
  if(!$(dropmenu).hasClass('active'))
  {
    $('.dropmenu').removeClass('active');
    $(dropmenu).addClass('active');
  }
  else
  {
    $(dropmenu).removeClass('active');
  }
  return false;
};

$(document).ready(function()
{
  if(document.all) $('.dropmenu').mouseover(function(){$(this).addClass('hovermenu');}).mouseout(function(){$(this).removeClass('hovermenu');}); //ie6
});
