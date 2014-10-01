<?php

//Writing
$writing = wp_insert_term('Writing', 'mbt_genre', array('slug' => 'writing'));
if(!is_wp_error($writing)) { $writing_id = array($writing['term_id']); }

//Randy Ingermanson
$ringermanson = wp_insert_term('Randy Ingermanson', 'mbt_author', array('slug' => 'ringermanson'));
if(!is_wp_error($ringermanson)) { $ringermanson_id = array($ringermanson['term_id']); }

//Writing Fiction for Dummies
$post_id = wp_insert_post(array(
	'post_title' => 'Writing Fiction for Dummies',
	'post_content' => 'So you want to write a novel? Great! That\'s a worthy goal, no matter what your reason. But don\'t settle for just writing a novel. Aim high. Write a novel that you intend to sell to a publisher. <em>Writing Fiction for Dummies</em> is a complete guide designed to coach you every step along the path from beginning writer to royalty-earning author. Here are some things you\'ll learn in Writing Fiction for Dummies:Strategic Planning: Pinpoint where you are on the roadmap to publication; discover what every reader desperately wants from a story; home in on a marketable category; choose from among the four most common creative styles; and learn the self-management methods of professional writers.Writing Powerful Fiction: Construct a story world that rings true; create believable, unpredictable characters; build a strong plot with all six layers of complexity of a modern novel; and infuse it all with a strong theme.Self-Editing Your Novel: Psychoanalyze your characters to bring them fully to life; edit your story structure from the top down; fix broken scenes; and polish your action and dialogue.Finding An Agent and Getting Published: Write a query letter, a synopsis, and a proposal; pitch your work to agents and editors without fear. Writing Fiction For Dummies takes you from being a writer to being an author. It can happen—if you have the talent and persistence to do what you need to do.',
	'post_excerpt' => 'The Most Wished For book in the Fiction Writing Reference category on Amazon is Writing Fiction for Dummies, the complete guide for writing and selling your novel.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($writing)) { wp_set_post_terms($post_id, $writing_id, "mbt_genre"); }
if(!is_wp_error($ringermanson)) { wp_set_post_terms($post_id, $ringermanson_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:106:"http://www.barnesandnoble.com/w/writing-fiction-for-dummies-randy-ingermanson/1100297881?ean=9780470530702";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:78:"http://www.amazon.com/Writing-Fiction-Dummies-Randy-Ingermanson/dp/0470530707/";}}'));



//Web Design
$webdesign = wp_insert_term('Web Design', 'mbt_genre', array('slug' => 'web-design'));
if(!is_wp_error($webdesign)) { $webdesign_id = array($webdesign['term_id']); }

//Christopher Schmitt
$cschmitt = wp_insert_term('Christopher Schmitt', 'mbt_author', array('slug' => 'cschmitt'));
if(!is_wp_error($cschmitt)) { $cschmitt_id = array($cschmitt['term_id']); }

//Designing Web & Mobile Graphics
$post_id = wp_insert_post(array(
	'post_title' => 'Designing Web & Mobile Graphics',
	'post_content' => 'Graphics are key to the user experience of online content, especially now that users are accessing that content on a multitude of devices: smartphones, tablets, laptops, and desktops. This book provides foundational methodology for optimal use of graphics that begins with HTML and CSS, and delves into the worlds of typography, color, transparency, accessibility, imagery, and layout for optimal delivery on all the different devices people use today.It serves beginners and intermediate web builders alike with a complete foundation needed to create successful illustrative and navigational imagery for web and mobile. Coverage includes:<ul>	<li>lessons on typography, icons, color, and images</li>	<li>the latest information on HTML5, CSS3, and other modern technologies</li>	<li>in-depth exploration of image formats: GIF, PNG, JPEG, and SVG</li>	<li>ways to employ adaptive strategies for responsive web design</li></ul>',
	'post_excerpt' => 'Designing Web & Mobile Graphics provides foundational methodology for optimal use of graphics that begins with HTML and CSS, and delves into the worlds of typography, color, transparency, accessibility, imagery, and layout for optimal delivery on all the different devices people use today.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($webdesign)) { wp_set_post_terms($post_id, $webdesign_id, "mbt_genre"); }
if(!is_wp_error($cschmitt)) { wp_set_post_terms($post_id, $cschmitt_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:114:"http://www.barnesandnoble.com/w/designing-web-and-mobile-graphics-christopher-schmitt/1111631892?ean=9780321858542";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:78:"http://www.amazon.com/Designing-Web-Mobile-Graphics-Fundamental/dp/0321858549/";}}'));



//Inspirational
$inspirational = wp_insert_term('Inspirational', 'mbt_genre', array('slug' => 'inspirational'));
if(!is_wp_error($inspirational)) { $inspirational_id = array($inspirational['term_id']); }

//N. J. Lindquist
$njlindquist = wp_insert_term('N. J. Lindquist', 'mbt_author', array('slug' => 'njlindquist'));
if(!is_wp_error($njlindquist)) { $njlindquist_id = array($njlindquist['term_id']); }

//A Second Cup of Hot Apple Cider
$post_id = wp_insert_post(array(
	'post_title' => 'A Second Cup of Hot Apple Cider',
	'post_content' => '<em>A Second Cup of Hot Apple Cider</em> is the follow-up to the bestseller,<em> Hot Apple Cider: Words to Stir the Heart and Warm the Soul</em>. <em>A Second Cup</em> won the 2012 Christian Small Publishers Gift Book Award Winner along with six 2012 The Word Awards and seven Awards of Merit. Midwest Book Review called <em>A Second Cup</em> a "reminder that there is something good in the world." <em>Faith Today</em> magazine\'s review said, "Some books surprise you with their ability to take your breath away... The short selections make this a perfect book for even indifferent readers. It would be a fabulous addition to an office waiting room, your bedside table, briefcase, backpack or purse. But be sure to buy more than one, for you will probably have the urge to share this gem of a collection with others." Please note that there is also a Discussion Guide for the book, with questions related to each story. Each contributor has supplied questions about his or her piece to help readers think further about the issues raised, enjoy stimulating discussions, and share ideas and meaningful experiences. Foreword by Ellen Vaughn. All books are available in paperback as well as e-pub and mobi formats.',
	'post_excerpt' => 'A collection of over 50 true stories, fictional short stories, and poems by 37 writers whose work is distinguished by honesty and vulnerability, combined with encouragement and hope.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($inspirational)) { wp_set_post_terms($post_id, $inspirational_id, "mbt_genre"); }
if(!is_wp_error($njlindquist)) { wp_set_post_terms($post_id, $njlindquist_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:106:"http://www.barnesandnoble.com/w/a-second-cup-of-hot-apple-cider-n-j-lindquist/1115136868?ean=9780978496319";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:111:"http://www.amazon.com/Second-Cup-Hot-Apple-Cider/dp/0978496310/ref=sr_1_3?s=books&ie=UTF8&qid=1366743096&sr=1-3";}}'));



//Michael Ryan James
$mrjames = wp_insert_term('Michael Ryan James', 'mbt_author', array('slug' => 'mrjames'));
if(!is_wp_error($mrjames)) { $mrjames_id = array($mrjames['term_id']); }

//How to Make Money from Writing Online
$post_id = wp_insert_post(array(
	'post_title' => 'How to Make Money from Writing Online',
	'post_content' => 'The Market for Online Writers is Wide Open. But to Succeed It Takes Resilience, Creative Thinking and a Solid Plan. As an online freelance writer, can you really... Work from home and set your own schedule? Build an active list of loyal clients who are located around the world? Become known for a specific, in-demand niche that has customers clamoring for your services? Realize virtually unlimited income from a variety of sources? Yes, all this and much more! <em>How To Make Money From Writing Online</em> by Michael Ryan James is a practical, effective guide for people looking to break into the world of writing online and for experienced writers who want to know how to add online freelancing to their portfolio of services. Inside <em>How To Make Money From Writing Online</em> you will learn: How to distinguish yourself from the competition by tapping into what makes your approach to freelance writing unique and valuable. Tips and examples for setting up your work space, creating a schedule and managing your time to maximize your productivity and profitability. Ways to uncover hidden markets and a variety of income streams that will help your online writing business thrive. Strategies for attracting and keeping more clients. Secrets of delivering outstanding customer service. Pricing strategies to make sure you get paid what you\'re worth. And much more. The market for online freelance writers is exploding. And the writers who succeed are those who commit to delivering outstanding results for the clients. <em>How To Make Money From Writing Online</em> details how you can set yourself apart from the crowd and build a lasting career as an online freelance writer quickly and effectively.',
	'post_excerpt' => 'Practical, effective guide for people looking to break into the world of writing online and for experienced writers who want to know how to add online freelancing to their portfolio of services.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($writing)) { wp_set_post_terms($post_id, $writing_id, "mbt_genre"); }
if(!is_wp_error($mrjames)) { wp_set_post_terms($post_id, $mrjames_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:1:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:35:"http://www.amazon.com/dp/B008R1F446";}}'));



//Thriller
$thriller = wp_insert_term('Thriller', 'mbt_genre', array('slug' => 'thriller'));
if(!is_wp_error($thriller)) { $thriller_id = array($thriller['term_id']); }

//R. E. McDermott
$remcdermott = wp_insert_term('R. E. McDermott', 'mbt_author', array('slug' => 'remcdermott'));
if(!is_wp_error($remcdermott)) { $remcdermott_id = array($remcdermott['term_id']); }

//Deadly Straits
$post_id = wp_insert_post(array(
	'post_title' => 'Deadly Straits',
	'post_content' => 'In the tradition of Clancy and Cussler, R.E. McDermott delivers a thriller to rival the masters. When marine engineer and very part-time spook Tom Dugan becomes collateral damage in the War on Terror, he\'s not about to take it lying down. Falsely implicated in a hijacking, he\'s offered a chance to clear himself by helping the CIA snare their real prey, Dugan\'s best friend, London ship owner Alex Kairouz. Reluctantly, Dugan agrees to go undercover in Alex\'s company, despite doubts about his friend\'s guilt. Once undercover, Dugan\'s steadfast refusal to accept Alex\'s guilt puts him at odds not only with his CIA superiors, but also with a beautiful British agent with whom he\'s romantically involved. When a tanker is found adrift near Singapore with a dead crew, and another explodes in Panama as Alex lies near death after a suspicious suicide attempt, Dugan is framed for the attacks. Out of options, and convinced the attacks are prelude to an even more devastating assault, Dugan eludes capture to follow his last lead to Russia, only to be shanghaied as an \'advisor\' to a Russian Spetsnaz unit on a suicide mission. <em>Deadly Straits</em> is a non-stop thrill ride, from London streets, to the dry docks of Singapore, to the decks of the tankers that feed the world\'s thirst for oil, with stops along the way in Panama, Langley, Virginia, and Teheran. Richly spiced with detail from the author\'s 30 years sailing, building, and repairing ships worldwide, it is, in the words of one reviewer, "fast-paced, multilayered and gripping."',
	'post_excerpt' => 'When marine engineer and very part-time spook Tom Dugan becomes collateral damage in the War on Terror, he\'s not about to take it lying down. Deadly Straits is a non-stop thrill ride, fast-paced, multilayered and gripping.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($thriller)) { wp_set_post_terms($post_id, $thriller_id, "mbt_genre"); }
if(!is_wp_error($remcdermott)) { wp_set_post_terms($post_id, $remcdermott_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:88:"http://www.barnesandnoble.com/w/deadly-straits-re-mcdermott/1103871471?ean=9780983741701";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:68:"http://www.amazon.com/Deadly-Straits-Dugan-Novel-ebook/dp/B0057AMO2A";}}'));



//Mary DeMuth
$mdemuth = wp_insert_term('Mary DeMuth', 'mbt_author', array('slug' => 'mdemuth'));
if(!is_wp_error($mdemuth)) { $mdemuth_id = array($mdemuth['term_id']); }

//The 11 Secrets of Getting Published
$post_id = wp_insert_post(array(
	'post_title' => 'The 11 Secrets of Getting Published',
	'post_content' => 'See how easily you can learn the secrets of getting published! Frustrated by how much there is to learn to finally see your name in print with a big publishing house? Mired in confusion about your next steps? An accomplished nonfiction freelancer and novelist with 11 traditionally published books under her author belt, Mary DeMuth understands the twists and turns of the publishing industry. She answers the question, "How can I get published?" by pulling 11 Secrets from her popular blog, Wannabepublished. Mary teaches you how to: * Craft the kind of query letter that gets you noticed by industry professionals. * Write stronger, powerful, attention grabbing prose. * Create effective writing routines to meet your daily and weekly goals. * Navigate a writing conference with confidence. * Find and woo an agent.',
	'post_excerpt' => 'Find the 11 secrets to getting published.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($writing)) { wp_set_post_terms($post_id, $writing_id, "mbt_genre"); }
if(!is_wp_error($mdemuth)) { wp_set_post_terms($post_id, $mdemuth_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:104:"http://www.barnesandnoble.com/w/11-secrets-of-getting-published-mary-demuth/1102378897?ean=2940012611758";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:151:"http://www.amazon.com/gp/product/098343672X/ref=as_li_ss_tl?ie=UTF8&tag=wwwrelevantpr-20&linkCode=as2&camp=1789&creative=390957&creativeASIN=098343672X";}}'));



//Christian Living
$christianliving = wp_insert_term('Christian Living', 'mbt_genre', array('slug' => 'christian-living'));
if(!is_wp_error($christianliving)) { $christianliving_id = array($christianliving['term_id']); }

//Nancy Grisham
$ngrisham = wp_insert_term('Nancy Grisham', 'mbt_author', array('slug' => 'ngrisham'));
if(!is_wp_error($ngrisham)) { $ngrisham_id = array($ngrisham['term_id']); }

//Thriving: Trusting God for Life to The Fullest
$post_id = wp_insert_post(array(
	'post_title' => 'Thriving: Trusting God for Life to The Fullest',
	'post_content' => 'Jesus made believers a bold promise: life and life to the fullest. He offers us more than just barely getting by when challenges come our way. But that kind of life doesn\'t happen automatically. Thriving equips you to live the abundant life. Using personal stories and biblical insights, Nancy Grisham shows you how to appropriate Jesus\' promise in your own life, even in the midst of difficult circumstances. Each chapter concludes with a practical reflection and application section perfect for individual study or small group discussions.',
	'post_excerpt' => 'Jesus promised life and life to the fullest. But it\'s not automatic. Thriving equips you to live the abundant life – even in difficult circumstances. Chapters include practical questions for individual study or small group discussion.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($christianliving)) { wp_set_post_terms($post_id, $christianliving_id, "mbt_genre"); }
if(!is_wp_error($ngrisham)) { wp_set_post_terms($post_id, $ngrisham_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:83:"http://www.barnesandnoble.com/w/thriving-nancy-grisham/1113451228?ean=9780801015434";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:134:"http://www.amazon.com/Thriving-Trusting-God-Life-Fullest/dp/080101543X/ref=sr_1_1?ie=UTF8&qid=1366995488&sr=8-1&keywords=nancy+grisham";}}'));



//Mark Mittleberg
$mmittleberg = wp_insert_term('Mark Mittleberg', 'mbt_author', array('slug' => 'mmittleberg'));
if(!is_wp_error($mmittleberg)) { $mmittleberg_id = array($mmittleberg['term_id']); }

//Confident Faith
$post_id = wp_insert_post(array(
	'post_title' => 'Confident Faith',
	'post_content' => 'You can enjoy a robust faith—one you can share with skeptical friends—by learning the “Twenty Arrows of Truth,” including evidence from science, logic, history, archaeology, the Bible, and more. Experience a confident faith—today!',
	'post_excerpt' => 'Building a Firm Foundation for Your Beliefs',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($christianliving)) { wp_set_post_terms($post_id, $christianliving_id, "mbt_genre"); }
if(!is_wp_error($mmittleberg)) { wp_set_post_terms($post_id, $mmittleberg_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:92:"http://www.barnesandnoble.com/w/confident-faith-mark-mittelberg/1113896998?ean=9781414329963";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:153:"http://www.amazon.com/Confident-Faith-Building-Foundation-Beliefs/dp/1414329962/ref=sr_1_2?s=books&ie=UTF8&qid=1367018844&sr=1-2&keywords=confident+faith";}}'));



//Historical Fiction
$historicalfiction = wp_insert_term('Historical Fiction', 'mbt_genre', array('slug' => 'historical-fiction'));
if(!is_wp_error($historicalfiction)) { $historicalfiction_id = array($historicalfiction['term_id']); }

//Jeanette Vaughan
$jvaughan = wp_insert_term('Jeanette Vaughan', 'mbt_author', array('slug' => 'jvaughan'));
if(!is_wp_error($jvaughan)) { $jvaughan_id = array($jvaughan['term_id']); }

//Flying Solo
$post_id = wp_insert_post(array(
	'post_title' => 'Flying Solo',
	'post_content' => 'Sometimes the choices we make in life have devastating consequences. Based on a true story, Nora is a 1960s French Cajun housewife who trains as a pilot. Dynamics collide when an ilicit affair produces a child and a choice.',
	'post_excerpt' => 'Sometimes the choices we make in life have devastating consequences.',
	'post_status' => 'publish',
	'post_type' => 'mbt_book'
));
if(!is_wp_error($historicalfiction)) { wp_set_post_terms($post_id, $historicalfiction_id, "mbt_genre"); }
if(!is_wp_error($jvaughan)) { wp_set_post_terms($post_id, $jvaughan_id, "mbt_author"); }
update_post_meta($post_id, "mbt_buybuttons", unserialize('a:2:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:3:"bnn";s:3:"url";s:89:"http://www.barnesandnoble.com/w/flying-solo-jeanette-vaughan/1115228279?ean=9780615618883";}i:1;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:162:"http://www.amazon.com/Flying-Solo-Unconventional-Navigates-Turbulence/dp/061561888X/ref=sr_1_1?ie=UTF8&qid=1366856431&sr=8-1&keywords=jeanette+vaughan+flying+solo";}}'));

//Add Sample Tag
wp_insert_term('Recommended Books', 'mbt_tag', array('slug' => 'recommended'));
