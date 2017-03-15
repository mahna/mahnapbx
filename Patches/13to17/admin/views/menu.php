<?php
$out = '';

$username = (isset($_SESSION['AMP_user']->username) ? $_SESSION['AMP_user']->username : 'ERROR');

global $module_page;
$currentmodule = $module_page;


$alertclass = (check_reload_needed()==1?'alert':''); 
$masir = null; //zamani k module ha daran tuye menu sakhte mishan, por mishe

if( getor($_GET['extdisplay']) != 'process' ){
//$out.=otag('div','id="nai_sidebar_cont"');
	$mdls = nai_mymodulesadmin_getmodules_to_array(true);

	$out.=otag('div','id="nai_sidebar"');

		$out .= otag('div','class="search"');
		$out .= tag('input','type="text" placeholder="جست و جو در منو ها"');
		$out .= tag('span','class="icon-magnifying-glass dark s18 icon"');
		$out .= ctag('div');
		

		
		if($currentmodule==$module['name']){
			$masir = $module['category'] . ' <span class="sep"></span> ' .  $module['subcategory'] . ' <span class="sep"></span> ' .  $module['title'] ;
		}
		elseif( $_GET['display'] == 'modules' ){
			$masir = 'مدیریت ماژول‌ها';
		}
		elseif($currentmodule=='home'||$currentmodule=='index'){
			$masir = 'صفحه اصلی';
		}

		$out.=otag('ul','class="allmenu"');
			foreach($mdls as $category => $subcategories){

				$out.=otag('li','class="category"');
					if($category == 'مدیریت'){
						$out.=tag('div','class="header"',tag('span','class="icon-cog s25 icon"') . tag('span','class="text"',$category) );
					}
					else if($category == 'گزارش'){
						$out.=tag('div','class="header"',tag('span','class="icon-stats s25 icon"') . tag('span','class="text"',$category));
					}
					else if($category == 'نظارت'){
						$out.=tag('div','class="header"',tag('span','class="icon-eye s25 icon"') . tag('span','class="text"',$category));
					}
					else if($category == 'اپراتور'){
						$out.=tag('div','class="header"',tag('span','class="icon-tools s25 icon"') . tag('span','class="text"',$category));
					}
					if( $category == 'اپراتور' || $category == 'نظارت' || $category == 'گزارش' || $category == 'مدیریت') {
						$out.=otag('ul','class="zircategory"');
							$out.=otag('li','class="subcategory"');
							foreach($subcategories as $subcategory => $modules){
								if($subcategory == 'تلفن'){
									$out.=tag('div','class="header"',tag('span','class="icon-phone s18 icon"') . tag('span','class="text"',$subcategory) );
								}
								else if($subcategory == 'فکس'){
									$out.=tag('div','class="header"',tag('span','class="icon-printer s18 icon"') . tag('span','class="text"',$subcategory));
								}
								else if($subcategory == 'کاربران'){
									$out.=tag('div','class="header"',tag('span','class="icon-users2 s18 icon"') . tag('span','class="text"',$subcategory));
								}
								else if($subcategory == 'سیستم'){
									$out.=tag('div','class="header"',tag('span','class="icon-laptop s18 icon"') . tag('span','class="text"',$subcategory));
								}
								else if($subcategory == 'تنظیمات'){
									$out.=tag('div','class="header"',tag('span','class="icon-settings s18 icon"') . tag('span','class="text"',$subcategory));
								}
								else if($subcategory == 'یک‌پارچه'){
									$out.=tag('div','class="header"',tag('span','class="icon-chart s18 icon"') . tag('span','class="text"',$subcategory));
								}
								$itemNumber = 1;
								$out.=otag('ul','class="zirsubcategory"');
									foreach($modules as $module){
										$title = strtolower(_($module['title'])).' '.strtolower($module['name']);
										if( nai_check_this_user_can_access_to_this_module($username,$module['name'])!==false ){
											$out.=otag('a','href="'.$module['href'].'" data-item-number="'. $itemNumber++ .'"');
												$_ver = getor($module['xml']['content']['MODULE']['VERSION'],'');
												$_naiVer = getor($module['xml']['content']['MODULE']['NAIVER'],'');
												$_isbeta = false;
												if( strpos($_naiVer,'beta') !== false )
													$_isbeta = true;
												$_isDeveloping = false;
												if( strpos($_naiVer,'ing') !== false )
													$_isDeveloping = true;
												$_isOld = substr( strrev($module['name']),0,2) == 'bs'/* i mean sb */?true:false;
												$out.=tag('li','class="module' .($_isbeta?' beta':'').($_isDeveloping?' developing':'').($_isOld?' old':''). ($currentmodule==$module['name'] ? ' selected"':'"')." title=\"$title \n naiVer: $_naiVer\"", _($module['title']) );
											$out.=ctag('a');
											$count++;
										}

										if($currentmodule==$module['name']){
											$masir = $module['category'] . ' <span class="sep"></span> ' .  $module['subcategory'] . ' <span class="sep"></span> ' .  _($module['title']) ;
										}
									}
								$out.=ctag('ul');
							}
							$out.=ctag('li');
						$out.=ctag('ul');
					}
				$out.=ctag('li');
				
			}
		$out.=ctag('ul');
	$out.=ctag('div');
	//$out.=ctag('div');
	//$out.= tag('table','id="alert_in_page"',  tag('t','class="closebtn"','x') . tag('span','class="text"') );
	$out.= tag('div','id="nai_masir"',$masir);
	
	$out .= otag('table','id="alert_in_page"');
	$out .= ctag('table');

	echo $out;

}
?>
