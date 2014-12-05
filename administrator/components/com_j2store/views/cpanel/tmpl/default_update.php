<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<div class="j2store_update row-fluid">
	<div class="well">
		<h3>
			<?php echo JText::_('J2STORE_CURRENT_VERSION'); ?>
			<span class="pull-right">
			<?php echo $this->row->version; ?>&nbsp;
			<?php echo (J2STORE_PRO==1)?'Professional':'Core'; ?>
			</span>
		</h3>

	</div>
	<div class="well">
		<h3>NewsFeed</h3>
		<?php
			if(J2STORE_PRO==1) {
				$newsfeed_link = "http://j2store.org/updates/newsfeed/j2store_{$this->row->version}_pro_newsfeed.html";
			} else {
				$newsfeed_link = "http://j2store.org/updates/newsfeed/j2store_{$this->row->version}_newsfeed.html";
			}
		?>

		<iframe
			src="<?php echo $newsfeed_link ?>">
		</iframe>
	</div>

	<div class="well">

		<h3>Credits</h3>
		<div>
			<p>
				Copyright &copy;
				<?php echo date('Y');?>
				-
				<?php echo date('Y')+5; ?>
				Sasivarnakumar / <a href="http://www.j2store.org"><b><span
						style="color: #000; display: inline;">J2</span><span
						style="color: #666666; display: inline;">Store</span> </b>.org</a>
			</p>
			<p>
				If you use J2Store, please post a rating and a review at the <a
					target="_blank"
					href="http://extensions.joomla.org/extensions/e-commerce/shopping-cart/19687">Joomla!
					Extensions Directory</a>.
			</p>
		</div>

	</div>

	<div class="well">
		<h3>Our Social Channels</h3>
		<div class="row-fluid">
			<div class="span5">
				<div class="fb_like">
				<h3>Like us on Facebook</h3>
				<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fj2store&amp;width=450&amp;height=21&amp;colorscheme=light&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;send=false&amp;appId=203937006428294" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe>
				</div>
			</div>

			<div class="span5">
				<div class="twitter_follow">
				<h3>Follow us in twitter</h3>
				<a href="https://twitter.com/j2store_joomla" class="twitter-follow-button" data-show-count="false">Follow @j2store_joomla</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
				</div>
			</div>

		</div>


	</div>

</div>
