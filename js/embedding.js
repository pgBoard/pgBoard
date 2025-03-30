function loadScript(src) {
  var script = document.createElement('script');
  script.async = true;
  script.setAttribute('src', src);
  document.head.appendChild(script);
  return script;
}

function embedInstagram() {
  // If the Instagram JS is already loaded on the page,
  if (typeof instgrm !== 'undefined') {
    instgrm.Embeds.process();
  } else {
    // Otherwise, load the Instagram JS.
    var script = loadScript('https://www.instagram.com/embed.js');
    // When the lib is done loading,
    $(script).load(function() {
      // create the Instagram embeds.
      instgrm.Embeds.process();
    });
  }
}

function embedTikTok() {
  // The :empty selector ensures we don't try re-embedding TikToks that have
  // already been embedded.
  $('.tiktok-embed:empty').each(function() {
    var $el = $(this);
    if ($el.attr('cite')) {
      // In addition to regular URLs like
      // https://www.tiktok.com/@frankiedrago/video/7301504666934824234
      // (which contain the video ID in the last pathname component), TikTok
      // has short URLs (e.g. https://www.tiktok.com/t/ZT8kgU74r/) which don't
      // contain a video ID. We have to use TikTok's oEmbed API to get the
      // actual video ID.
      var url = new URL('https://www.tiktok.com/oembed');
      url.searchParams.append('url', $el.attr('cite'));
      $.ajax({
        url: url.toString(),
        cache: false,
        error: function() {
          // oEmbed will return 400 sometimes (if a video isn't found?). In
          // these cases, just display the originally-embedded URL.
          $el
            .css('font-style', 'normal')
            .removeClass('tiktok-embed')
            .html($el.attr('cite'));
        },
        success: function(data) {
          // Add the video ID as a data attribute to the target element,
          if (data.embed_product_id) {
            $el.attr('data-video-id', data.embed_product_id);
            // add a child section element,
            $el.html('<section></section>');
            // and load the embed script with a cache buster. TikTok makes a
            // window.tiktokEmbed object available, but there's no docs on
            // manually initializing embeds after the script loads. The cb
            // querystring params ensures it re-loads and initializes.
            loadScript('https://www.tiktok.com/embed.js?cb=' + Date.now());
          }
        }
      });
    }
  });
}

function createTweetEmbeds() {
  // The :empty selector ensures we don't try re-embedding tweets that have
  // already been embedded.
  $('.embedded-tweet:empty').each(function() {
    twttr.widgets.createTweet(
      $(this).attr('data-tweet-id'),
      this,
      { dnt: true, theme: 'dark' }
    );
  });
}

function embedTweet() {
  // If the Twitter JS is already loaded on the page,
  if (typeof twttr !== "undefined") {
    // just create the tweet embeds. The :empty selector avoids re-embedding
    // tweets that have already been embedded.
    createTweetEmbeds();
  } else {
    // Otherwise, load the Twitter JS.
    var script = loadScript('https://platform.twitter.com/widgets.js');
    // When the lib is done loading,
    $(script).load(function() {
      // create the tweet embeds.
      createTweetEmbeds();
    });
  }
}

$(document).on('ajaxComplete', function (_event, _jqXHR, ajaxOptions) {
  // This is called every time jQuery's XHR finishes loading, e.g. when a
  // post is previewed or 'load new posts' is clicked. However, we don't want
  // to do anything when the TikTok oEmbed XHR call completes.
  if (!ajaxOptions.url.startsWith('https://www.tiktok.com/oembed')) {
    embedInstagram();
    embedTikTok();
    embedTweet();
  }
});

$(document).ready(function() {
  embedInstagram();
  embedTikTok();
  embedTweet();
});
