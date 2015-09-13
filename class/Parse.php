<?php
class BoardParse
{
  private $bbc;
  private $rep;
  private $imgsuffix = array("jpg","gif","png");
  private $hidemedia = false;

  function __construct($bbc,$rep)
  {
    $this->bbc = $bbc;
    $this->rep = $rep;
    $this->hidemedia = session('hidemedia');
    if(get('media')=='enabled') $this->hidemedia=false;
    else if(get('media')=='disabled') $this->hidemedia=true;
    else if(get('media')) $this->hidemedia = !$this->hidemedia;
  }

  // prepare urls (so hack)
  function prep_url_linktext($href) { return $this->prep_url(array($href[1]),htmlentities($href[2])); }
  function prep_url($href,$link=false)
  {
    $clean = str_replace(array("[url]","[/url]"),"",$href[0]);
    if(substr($clean,0,3) == "www") $clean = "http://$clean";
    if(!$link) $link = htmlentities($clean);
    $clean = trim($clean);

    if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',$clean)) return $href[0];
    else
    {
      $host = parse_url($clean);
      $host = $host['host'];
      $link = htmlspecialchars_decode($link);
      return "<a href=\"$clean\" class=\"link\" onclick=\"window.open(this.href); return false;\" title=\"$link\">$link</a> [$host] ".ARROW_RIGHT.SPACE;
    }
  }

  // Using a set of regexps try to extract the video id of as many forms of current and legacy
  // youtube URLs as possible.  Match and allow playlist, users' uploads, and query style embed
  // URLs too, but just pass those through since they don't need special handling.
  //
  // Collect the params and process them:
  //   - convert &t to &start params to work with the new embed style time specifiers.
  //   - rewrite 'autoplay' param to disable that functionality
  //
  // Return an iframe embed tag for the video that supports all platforms automatically as per
  // the youtube embed docs here: https://developers.google.com/youtube/player_parameters
  function youtube($href)
  {

    // make sure no entities or escapes are in the URL string so that the regexps are simpler
    $href[1] = htmlspecialchars_decode($href[1]);

    $video_width  = 425;
    $video_height = 355;

    $video_id     = "";
    $video_params = "";

    $playlist_id = "";
    $playlist_params = "";

    $playlist_url = "";


    if ( preg_match("/\A.*youtu\.be\/(.*?)(\Z|\?)+(.*)/i", $href[1], $matches) ) {

      // Match https://youtu.be/VIDEO_ID?foo=1&bar=baz
      // Click Share button on a video for this short URL
      $video_id     = $matches[1];
      $video_params = $matches[3];

    } elseif ( preg_match("/\A.*youtube\.com\/embed\/(.*?)(\Z|\?)+(.*)/i", $href[1], $matches) ) {

      // Match https://www.youtube.com/embed/VIDEO_ID?foo=1&bar=baz
      // Share -> Embed and copy src from the embed code
      $video_id     = $matches[1];
      $video_params = $matches[3];

    } elseif ( preg_match("/\A.*youtube\.com\/watch\?v=(.*?)(\Z|\&)+(.*)/i", $href[1], $matches) ) {

      // Match https://www.youtube.com/watch?v=VIDEO_ID&foo=1&bar=baz
      // This seems to show in the URL bar when you arrive on a video from an external link
      $video_id     = $matches[1];
      $video_params = $matches[3];

    } elseif ( preg_match("/\A.*youtube\.com\/v\/(.*?)(\Z|\?)+(.*)/i", $href[1], $matches) ) {

      // Match http://www.youtube.com/v/VIDEO_ID?version=3&foo=1&bar=baz
      // Embedded AS3 player: (DEPRECATED)
      $video_id     = $matches[1];
      $video_params = $matches[3];

    } elseif ( preg_match("/\A.*youtube\.com\/apiplayer\?video_id=(.*?)(\Z|\&)+(.*)/i", $href[1], $matches) ) {

      // Match http://www.youtube.com/apiplayer?video_id=VIDEO_ID&version=3
      // Chromeless AS3 player: (DEPRECATED)
      $video_id     = $matches[1];
      $video_params = $matches[3];

    } elseif ( preg_match("/\A.*www\.youtube\.com\/playlist\?list=(.*?)(\Z|\&)(.*)\Z/i", $href[1], $matches) ) {

      // Match: Playlist Share link from playlist page -> click share button
      // https://www.youtube.com/playlist?list=PLf7Pime6sgNUom_fs9wktBPMo9IrEHJT-&foo=bar
      $playlist_id     = $matches[1];
      $playlist_params = $matches[3];

    } elseif ( preg_match("/\A.*youtube\.com\/embed\?listType=playlist&list=(.*)\Z/i", $href[1], $matches) ) {

      // Match: Playlist embed http://www.youtube.com/embed?listType=playlist&list=PLC77007E23FF423C6
      $playlist_url = $href[1];

    } elseif ( preg_match("/\A.*youtube\.com\/embed\?listType=user_uploads&list=(.*)\Z/i", $href[1], $matches) ) {

      // Match: Username's videos list: http://www.youtube.com/embed?listType=user_uploads&list=KenBlockRacing
      $playlist_url = $href[1];

    } elseif ( preg_match("/\A.*youtube\.com\/embed\?listType=search\&list=(.*)\Z/i", $href[1], $matches) ) {

      // Match: search query video list: http://www.youtube.com/embed?listType=search&list=QUERY
      $playlist_url = $href[1];

    } else {
      // how to errrrrror?
      return $href[1];
    }

    // Split the params out and process them if they exist
    if ( $video_params !== "" ) {

      // Handle a single parameter or a list
      if ( strpos( "&", $video_params ) === FALSE ) {
        $params = explode( "&", $video_params );
      } else {
        $params = [ $video_params ];
      }

      for ( $i = 0; $i < count($params); $i++ ) {

        // convert regular video URLs that use &t=200 to start at 200sec into embed url style that uses &start=200 or &start=3m20s
        $params[$i] = preg_replace("/\At=/i", "start=", $params[$i]);

        // Disable autoplay
        $params[$i] = preg_replace("/\Aautoplay=/i", "no=", $params[$i]);

      }

      $video_params = implode( "&", $params );

    }

    // Build the proper URL depending on what the above regexps matched
    if ( $video_id != "" ) {

      $video_url = "https://www.youtube.com/embed/$video_id";

      if ( $video_params != "" ) {
        $video_url .= "?$video_params";
      }

    } elseif ( $playlist_url != "" ) {

      // Make sure none of the playlists can autoplay either
      $video_url = str_replace("autoplay=","no=", $playlist_url);

    } elseif ( $playlist_id != "" ) {

      // Build a playlist embed URL from the playlist id & params of a Playlist "share" URL
      // https://www.youtube.com/embed/videoseries?list=PLf7Pime6sgNUom_fs9wktBPMo9IrEHJT-
      $video_url = "https://www.youtube.com/embed/videoseries?list=$playlist_id";

      if ( $playlist_params != "" ) {
        $playlist_params = str_replace("autoplay=","no=", $playlist_params);

        $video_url .= "&$playlist_params";
      }

    } else {

      // If we matched nothing just pass the text back to be rendered in plain text
      return $url[1];

    }

    $video_url = htmlspecialchars($video_url);

    return "<iframe width=\"$video_width\" height=\"$video_height\" src=\"$video_url\" frameborder=\"0\" allowfullscreen></iframe>";

  }


  function soundcloud($href)
  {
    $host = parse_url($href[1]);
    $host = isset($host['host']) ? $host['host'] : "";

    if($host == "soundcloud.com" || $host == "www.soundcloud.com" ) {

      $height = 81;

      // player embed URL accepts the encoded soundcloud page URL as the param
      $href_encoded = rawurlencode($href[1]);
      $embed_src = "http://player.soundcloud.com/player.swf?url=$href_encoded";

      $embed  = "<object height=\"$height\" width=\"100%\">";
      $embed .= "<param name=\"wmode\" value=\"opaque\">";
      $embed .= "<param name=\"movie\" value=\"$embed_src\">";
      $embed .= "<param name=\"allowscriptaccess\" value=\"always\">";
      $embed .= "<embed allowscriptaccess=\"always\" height=\"$height\" src=\"$embed_src\" type=\"application/x-shockwave-flash\" width=\"100%\">";
      $embed .= "</object>";

      return $embed;
    }

    return $href[1];
  }

  function vimeo($href)
  {
    $host = parse_url($href[1]);
    $host = isset($host['host']) ? $host['host'] : "";

    // Convert Vimeo links http://vimeo.com/######### to player.vimeo.com/video/####### style and embed with their iframe code
    if($host == "vimeo.com")
    {
      $href = str_replace("vimeo.com","player.vimeo.com/video", $href[1]);
      $href.="?title=0&amp;byline=0&amp;portrait=0";

      return "<iframe src=\"$href\" width=\"425\" height=\"239\" frameborder=\"0\" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>";
    }

    return $href[1];
  }

  function run($string)
  {
    if(!$s = $string) return "";

    // remove the garbage
    $s = htmlentities($s,ENT_QUOTES,'UTF-8');

    // basic parse
    for($b=1;$b<count($this->bbc);$b++)
    {
      $bbcn = '#'.preg_quote($this->bbc[$b],'#')."(.*)".preg_quote($this->bbc[$b+1],'#').'#Uis'; // needle
      $bbcr = $this->rep[$b]."$1".$this->rep[++$b]; // replacement
      $s = preg_replace($bbcn,$bbcr,$s);
    }

    // do links
    $s = preg_replace_callback("#\[url\=(.*)\](.*)\[\/url\]#Ui",array(&$this,'prep_url_linktext'),$s);
    $s = preg_replace_callback("#\[url\](.*)\[\/url\]#Ui",array(&$this,'prep_url'),$s);
    $s = preg_replace_callback("#(^|\s|>)((http|https)://\w+[^\s\[\]\<]+)#i",array(&$this,'prep_url'),$s);

    // do media
    if($this->hidemedia)
    {
      $s = preg_replace("#\[img\](.*)\[\/img\]#Ui","<a href=\"$1\" class=\"link\" onclick=\"$(this).after('<img src=\\''+this.href+'\\' ondblclick=\\'window.open(this.src);return false\\'/>');$(this).remove();return false;\">IMAGE REMOVED CLICK TO VIEW</a>",$s);
      $s = preg_replace("#\[youtube\](.*)\[\/youtube\]#Ui","<a href=\"$1\" onclick=\"window.open(this.href); return false;\">YOUTUBE REMOVED CLICK TO VIEW</a>",$s);
      $s = preg_replace("#\[vimeo\](.*)\[\/vimeo\]#Ui","<a href=\"$1\" onclick=\"window.open(this.href); return false;\">VIMEO REMOVED CLICK TO VIEW</a>",$s);
      $s = preg_replace("#\[soundcloud\](.*)\[\/soundcloud\]#Ui","<a href=\"$1\" onclick=\"window.open(this.href); return false;\">SOUNDCLOUD REMOVED CLICK TO VIEW</a>",$s);
    }
    else
    {
      $s = preg_replace("#\[img\](.*)\[\/img\]#Ui","<img src=\"$1\" ondblclick=\"window.open(this.src);\"/>",$s);
      $s = preg_replace_callback("#\[youtube\](.*)\[\/youtube\]#Ui",array(&$this,'youtube'),$s);
      $s = preg_replace_callback("#\[vimeo\](.*)\[\/vimeo\]#Ui",array(&$this,'vimeo'),$s);
      $s = preg_replace_callback("#\[soundcloud\](.*)\[\/soundcloud\]#Ui",array(&$this,'soundcloud'),$s);
    }

    // start line break stuff
    $s = str_replace('<br />',NULL,$s);
    $s = nl2br(chop($s));

    // remove line breaks inside these tags
    $lbr = array(array("<pre>","</pre>"));

    foreach($lbr as $lb)
    {
      $lb1 = $lb[0];
      $lb2 = $lb[1];
      $lb1q = preg_quote($lb1,'#');
      $lb2q = preg_quote($lb2,'#');
      $lbn = "#".$lb1q."(.+?)".$lb2q."#sie";
      $s = preg_replace($lbn,"'".$lb1."'.str_replace('<br />','',str_replace('\\\"','\"','$1')).'".$lb2."'",$s);
      $s = preg_replace("#\<br \/\>(\r\n)".$lb1q."#i","\n".$lb1,$s);
      $s = preg_replace("#".$lb2q."\<br \/\>#i",$lb2,$s);
      $s = preg_replace("#".$lb2q."(\r\n)\<br \/\>#i",$lb2,$s);
    }
    // end line break stuff

    return $s;
  }
}
