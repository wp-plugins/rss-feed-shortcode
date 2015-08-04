jQuery('.rss-feed').each(function(){jQuery(this).load(ajaxurl,{'action':'rss_feed_shortcode_get_feed','src':jQuery(this).find('a').attr('href')})});
