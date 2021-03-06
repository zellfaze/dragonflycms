<?php
/*********************************************
  CPG Dragonfly™ CMS
  ********************************************
  Copyright © 2004 - 2014 by CPG-Nuke Dev Team
  http://dragonflycms.org

  Based on "All Info Block" by Alex Hession
   http://www.gnaunited.com

  Dragonfly is released under the terms and conditions
  of the GNU GPL version 2 or any later version
Encoding test: n-array summation ∑ latin ae w/ acute ǽ
********************************************************/
if (!defined('CPG_NUKE')) { exit; }

$K = \Dragonfly::getKernel();

$content = '<div style="text-align:center;"><img src="'.$K->IDENTITY->avatar_url.'" alt=""/><br/>'._BWEL;
if (is_user()) {
	$userinfo = $K->IDENTITY;
	$content .= ' <b>'.$userinfo->nickname.'</b></div>';
	if (\Dragonfly\Modules::isActive('Private_Messages')) {
		$pm = $userinfo->new_privmsg + $userinfo->unread_privmsg;
		$content .= '<a title="'._READSEND.'" href="'.URL::index('Private_Messages').'"><img src="'.DF_STATIC_DOMAIN.'images/blocks/email.gif" alt=""/></a>
		<a title="'._READSEND.'" href="'.URL::index('Private_Messages').'">'._INBOX.'</a>
		'._NEW.": <b>{$pm}</b><br/>\n";
	}
	$content .= '<a title="'._ACCOUNTOPTIONS.'" href="'.URL::index('Your_Account').'"><img src="'.DF_STATIC_DOMAIN.'images/blocks/logout.gif" alt=""/></a>
	<a title="'._ACCOUNTOPTIONS.'" href="'.URL::index('Your_Account').'">'._Your_AccountLANG.'</a><br/>
	<a title="'._LOGOUTACCT.'" href="'.htmlspecialchars(\Dragonfly\Identity::logoutURL()).'"><img src="'.DF_STATIC_DOMAIN.'images/blocks/login.gif" alt=""/></a>
	<a title="'._LOGOUTACCT.'" href="'.htmlspecialchars(\Dragonfly\Identity::logoutURL()).'">'._LOGOUT.'</a>';
} else {
	$content .= ' <b>'._ANONYMOUS.'</b></div>
	<hr/><form action="'.\URL::index('login&amp;auth=0').'" data-df-challenge="'.\Dragonfly\Output\Captcha::generateHidden().'"><div>';

	$auth_providers = \Poodle\Auth\Provider::getPublicProviders();
	$one_provider = (1 == count($auth_providers));
	if (!$one_provider) {
		$content .= '<script>function switchLoginProvider(s){
			var i=1, o;
			for (;i<10;++i) {
				o = Poodle.$("auth-provider-"+i);
				if (o) { o.style.display = (i==s.value)?"block":"none"; }
			}
		}</script><select name="provider" id="auth-provider" onchange="switchLoginProvider(this)">';
		foreach ($auth_providers as $provider) {
			$content .= '<option value="'.$provider['id'].'">'.$provider['name'].'</option>';
		}
		$content .= '</select>';
	}
	foreach ($auth_providers as $i => $provider) {
		$content .= '<div id="auth-provider-'.$provider['id'].'"'.(0<$i?' style="display:none"':'').'>';
		if ($one_provider) {
			$content .= '<input type="hidden" name="provider" value="'.$provider['id'].'"/>';
		}
		$c = new $provider['class']($provider);
		$f = $c->getAction();
		foreach ($f->fields as $field) {
			$content .= '<input type="'.$field['type'].'" name="auth['.$provider['id'].']['.$field['name'].']" id="'.$field['name'].'" placeholder="'.$field['label'].'"/><br/>';
		}
		$content .= '</div>';
	}

	if ($K->CFG->auth_cookie->allow) {
		$content .= '<label>
			<input type="checkbox" value="1" name="auth_cookie"/>
			'._LOGIN_REMEMBERME.'
		</label>';
	}

	// don't show register link unless allowuserreg is yes
	if ($url = \Dragonfly\Identity::getRegisterURL()) {
		$content .= '<input type="button" value="'._BREG.'" onclick="window.location=\''.htmlspecialchars($url).'\'"/>';
	}
	$content .= '
		<input type="hidden" name="redirect_uri" value="'.htmlspecialchars($_SERVER['REQUEST_URI']).'"/>
		<input type="submit" value="'._LOGIN.'" formmethod="post"/>
	</div></form>';
}
if (is_admin()) {
	$content .= '<br/>
	<a title="'._LOGOUTADMINACCT.'" href="'.htmlspecialchars(URL::admin('logout')).'"><img src="'.DF_STATIC_DOMAIN.'images/blocks/login.gif" alt=""/></a>
	<a title="'._LOGOUTADMINACCT.'" href="'.htmlspecialchars(URL::admin('logout')).'">'._ADMIN.' '._LOGOUT."</a><br/>\n";
}

$SQL = $K->SQL;

$memres = $SQL->query("SELECT u.user_id, u.username, s.module, s.url, u.user_allow_viewonline
	FROM {$SQL->TBL->session} AS s
	LEFT JOIN {$SQL->TBL->users} AS u ON u.user_id = s.identity_id
	WHERE guest = 0
	ORDER BY u.username");
$anonres = $SQL->query("SELECT module, url
	FROM {$SQL->TBL->session}
	WHERE guest = 1");
$online_num = array($memres->num_rows, $anonres->num_rows);
$online_num[2] = $online_num[0] + $online_num[1];

// number of members
list($numusers) = $SQL->uFetchRow("SELECT COUNT(*) FROM {$SQL->TBL->users}
	WHERE user_id > 1 AND user_level > 0");
// users registered today
$day = mktime(0,0,0,date('n'),date('j'),date('Y'));
list($reg_today) = $SQL->uFetchRow("SELECT COUNT(*) FROM {$SQL->TBL->users}
	WHERE user_regdate >= {$day}");
// users registered yesterday
list($reg_yesterday) = $SQL->uFetchRow("SELECT COUNT(*) FROM {$SQL->TBL->users}
	WHERE user_regdate < {$day} AND user_regdate >= ".($day-86400));
// latest member
list($lastuser) = $SQL->uFetchRow("SELECT username FROM {$SQL->TBL->users}
	WHERE user_active = 1 AND user_level > 0
	ORDER BY user_id DESC");

$content .= '<hr/>
<strong>'._BMEMP.':</strong><br/>
'._BLATEST.': <a href="'.htmlspecialchars(\Dragonfly\Identity::getProfileURL($lastuser)).'"><b>'.$lastuser.'</b></a><br/>
'._BTD.': <b>'.$reg_today.'</b><br/>
'._BYD.': <b>'.$reg_yesterday.'</b><br/>
'._BOVER.': <b>'.$numusers.'</b><br/>

<hr/>
<strong>'._BVISIT.':</strong><br/>
'._BMEM.': <b>'.$online_num[0].'</b><br/>
'._BVIS.': <b>'.$online_num[1].'</b><br/>
'._BTT.': <b>'.$online_num[2].'</b>

<hr/>
<strong>'._WHOWHERE.':</strong><br/>';
$hidden = 0;
if ($online_num[0] > 0) {
	$content .= '<b>'._BMEM.':</b><br/>';
	while ($onluser = $memres->fetch_assoc()) {
		if ($onluser['user_allow_viewonline'] || is_admin()) {
			$onluser['url'] = htmlspecialchars($onluser['url']);
			$content .= "<a href=\"".htmlspecialchars(\Dragonfly\Identity::getProfileURL($onluser['user_id']))."\">{$onluser['username']}</a>";
			$content .= " &gt; <a href=\"{$onluser['url']}\">{$onluser['module']}</a><br/>";
		} else {
			++$hidden;
		}
	}
}
if ($online_num[1] > 0) {
	$content .= '<b>'._BVIS.':</b><br/>';
	while ($onluser = $anonres->fetch_assoc()) {
		$onluser['url'] = htmlspecialchars($onluser['url']);
		$content .= "<a href=\"{$onluser['url']}\">{$onluser['module']}</a><br/>";
	}
}
if ($hidden > 0) {
	$content .= '<span class="content"><b>'._BHID.':</b></span> '.$hidden;
}
$memres->free();
$anonres->free();
