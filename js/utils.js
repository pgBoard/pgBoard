Array.prototype.remove = function(from,to)
{
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};


function firstpost(type,num,ob)
{
  var fp = $('#fp_'+num);
  fp.toggle();

  if(!fp.attr('loaded'))
  {
     fp.html("loading...");
     $.ajax(
     {
       url: '/'+type+'/firstpost/'+num+'/&ajax=true',
       cache: false,
       success: function(html)
       {
         fp.attr('loaded',true);
         fp.html(html);
       }
    });
  }
  if(fp.css('display') == 'block') $(ob).html('&laquo;');
  else
  $(ob).html('&raquo;');
};

function uncollapse(type,num,media,ob)
{
  if(ob)
  {
    if(!ob.save) ob.save = ob.innerHTML;
    ob.innerHTML = "loading...";
  }
  media = media ? "&media=true" : "";
  data = $('.post:last')[0].id.split('_');
  id = data[1];
  $.ajax(
  {
    url: '/'+type+'/view/'+id+'/0/'+num+'/&ajax=true'+media,
    cache: false,
    success: function(html)
    {
      $('#view_'+id).prepend(html);
      $('#uncollapse').remove();
    }
  });
};

function loadposts(type,ob)
{
  if(ob)
  {
    if(!ob.save) ob.save = ob.innerHTML;
    ob.innerHTML = "loading...";
  }
  data = $('.post:last')[0].id.split('_');
  id = data[1];
  lastpost = parseInt(data[data.length-1]);
  $.ajax(
  {
    url: '/'+type+'/view/'+id+'/'+lastpost+'/&ajax=true',
    cache: false,
    success: function(html)
             {
               $('#view_'+id).append(html);
               if(html) window.scroll(0,99999999);
               if(ob) ob.innerHTML = ob.save;
             }
  });
};

function preview_post(form,type,id)
{
  $('#'+form).ajaxSubmit(
  {
    target: '#response_'+form,
    beforeSubmit: validate,
    url: '/'+type+'/previewpost/'+id,
    success: function() { $('.submit').attr('disabled',false); }
  });
};

function quote_post(id)
{
  var info = $('#post_'+id+' .postinfo').text();
  var body = jQuery.trim($('#post_'+id+' .postbody').text());
  $('#body').val($('#body').val()+'[quote]'+info+'\n'+body+'[/quote]\n\n');
}

function toggle_favorite(id)
{
  var status;
  if($('#fcmd').html() == "add") status = "adding...";
  if($('#fcmd').html() == "remove") status = "removing...";
  if(!status) return;
  $('#fcmd').html(status);

  $.ajax(
  {
    url: '/thread/togglefavorite/'+id+'/',
    cache: false,
    success: function(html){ $('#fcmd').html(jQuery.trim(html)); }
  });
}

// Member Add Box
function catch_enter(e)
{
  var code = e ? e.keyCode : event.keyCod;
  if(code == 13)
  {
    check_member();
    return false;
  }
  else
  return true;
}

function check_member()
{
  $('#notice').remove();
  if(!$('#_recipients').val()) return;
  $('#add').val('Adding..');

  members = $('#_recipients').val();
  $('#_recipients').val('');
  $.post("/message/addmember/",{names:members},function(data)
  {
    data = jQuery.trim(data);
    if(data)
    {
      data = data.split(',');
      for(i=0;i<data.length;i=i+2) add_member(data[i],data[i+1]);
    }
    $('#add').val('Add');
  });
}

function add_member(id,name)
{
  var mm = $('#message_members').val();
  if(!mm) mm = new Array();
  else
  mm = mm.split(',');
  if(jQuery.inArray(id,mm) == -1)
  {
    members = $('#message_members').val();
    if($('#m').html() == '-') $('#m').empty();
    $('#m').append('<span id="m'+id+'"><sup><a href="javascript:;" onclick="remove_member(\''+id+'\')">x</a></sup>&nbsp;'+name+'&nbsp;&nbsp;&nbsp;</span> ');
    $('#message_members').val((members?members+',':'')+id);
  }
}

function remove_member(id)
{
  var mm = $('#message_members').val().split(',');
  if(jQuery.inArray(id,mm) != -1)
  {
    mm.remove(jQuery.inArray(id,mm));
    $('#message_members').val(mm.join(','));
    $('#m'+id).remove();
    if(!mm.length) $('#m').html('-');
  }
}
