<?php if(!session('nosnow')) { ?>
<script>
function nosnow(stop) {
  $.ajax({type:'POST',url:'/',data:{nosnow:true}});
  snowStorm.stop();
  $(stop).remove();
}
</script>
<script src="/lib/snowstorm/snow.js"></script>
<img src="/lib/snowstorm/off.gif" onclick="nosnow(this);" style="float:right;cursor:pointer"/>
<?php
}
if(post('nosnow')) {
  $_SESSION['nosnow'] = true;
}
