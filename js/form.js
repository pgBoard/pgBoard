function validate(data,form,options)
{
  var res = true;
  id = $(form).attr('id');
  $('.alert').removeClass('alert');
  $('.alertmsg').remove();
  $('.validate_'+id).each(function()
  {
    label = $('#label_'+$(this).attr('id'));
    if(!$(this).val())
    {
      if(msg = $(this).attr('notnull'))
      {
        label.addClass('alert');
        $(this).before('<div class="alertmsg">'+msg+'</div>');
        res = false;
      }
    }
  });
  if(window.custom_validate) res = custom_validate();
  if(res) $('#'+id+' input[type=submit]').not('.nodisable').attr('disabled',true);
  return res;
}

function capture_submit(form,ajax)
{
  var response = "#response_"+form.id;

  if(!ajax) return validate(false,form,false);
  else
  {
    if(window.completed) $(form).ajaxSubmit({ target: response, beforeSubmit: validate, success: completed });
    else
    $(form).ajaxSubmit({ target: response, beforeSubmit: validate });
  }
  
  return false;
}
