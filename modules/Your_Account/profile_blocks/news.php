<?php
/*********************************************
  CPG Dragonfly™ CMS
  ********************************************
  Copyright © 2004 - 2009 by CPG-Nuke Dev Team
  http://dragonflycms.org

  Dragonfly is released under the terms and conditions
  of the GNU GPL version 2 or any later version
**********************************************/
if (!defined('CPG_NUKE')) { exit; }
if (is_user() && \Dragonfly\Modules::isActive('News')) {
	// Last 10 Submissions
	$result = $db->query("SELECT sid, title FROM {$db->TBL->stories} WHERE ptime<=".time()." AND informant='".Fix_Quotes($username)."' ORDER BY sid DESC LIMIT 10");
	if ($result->num_rows > 0) {
		$OUT = \Dragonfly::getKernel()->OUT;
		$OUT->assign_vars(array(
			'NEWS_TITLE' => $username.'\'s '._LAST10SUBMISSION,
		));
		while (list($sid, $title) = $result->fetch_row()) {
			$OUT->assign_block_vars('article', array(
				'URL'   => URL::index('News&file=article&sid='.$sid),
				'TITLE' => $title
			));
		}
		$OUT->display('your_account/blocks/news');
	}
}
