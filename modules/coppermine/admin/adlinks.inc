<?php
/*********************************************
  CPG Dragonfly™ CMS
  ********************************************
  Copyright © 2004 - 2005 by CPG-Nuke Dev Team
  http://www.dragonflycms.com

  Dragonfly is released under the terms and conditions
  of the GNU GPL version 2 or any later version
**********************************************/
if (!defined('CPG_NUKE')) { exit; }

if (can_admin(strtolower(($module)))) {
	$menuitems['_AMENU8'][$module]['URL'] = URL::admin($module);
	$menuitems['_AMENU8'][$module]['IMG'] = 'coppermine';
	$menuitems['_AMENU8'][$module]['MOD'] = $module;
}
