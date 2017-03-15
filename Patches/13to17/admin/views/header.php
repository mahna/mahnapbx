
<?php
global $user;
$version			= get_framework_version();
$version_tag		= '?load_version=' . urlencode($version);
if ($amp_conf['FORCE_JS_CSS_IMG_DOWNLOAD']) {
	$this_time_append	= '.' . time();
	$version_tag 		.= $this_time_append;
} else {
	$this_time_append = '';
}

//set language to persian AND setting *NEW
$amp_conf['DISABLE_CSS_AUTOGEN'] = true;
$amp_conf['USE_GOOGLE_CDN_JS'] = false;
$_COOKIE['lang'] = 'fa_IR';
setcookie("lang", 'fa_IR', time()+365*24*60*60);

//html head
echo '<!DOCTYPE html>';
echo otag('html');
echo otag('head');

global $module_page;
$mdl = nai_mymodulesadmin_getmodule($module_page);

global $system_xml_file_address,$system_xml;

$panel_name = $system_xml['SYSTEM']['PANEL']['NAME'];
$panel_minname = $system_xml['SYSTEM']['PANEL']['MINNAME'];
$panel_logo = $system_xml['SYSTEM']['PANEL']['LOGO'];
$panel_titleicon = $system_xml['SYSTEM']['PANEL']['TITLEICON'];
if($mdl['title'])
	echo tag('title','', $panel_minname. ' | '. $mdl['title']);
else
	echo tag('title','', $panel_name);


// meta tags		
echo otag('meta','http-equiv="Content-Type" content="text/html;charset=utf-8"'); //no need to close (2 ta bud nemidunam chera)
echo otag('meta','http-equiv="X-UA-Compatible" content="chrome=1"'); //no need to close
echo otag('meta','name="robots" content="noindex"'); //no need to close
echo otag('meta','charset="UTF-8"'); //no need to close
echo otag('meta','name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"'); //no need to close
//echo otag('meta','rel="shortcut icon" href="' . $panel_titleicon . '"'); //no need to close
//<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		
//add the popover.css stylesheet if we are displaying a popover to override mainstyle.css styling
if ($use_popover_css) {
	$popover_css = $amp_conf['BRAND_CSS_ALT_POPOVER'] ? $amp_conf['BRAND_CSS_ALT_POPOVER'] : 'assets/css/popover.css';
	include_css($popover_css.$version_tag);
}


// plugins
include_plugin('jquery');
include_plugin('jqueryui');
include_plugin('socket.io');
include_plugin('chosen');
include_plugin('persianDatepicker');
include_plugin('tipper');
include_plugin('onoff');
include_plugin('maskedinput');
include_plugin('naiGrid');
include_plugin('naiJax');


// styles
include_css('assets/css/mainstyle.css');
include_css('assets/css/nainemom/icomoon.css');
include_css('assets/css/nainemom/nai_style.css');

// javascripts
/* include_js('assets/js/nainemom/nai_check_loaded.js'); */
if( ncl() ) include_js('assets/js/nainemom/nai_func.js');


// set theme
$theme = $user['theme']==''?'default':$user['theme'];
include_css('assets/css/nainemom/theme/'.$theme.'.css');


if( getor($_GET['nai_dialog']) == 'true' || getor($_GET['fw_popover']) == 1 ){
	include_css('assets/css/nainemom/nai_dialog.css');
}
	

echo ctag('head');

echo otag('body');
echo tag('span','class="spinner" id="nailoadspinner"');
		
//nai_header
$username = (isset($_SESSION['AMP_user']->username) ? $_SESSION['AMP_user']->username : 'ERROR');
if( !ncl() ) $username = 'ERROR';
$currentmodule = $display;

$alertclass = (check_reload_needed()==1?'alert':''); 
echo otag('div','id="nai_header"');
	echo otag('div',"id=\"logo\"");
		//echo tag('span','class="icon icon-logo_mahna s55 light "');
		if( ncl() ) echo stag('img',"class=\"image\" src=\"$panel_logo\"");
		echo tag('span','class="text" title="نسخه '. $system_xml['SYSTEM']['PANEL']['NAIVER'] .'"',$panel_name);
	echo ctag('div');
	
	if($username!='ERROR'){
		echo  otag('div','id="toppan"');
			echo otag('ul');
				
				echo otag('li','onclick="document.location=\'?display=index\'"');
						echo tag('span','class="icon-home2 icon light s28 clickable"');
				echo ctag('li');
				
				global $vl;
				$user_dpname = getor($user['username'],null);
				//$_dpname = $vl->match($user_dpname,'farsi')?$user_dpname:'منوی کاربری';
				$_dpname = $user_dpname;
				
				echo otag('li','id="menu_karbari"');
					echo tag('span','class="icon-user2 icon light s28 clickable"');
					//echo tag('span','class="text clickable"', $_dpname );
					echo otag('ul');
						echo otag('li','class="usrnm"');
							echo ucfirst($_dpname);
						echo ctag('li');
						echo otag('a',"href=\"?display=changepassword\"");
							echo otag('li');
								echo tag('span','class="icon-wrench icon light s22 clickable"').'تنظیمات کاربری';
							echo ctag('li');
						echo ctag('a');
						echo otag('a','id="user_logout" href="#"');
							echo otag('li');
								echo tag('span','class="icon-exit icon light s22 clickable"').'خروج';
							echo ctag('li');
						echo ctag('a');
					echo ctag('ul');
				echo ctag('li');
				$notifCount = 0;
				if($alertclass) $notifCount++;
				
				$asterisk_status = true;
				$check_asterisk = shell_exec("service asterisk status");
				if( strpos($check_asterisk,'stopped') !== false ) { $asterisk_status = false; $notifCount++; }
				
				$node_status = true;
				$check_node = shell_exec('ps aux | grep node');
				if( strpos($check_node, 'index.js') === false ) { $node_status = false; $notifCount++; }
				
				echo otag('li',"data-notifcount=\"$notifCount\" id=\"user_menu\"");
					if($notifCount>0) echo tag('span',"class=\"notif_count\" id=\"notif_count\" data-notifCount=\"$notifCount\"",$notifCount);
					echo tag('span','class="icon-earth icon light s28 clickable"');
					//echo tag('span','class="text clickable"','اعلان ها');
					echo otag('ul');
					
						if($asterisk_status==false){
							echo otag('a',"href=\"#\"");
								echo tag('li','',tag('span','class="icon-warning icon light s22 clickable"').'استریسک غیرفعال است!');
							echo ctag('a');
						}
						if($node_status==false){
							echo otag('a',"href=\"#\"");
								echo tag('li','',tag('span','class="icon-warning icon light s22 clickable"').'مانیتورینگ غیرفعال است!');
							echo ctag('a');
						}
						
						
						echo otag('a',"id=\"button_reload\" href=\"#\"");
							echo tag('li','',tag('span','class="icon-check-alt icon light s22 clickable"').'اعمال تغییرات');
						echo ctag('a');

						echo tag('li','id="nainonotif" class="'. ($notifCount > 0?'hidden':'') .'"',tag('span','class="icon-info icon light s22 clickable"').'هیج اعلانی موجود نیست!');


					echo ctag('ul');
				echo ctag('li');
				
				
				echo otag('li','title="سیستم"');
					echo tag('span','class="icon-wrench3 icon light s28 clickable"');
					//echo tag('span','class="text clickable"','سیستم');
					echo otag('ul');
						if(nai_myampusers_is_admin($username)){
							echo otag('a',"href=\"?display=modules\"");
								echo tag('li','',tag('span','class="icon-window icon light s22 clickable"').'مدیریت ماژول‌ها');
							echo ctag('a');
						}
						echo otag('a',"id=\"button_cache_reload\" href=\"config.php?display=$_GET[display]&do=". (nai_myampusers_is_admin($username)?'reloadcache':'reloadmycache') ."\"");
							echo tag('li','',tag('span','class="icon-reload-alt icon light s22 clickable"').'به‌روزرسانی حافظه‌نهان');
						echo ctag('a');
						
						if(nai_myampusers_is_admin($username)){
							echo otag('a',"id=\"button_macpl\" href=\"config.php?display=$_GET[display]&do=shutdownserver\" onclick=\"var sure = confirm('آیا از خاموش کردن سرور مطمئن هستید؟'); if(!sure) return false;\"");
								echo tag('li','',tag('span','class="icon-switch icon light s22 clickable"').'خاموش‌کردن سرور');
							echo ctag('a');
							
							echo otag('a',"id=\"button_macpl\" href=\"config.php?display=$_GET[display]&do=restartserver\" onclick=\"var sure = confirm('آیا از راه اندازی مجدد سرور مطمئن هستید؟'); if(!sure) return false;\"");
								echo tag('li','',tag('span','class="icon-reload-alt icon light s22 clickable"').'راه‌اندازی مجدد سرور');
							echo ctag('a');
							echo otag('a',"id=\"button_macpl\" href=\"config.php?display=$_GET[display]&do=macpl\" onclick=\"var sure = confirm('آیا از پیکربندی تلفن ها مطمئن هستید؟'); if(!sure) return false;\"");
								echo tag('li','',tag('span','class="icon-phone2 icon light s22 clickable"').'پیکربندی تلفن‌ها');
							echo ctag('a');
						}
					echo ctag('ul');
				echo ctag('li');
				
				
				if( $display != 'noauth' ) nai_basemodules_includes_header();
	
				
			echo ctag('ul');
			
			
		echo ctag('div');
	}

	
echo ctag('div'); // end nai_header