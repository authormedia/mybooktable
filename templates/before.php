<?php
$template = get_option('template');
//var_dump($template);

switch($template) {
	case 'twentyeleven':
		echo('<div id="primary"><div id="content" role="main">');
		break;
	case 'twentytwelve':
		echo('<div id="primary" class="site-content"><div id="content" role="main">');
		break;
	case 'twentythirteen':
		echo('<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">');
		break;
	case 'peddlar':
	case 'resort':
	case 'superstore':
	case 'theonepager':
	case 'appply':
	case 'hustle':
	case 'function':
	case 'definition':
	case 'chapters':
	case 'auld':
	case 'caffeinated':
	case 'unite':
	case 'coda':
	case 'apz':
	case 'inspire':
	case 'slanted':
	case 'cinch':
	case 'retreat':
	case 'sophisticatedfolio':
	case 'mystream':
	case 'royalle':
	case 'f0101':
	case 'digitalfarm':
	case 'therapy':
	case 'antisocial':
	case 'exposure':
	case 'bigeasy':
	case 'groovyblog':
	case 'mortar':
	case 'myweblog':
	case 'suitandtie':
	case 'groovyphoto':
	case 'productum':
	case 'groovyvideo':
	case 'bloggingstream':
	case 'forewordthinking':
	case 'cushy':
	case 'newsport':
	case 'geometric':
	case 'abstract':
	case 'ambience':
	case 'thick':
	case 'gothamnews':
	case 'openair':
	case 'papercut':
	case 'freshfolio':
	case 'vibrantcms':
	case 'proudfolio':
	case 'livewire':
	case 'flashnews':
	case 'newspress':
	case 'athena':
	case 'scrollider':
	case 'pixelpress':
	case 'capital':
	case 'mystile':
	case 'flipflop':
	case 'merchant':
	case 'smpl':
	case 'daybook':
	case 'sentient':
	case 'olya':
	case 'whitelight':
	case 'unsigned':
	case 'shelflife':
	case 'sliding':
	case 'beveled':
	case 'wikeasi':
	case 'currents':
	case 'emporium':
	case 'teamster':
	case 'argentum':
	case 'announcement':
	case 'buro':
	case 'woostore':
	case 'coquette':
	case 'empire':
	case 'editorial':
	case 'statua':
	case 'briefed':
	case 'kaboodle':
	case 'savinggrace':
	case 'premiere':
	case 'boldnews':
	case 'biznizz':
	case 'simplicity':
	case 'deliciousmagazine':
	case 'elefolio':
	case 'continuum':
	case 'diner':
	case 'crisp':
	case 'sealight':
	case 'inspire':
	case 'spectrum':
	case 'diarise':
	case 'boast':
	case 'postcard':
	case 'delegate':
	case 'canvas':
	case 'cityguide':
	case 'optimize':
	case 'backstage':
	case 'dailyedition':
	case 'headlines':
	case 'coffeebreak':
	case 'featurepitch':
	case 'thejournal':
	case 'object':
	case 'aperture':
	case 'thestation':
	case 'busybee':
	case 'wootube':
	case 'overeasy':
	case 'gazette':
	case 'freshnews':
	case 'faultpress':
	case 'supportpress':
	case 'listings':
	case 'estate':
		if(function_exists('woo_content_before')) { woo_content_before(); }
		echo('<div id="content" class="col-full"><div id="main-sidebar-container">');
		if(function_exists('woo_main_before')) { woo_main_before(); }
		echo('<div id="main" class="col-left">');
		break;
	case 'Nova':
		echo('<div id="main-content"><div class="container clearfix"><div id="entries-area"><div id="entries-area-inner"><div id="entries-area-content" class="clearfix"><div id="content-area">');
		break;
	case 'MyProduct':
		echo('<div id="content-left"><div class="entry post clearfix">');
		break;
	case 'DeepFocus':
		//echo('<div id="content-full"><div id="hr"><div id="hr-center"><div id="intro"><div class="center-highlight"><div class="container">');
		//get_template_part('includes/breadcrumbs');
		//echo('</div></div></div></div></div>');
		echo('<div class="center-highlight"><div class="container"><div id="content-area" class="clearfix"><div id="left-area"><div class="entry clearfix post">');
		break;
	case 'SimplePress':
		echo('<div id="content"><div class="content_wrap"><div class="content_wrap"><div id="posts">');
		break;
	default:
		echo('<div id="container"><div id="content" role="main">');
		break;
}