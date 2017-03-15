<?php
	$outputPath = 'nains/';
	if( file_exists($outputPath) )
		rmdir_recursive( $outputPath );
	mkdir( $outputPath );
	mkdir( $outputPath.'base' );
	mkdir( $outputPath.'core_modules' );
	mkdir( $outputPath.'nodejs' );
	mkdir( $outputPath.'other_modules' );
	mkdir( $outputPath.'nodejsmodules' );
	
	
	
	
	// base
	$lastPanel = null;
	$panels = glob('../Panel/*.zip');
	foreach ($panels as $panel)
		if( $lastPanel == null || version($lastPanel) < version($panel) )
			$lastPanel = $panel;
	copy($lastPanel, $outputPath.'base/'.basename($lastPanel) );

	
	// core_modules
	$lastCoreModules = array();
	$coreModules = glob_recursive('../Modules/!*.tgz');
	foreach ($coreModules as $coreModule)
		if( !isset( $lastCoreModules[ name($coreModule) ] ) || version($coreModule) > version($lastCoreModules[ name($coreModule) ]) )
			$lastCoreModules[ name($coreModule) ] = $coreModule;
	foreach( $lastCoreModules as $lastCoreModule )
		copy($lastCoreModule, $outputPath.'core_modules/'.basename($lastCoreModule) );


	// other_modules
	$lastModules = array();
	$modules = glob_recursive('../Modules/*.tgz');
	foreach ($modules as $module)
		if( (!isset( $lastModules[ name($module) ] ) || version($module) > version($lastModules[ name($module) ])) && type($module) == 'normal' )
			$lastModules[ name($module) ] = $module;
	foreach( $lastModules as $lastmodule )
		copy($lastmodule, $outputPath.'other_modules/'.basename($lastmodule) );
		

	// nodejs
	$lastNodeJs = null;
	$nodeJss = glob('../NodeJs/*.tar.xz');
	foreach ($nodeJss as $nodeJs)
		$lastNodeJs = $nodeJs;
	copy($lastNodeJs, $outputPath.'nodejs/'.basename($lastNodeJs) );
	
	// nodejs modules
	$nodeJsModules = glob_recursive('../NodeJsModules/*.npmbox');
	foreach( $nodeJsModules as $nodeJsModule )
		copy($nodeJsModule, $outputPath.'nodejsmodules/'.basename($nodeJsModule) );

	
	// install
	copy('../Scripts/install', $outputPath.'install' );
	
	
	echo
"
1: remove modules you dont want from 'nains/other_modules' folder
2: copy 'nains' folder to '/var/www/html/'
3: in linux terminal: cd /var/www/html/nains
4: in linux terminal: chmod +rwx install
5: in linux terminal: ./install
";
	
	
	function name($fname){
		$fname = basename($fname);
		$fname = substr($fname, 0, strpos($fname,'@') );
		if( type($fname) != 'normal' )
			$fname = substr($fname,1);
		return $fname;
		
	}
	
	function version($fname){
		$fname = basename($fname);
		$ver = substr($fname, strpos($fname,'@')+1 );
		$chr = strpos($ver, '#') === false? '.': '#';
		return substr($ver,0, strpos($ver,$chr) );
	}
	
	function type($fname){
		$fname = basename($fname);
		switch( substr($fname,0,1) ){
			case '_':
				return 'beta';
			case '!':
				return 'core';
			default:
				return 'normal';
		}
	}
	
	function glob_recursive($pattern, $flags = 0){
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
			$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
		return $files;
	}
	
	function rmdir_recursive($dir) {
		foreach(scandir($dir) as $file) {
			if ('.' === $file || '..' === $file) continue;
			if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
			else unlink("$dir/$file");
		}
		rmdir($dir);
	}
?>