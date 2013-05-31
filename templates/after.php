<?php
$template = get_option('template');

switch($template) {
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
		echo('</div><!-- /#main -->');
		if(function_exists('woo_main_after')) { woo_main_after(); }
		get_sidebar();
		echo('</div><!-- /#main-sidebar-container -->');
		get_sidebar('alt');
		echo('</div><!-- /#content -->');
		if(function_exists('woo_content_after')) { woo_content_after(); }
		break;
	case 'Nova':
		echo('</div>');
		get_sidebar();
		echo('</div></div></div></div></div>');
		break;
	case 'MyProduct':
		echo('</div></div>');
		get_sidebar();
		break;
	case 'DeepFocus':
		echo('</div></div><!-- end #left-area -->');
		get_sidebar();
		echo('</div><!-- end #content-area --></div> <!-- end .container -->');
		break;
	case 'SimplePress':
		echo('</div><!-- #posts -->');
		get_sidebar();
		echo('</div><!-- .content_wrap --></div><!-- .content_wrap --></div><!-- #content --></div><!-- .wrapper -->');
		break;
	default:
		echo('</div></div>');
		get_sidebar();
		break;
}