//<?php
/**
 * ダッシュボード・MODXニュース
 * 
 * ダッシュボードにMODXニュースを表示します。
 *
 * @category 	plugin
 * @version 	0.2
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@events OnManagerWelcomeRender,OnInterfaceSettingsRender
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base
 *
 * @author yama  / created: 2012/07/28
 */

include_once($modx->config['base_path'] . 'assets/plugins/dashboard/newsfeed.class.inc.php');
$modxnews = new MODXNEWS();

switch($modx->event->name)
{
	case 'OnInterfaceSettingsRender';
		$form = $modxnews->render_settings();
		$modx->event->output($form);
		break;
	case 'OnManagerWelcomeRender';
		$feedData = $modxnews->getfeeds();
		$modx->event->output($feedData);
		break;
	default:
		return;
}
