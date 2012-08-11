<?php
 /*
 *  MODx Manager Home Page Implmentation by pixelchutes (www.pixelchutes.com)
 *  Based on kudo's kRSS Module v1.0.72
 *
 *  Written by: kudo, based on MagpieRSS
 *  Contact: kudo@kudolink.com
 *  Created: 11/05/2006 (November 5)
 *  Updated: 2012-08-11 yama(yamamoto@kyms.jp)
 *  For: MODX (modx.com)
 *  Name: kRSS
 *  Version (MODx Module): 1.0.72
 *  Version (Magpie): 0.72
 */
class MODXNEWS {

	function MODXNEWS()
	{
	}
	
	function getfeeds()
	{
		global $modx;
		
		$_lang = $this->getlang();
		
		$urls['modx_news_content']             = $modx->config['rss_url_news'];
		$urls['modx_security_notices_content'] = $modx->config['rss_url_security'];
		
		$feedData = $this->fetchrss($urls);
		
		// modx news
		$ph['modx_news']         = $_lang["modx_news_tab"];
		$ph['modx_news_title']   = $_lang["modx_news_title"];
		$ph['modx_news_content'] = $feedData['modx_news_content'];
		
		// security notices
		$ph['modx_security_notices']         = $_lang["security_notices_tab"];
		$ph['modx_security_notices_title']   = $_lang["security_notices_title"];
		$ph['modx_security_notices_content'] = $feedData['modx_security_notices_content'];
		
		$block = $this->tpl();
		return $modx->parsePlaceholder($block,$ph);
	}
	
	function fetchrss($urls)
	{
		global $modx;
		
		$itemsNumber = '3';
		
		/* End of configuration
		NO NEED TO EDIT BELOW THIS LINE
		---------------------------------------------- */
		
		// include MagPieRSS
		require_once($modx->config['base_path'] . 'assets/plugins/dashboard/rss/rss_fetch.inc');
		
		$feedData = array();
		$itemtpl = '<li><a href="[+link+]" target="_blank">[+title+]</a> - <b>[+pubdate+]</b><br />[+description+]</li>';
		
		// create Feed
		foreach ($urls as $section=>$url)
		{
			if(!$url)
			{
				$feedData[$section] = ' - ';
				continue;
			}
			$output = '';
			// While getting RSS, SESSION is closed temporarily.  
			if ( !headers_sent() )
			{
				$tmp_sessionname=session_name();
				session_write_close();
			}
			$rss = @fetch_rss($url);
			if ( isset($tmp_sessionname) )
			{
				session_start($tmp_sessionname);
			}
			if( !$rss )
			{
				$feedData[$section] = 'Failed to retrieve ' . $url;
				continue;
			}
			$output .= '<ul>';
			
			$items = array_slice($rss->items, 0, $itemsNumber);
			foreach ($items as $item)
			{
				$href    = $item['link'];
				$item['pubdate'] = $modx->toDateFormat(strtotime($item['pubdate']));
				$description = strip_tags($item['description']);
				if (strlen($description) > 199)
				{
					$description = mb_substr($description, 0, 200);
					$description .= $modx->parsePlaceholder('...<br />Read <a href="[+link+]" target="_blank">more</a>.',$item);
				}
				$output .= $modx->parsePlaceholder($itemtpl,$item);
			}
			$output .= '</ul>';
			$feedData[$section] = $output;
		}
		return $feedData;
	}
	
	function tpl()
	{
		$block = <<< EOT
<div class="tab-page" id="tabNews" style="padding-left:0; padding-right:0">
<!-- modx news -->
	<h2 class="tab">[+modx_news+]</h2>
	<script type="text/javascript">tpPane.addTabPage( document.getElementById( "tabNews" ) );</script>
	<div class="sectionHeader">[+modx_news_title+]</div>
	<div class="sectionBody">
		[+modx_news_content+]
	</div>
<!-- security notices -->
	<div class="sectionHeader">[+modx_security_notices_title+]</div>
	<div class="sectionBody">
		[+modx_security_notices_content+]
	</div>
</div>
EOT;
		return $block;
	}

	function render_settings()
	{
		global $modx;
		
		$_ = file_get_contents($modx->config['base_path'] . 'manager/actions/mutate_settings.dynamic.php');
		if(strpos($_, 'rss_url_news_title')!==false) return;
		
		$ph = $this->getlang();
		$ph['form_rss_url_news']     = $this->form_text('rss_url_news',$rss_url_news);
		$ph['form_rss_url_security'] = $this->form_text('rss_url_security',$rss_url_news);
		$tpl = <<< EOT
<tr>
	<th>[+rss_url_news_title+]</th>
	<td>
		[+form_rss_url_news+]<br />
		[+rss_url_news_message+]
	</td>
</tr>
<tr>
	<th>[+rss_url_security_title+]</th>
	<td>
		[+form_rss_url_news+]<br />
		[+rss_url_security_message+]
	</td>
</tr>
EOT;
		return $modx->parsePlaceholder($tpl,$ph);
	}
	
	function form_text($name,$value,$maxlength='255',$add='',$readonly=false)
	{
		if($readonly) $readonly = ' disabled';
		if($add)      $add = ' ' . $add;
		if($maxlength<=10) $maxlength = 'maxlength="' . $maxlength . '" style="width:' . $maxlength . 'em;"';
		else               $maxlength = 'maxlength="' . $maxlength . '"';
		return '<input onchange="documentDirty=true;" type="text" ' . $maxlength . ' name="' . $name . '" value="' . $value . '"' . $readonly . $add . ' />';
	}

	function getlang()
	{
		global $modx;
		
		$lang_dir = $modx->config['base_path'] . 'assets/plugins/dashboard/langs/';
		if(is_file($lang_dir . $modx->config['manager_language'] . '.inc.php'))
		{
			include_once($lang_dir . $modx->config['manager_language'] . '.inc.php');
		}
		else
		{
			include_once($lang_dir . 'english.inc.php');
		}
		
		return $_lang;
	}
}
