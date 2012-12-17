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

function uncollapser(type,media,count)
{
  $('#uncollapse_links').hide();
  $('#uncollapse_loading').show();
  media = media ? "&media=true" : "";
  data = $('.post:last')[0].id.split('_');
  id = data[1];

  var start = $('.post:first').next()[0].id.split('_')[3] - 1;
  var num = count;
  if (count !== null)
  {
    start -= count;
  }
  else
  {
    num = start;
    start = 0;
  }

  if (start < 0)
  {
    num += start;
    start = 0;
  }
  if (start == Math.min(start,num))
  {
    $("#uncollapse_all").hide();
    $("#uncollapse_more_counter").html("all " + Math.min(start,num));
    if (start == 1) $("#uncollapse_some").html("show final post");
  }
  else
  {
    $("#uncollapse_counter").html(start);
    $("#uncollapse_more_counter").html(Math.min(start,num));
  }
  $.ajax(
  {
    url: '/'+type+'/view/'+id+'/-'+start+'/'+num+'/&ajax=true'+media,
    cache: false,
    success: function(html)
    {
      $('#uncollapse').after(html);
      if(start<=0) $('#uncollapse').hide();
      $('#uncollapse_loading').hide();
      $('#uncollapse_links').show();
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
               if(html) $(document).scrollTop($(document).height()+500);
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

function toggle_ignore_thread(id)
{
  var status;
  if($('#ignorecmd').html() == "ignore") status = "ignoring...";
  if($('#ignorecmd').html() == "unignore") status = "unignoring...";
  if(!status) return;
  $('#ignorecmd').html(status);

  $.ajax(
  {
    url: '/thread/toggleignore/'+id+'/',
    cache: false,
    success: function(html){ $('#ignorecmd').html(jQuery.trim(html)); }
  });
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

function undot(id)
{
  var status;
  $('#undot').html('undotting...');

  $.ajax(
  {
    url: '/thread/undot/'+id+'/',
    cache: false,
    success: function(html){ $('#undot').html(jQuery.trim(html)); }
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
