=== Take the Control of your Content! ===
Contributors: Philippe Gras
Donate link: http://www.avoirun.com/
Tags: marketing, display, promotion, post, posts, content, HTML, admin, shortcode, ad, ads, plugin, form, embed, video, hack, call to action
Requires at least: 3.6
Tested up to: 4.1
Stable tag: 0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays HTML, ads, forms or javascript before, after, or anywhere in the content of your posts.

== Description ==
Addme lets you take control of your content by hacking a well-known WP function to filter many elements of your CMS. In this case, we will filter the content of your posts with a simple HTML form.

You can create as text strings, HTML or javascript blocks of scripts you want. A new form will be displayed when the previous one is validated. You have several options to display all your add-ons in the content:

[youtube https://www.youtube.com/watch?v=xhRqkj3d0Go]

**Display options:**

1. no display
1. before content
1. after the content
1. around the post
1. upon the last paragraph
1. after the first paragraph
1. randomly in post content
1. twice wrapped inner text
1. upon a video
1. under a video
1. around a video	

**Demo:**

* Go to the [Facebook page](https://www.facebook.com/wp.addme) to watch a video and screenshots of Addme users sites.
* Post one more at your turn with a link to one of your posts ;-) when you have finished to configure your plugin!

== Installation ==
1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. go to the Settings > Add Me section to fill the forms. **That's all!**

== Frequently Asked Questions ==

= What is the priority form field for? =

The priority defines the precedence of 2 displays against the content, as described in the [Wordpress Codex](http://codex.wordpress.org/Function_Reference/add_filter "Function Reference/add filter &laquo; WordPress Codex") for the function:
`<?php add_filter( $tag, $function_to_add, $priority, $accepted_args ); ?>`

Larger the number, the more your stuff will be far away of the post's content, compared to other blocks.

If you want to display 2 HTML blocks around the content with priorities 10 and 11 for instance, you'll see: 11, 10, content, 10, 11.

== Screenshots ==
1. A view of the admin panel from the plugin Addme
2. An example of two HTML blocks after the content

== Changelog ==

= 0.1 =
* First release.

== Upgrade Notice ==
Nothing to fix, in the lack of previous releases.