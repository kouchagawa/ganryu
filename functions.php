<?php
require_once "tnk/tnk_include.php";
require_once "tnk/tnk_card.php";
require_once "tnk/tnk_command.php";

// 子テーマのスタイルシートをエンキュー
function ThemeEnqueueStyles() {
	wp_enqueue_style("parent-style", get_template_directory_uri()."/style.css");
	wp_enqueue_style("child-style", get_stylesheet_directory_uri()."/style.css", array("parent-style"), filemtime(get_theme_file_path("/style.css")));
}
add_action("wp_enqueue_scripts", "ThemeEnqueueStyles");

// スタイルシートとjavascriptファイルのエンキュー
// CSSをheadタグ内に出力しないと、ページ読み込み時点でレンダリングが崩れてしまう。
// しかし、ショートコード内のクラスのインスタンスでCSSをエンキューしようとしても、
// すでにheadタグの出力が終了し、bodyタグ内容の出力が開始された後にショートコードを
// 実行するタイミングでエンキューされるため、WordPressのフッターに出力されてしまう。
// そのため、フックを記述してヘッダーに出力する。
add_action("wp_enqueue_scripts", function() {
	$prioritise=array("twentysixteen-fonts", "genericons", "twentysixteen-style", "twentysixteen-block-style", "twentysixteen-ie", "twentysixteen-ie8", "twentysixteen-ie7");
	wp_enqueue_style("tnk-common", get_theme_file_uri("/tnk/css/common.css"), $prioritise, filemtime(get_theme_file_path("/tnk/css/common.css")));
	wp_enqueue_style("tnk-command", get_theme_file_uri("/tnk/css/command.css"), array("tnk-common"), filemtime(get_theme_file_path("/tnk/css/command.css")));
	wp_enqueue_style("tnk-card-common", get_theme_file_uri("/tnk/css/card-common.css"), array("tnk-common"), filemtime(get_theme_file_path("/tnk/css/card-common.css")));
	wp_enqueue_style("tnk-card-list", get_theme_file_uri("/tnk/css/card-list.css"), array("tnk-card-common"), filemtime(get_theme_file_path("/tnk/css/card-list.css")));
	wp_enqueue_style("tnk-card-detail", get_theme_file_uri("/tnk/css/card-detail.css"), array("tnk-card-common"), filemtime(get_theme_file_path("/tnk/css/card-detail.css")));
});


// 最新のJQueryを読み込み
function load_js() {
	// 管理画面は除外
	if (!is_admin()){
		// 最新のJQueryを読み込み
		wp_deregister_script("jquery");
		wp_enqueue_script("jquery", "//ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js", array(), "3.5.1", true);
	}
}
add_action("wp_enqueue_scripts", "load_js");

// jQueryライブラリ依存のCSS
function style_script(){
	wp_enqueue_style("anton", "https://fonts.googleapis.com/css?family=Oswald");
}
add_action("wp_enqueue_scripts", "style_script");

// JQuery非同期通信で使うサーバー側URLパス
function add_my_url() {
	$url=admin_url("admin-ajax.php");
	$theme_file_uri=get_theme_file_uri();
	echo "<script>".EOL;
	echo "var ajaxurl='{$url}';".EOL;
	echo "var themeuri='{$theme_file_uri}';".EOL;
	echo "</script>".EOL;
}
add_action("wp_head", "add_my_url", 1);


// パンくずリスト
function getBreadcrumbs() {
	$id=get_the_ID();
	if($id!=1002) {
		ob_start();
		get_template_part("template-parts/breadcrumbs");
		echo ob_get_clean();
	}
}


// 管理画面のXREA Free自動バナー非表示
function getXreaNoBannerAdmin() {
	$template="";
	if(XREA_FREE && !IS_LOCAL) $template="<!--nobanner-->";
	echo $template;
}
add_action("admin_head", "getXreaNoBannerAdmin");

// 管理画面以外のXREA Free自動バナー非表示
function getXreaNoBanner() {
	$template="";
	if(XREA_FREE && !IS_LOCAL) $template="<!--nobanner-->";
	echo $template;
}
add_action("wp_head", "getXreaNoBanner");

// 管理画面以外のXREAバナー自力表示
function getXreaBanner() {
	$template="";
	if(XREA_FREE) {
		if(IS_LOCAL)	$template="template-parts/xrea-banner-dummy";
		else			$template="template-parts/xrea-banner";
	}
	ob_start();
	get_template_part($template);
	echo ob_get_clean();
}


// ヘッダのAdSenseスニペット
// Site Kitプラグインを入れている場合は勝手に挿入されるので呼び出す必要はない
function getAdsenseSnippet() {
	if(!ENABLE_ADSENSE) return;

	if(!IS_SITEKIT && !IS_LOCAL) {
		ob_start();
		get_template_part("template-parts/adsense-snippet");
		echo ob_get_clean();
	}
}

// AdSense隊士検索と戦技検索フォーム下の広告
function AdsenseUnderForm() {
	if(!ENABLE_ADSENSE) return;
	
	if(IS_LOCAL)	$template="template-parts/adsense-under-form-dummy";
	else			$template="template-parts/adsense-under-form";
	ob_start();
	get_template_part($template);
	return ob_get_clean();
}

// AdSenseコンテンツ上部の広告
// 上：サイトトップ以外の固定ページと隊士検索・戦技検索ページは除外
// 中：モバイルの場合表示・サイトトップ以外の固定ページは除外
// 下：サイトトップ以外の固定ページは除外
function AdsenseContentUpper() {
	if(!ENABLE_ADSENSE) return;
	
	$id=get_the_ID();
//	if((!is_page() || is_front_page()) && ($id!=1282 && $id!=1285)) {
//	if(wp_is_mobile() && (!is_page() || is_front_page())) {
	if(!is_page() || is_front_page()) {
		if(wp_is_mobile()) {
			if(IS_LOCAL)	$template="template-parts/adsense-content-upper-dummy";
			else			$template="template-parts/adsense-content-upper-moblie";
		} else {
			if(IS_LOCAL)	$template="template-parts/adsense-content-upper-dummy";
			else			$template="template-parts/adsense-content-upper";
		}
		ob_start();
		get_template_part($template);
		return ob_get_clean();
	}
}
add_shortcode("AdsenseContentUpper", "AdsenseContentUpper");

function getAdsenseContentUpper() {
	echo AdsenseContentUpper();
}

/*
// 記事上にAdSense挿入
function addAdsenseContentUpper($the_content) {
	if(!ENABLE_ADSENSE) return;
	
	// 固定ページと複数記事を表示するページでは挿入しない
	if((!is_page() && !is_archive()) && is_single()) {
		if(IS_LOCAL)	$template="\\template-parts\\adsense-content-upper-dummy.php";
		else			$template="/template-parts/adsense-content-upper.php";
		$ad=file_get_contents(__DIR__.$template);
		
		if($ad!==false) {
			$the_content=$ad.$the_content;
		}
	}
	return $the_content;
}
add_filter('the_content','addAdsenseContentUpper', 10);
*/

// AdSenseコンテンツ内の広告ショートコード
// サイトトップ以外の固定ページと隊士検索・戦技検索ページは除外
// echoとreturnの違いに注意
// ショートコードはreturnで値を返す
// テンプレートなどに直接コードを書くときはechoで出力
function AdsenseContentMiddle() {
	if(!ENABLE_ADSENSE) return;
	
	$id=get_the_ID();
	if((!is_page() || is_front_page()) && ($id!=1282 && $id!=1285)) {
		if(IS_LOCAL)	$template="template-parts/adsense-content-middle-dummy";
		else			$template="template-parts/adsense-content-middle";
		ob_start();
		get_template_part($template);
		return ob_get_clean();
	}
}
add_shortcode("AdsenseContentMiddle", "AdsenseContentMiddle");

// AdSenseコンテンツ下の広告表示
// 上：サイトトップ以外の固定ページと隊士検索・戦技検索ページは除外
// 上：サイトトップ以外の固定ページは除外
// echoとreturnの違いに注意
// ショートコードはreturnで値を返す
// テンプレートなどに直接コードを書くときはechoで出力
function AdsenseContentLower() {
	if(!ENABLE_ADSENSE) return;
	
	$id=get_the_ID();
//	if((!is_page() || is_front_page()) && ($id!=1282 && $id!=1285)) {
	if(!is_page() || is_front_page()) {
		if(IS_LOCAL)	$template="template-parts/adsense-content-lower-dummy";
		else			$template="template-parts/adsense-content-lower";
		ob_start();
		get_template_part($template);
		return ob_get_clean();
	}
}
add_shortcode("AdsenseContentLower", "AdsenseContentLower");

function getAdsenseContentLower() {
	echo AdsenseContentLower();
}

/*
// 記事下にAdSense挿入
function addAdsenseContentLower($the_content) {
	if(!ENABLE_ADSENSE) return;
	
	// 固定ページと複数記事を表示するページでは挿入しない
	if((!is_page() && !is_archive()) && is_single()) {
		if(IS_LOCAL)	$template="\\template-parts\\adsense-content-inner-dummy.php";
		else			$template="/template-parts/adsense-content-inner.php";
		$ad=file_get_contents(__DIR__.$template);
		
		if($ad!==false) {
			$the_content=$the_content.$ad;
		}
	}
	return $the_content;
}
add_filter('the_content','addAdsenseContentLower', 12);
*/

// 記事内のH2上部にAdSense挿入
function addAdsenseBeforeH2($the_content) {
	if(!ENABLE_ADSENSE) return $the_content;

	// 上：固定ページでは挿入しない
	// 下：固定ページと複数記事を表示するページでは挿入しない
	//if(!is_page()) {
	if((!is_page() && !is_archive()) && is_single()) {
		if(IS_LOCAL)	$template="\\template-parts\\adsense-content-inner-dummy.php";
		else			$template="/template-parts/adsense-content-inner.php";
		$ad=file_get_contents(__DIR__.$template);
		if($ad!==false) {
			$h2="/^<h2.*?>.+?<\/h2>$/im";
			if(preg_match_all($h2, $the_content, $arr)) {
				if($arr[0]) {
					for($i=1; $i<count($arr[0]); $i=$i+2) {
						if($arr[0][$i]) {
							$the_content=str_replace($arr[0][$i], $ad.$arr[0][$i], $the_content);
						}
					}
				}
			}
		}
	}
	return $the_content;
}
add_filter('the_content','addAdsenseBeforeH2', 11);

// サイドバー上部のAdSense表示
// サイトトップ以外の固定ページは除外
// モバイルは除外
function getAdsenseWidgetUpper() {
	if(!ENABLE_ADSENSE) return;

	/*	if(!wp_is_mobile()) {
		$id=get_the_ID();
		if(!is_page() || is_front_page()) {
			if(IS_LOCAL)	$template="template-parts/adsense-widget-upper-dummy";
			else			$template="template-parts/adsense-widget-upper";
			ob_start();
			get_template_part($template);
			echo ob_get_clean();
		}
	}
*/
}

// サイドバー下部の追尾AdSense表示
// モバイルは除外
// 固定ページは除外
// 上：複数記事を表示するページ以外は除外(隊士検索・戦技検索は表示)
// 下：サイトトップ以外の固定ページは除外
function getAdsenseWidgetLower() {
	if(!ENABLE_ADSENSE) return;
	
	$id=get_the_ID();
//	if(!wp_is_mobile() && !is_page() && (!is_single() || $id==1282 || $id==1285)) {
	if(!is_page() || is_front_page()) {
		if(IS_LOCAL)	$template="template-parts/adsense-widget-lower-dummy";
		else			$template="template-parts/adsense-widget-lower";
		ob_start();
		get_template_part($template);
		echo ob_get_clean();
	}
}

// ギフトBOX削除日時
function giftbox() {
	$template="<script src='/wp-content/themes/twentysixteen-child/tnk/js/giftbox.js'></script>";
	if(wp_is_mobile()) {
		$template.=""
			."<p>現在日時<br />"
			."<span id='now'></span><br />"
			."<br />"
			."180日前<br />"
			."<span id='del'></span></p>";
	} else {
		$template.=""
			."<figure class='wp-block-table-2clumns'>"
			."<table>"
			."<tbody>"
			."<tr><td>現在日時</td><td><span id='now'></span></td></tr>"
			."<tr><td>180日前</td><td><span id='del'></span></td></tr>"
			."</tbody>"
			."</table>"
			."</figure>";
	}
	ob_start();
	echo $template;
	return ob_get_clean();
}
add_shortcode("giftbox", "giftbox");


// PCでのみ表示するコンテンツ
function if_is_pc($atts, $content = null) {
$content = do_shortcode($content);
	if(!wp_is_mobile()) {
		return $content;
	}
}
add_shortcode("pc", "if_is_pc");


// スマートフォン・タブレットでのみ表示するコンテンツ
function if_is_mb($atts, $content=null) {
	$content=do_shortcode($content);
	if(wp_is_mobile()) {
		return $content;
	}
}
add_shortcode("mb", "if_is_mb");


// 新着記事
function WhatsNew() {
	// 投稿から10日以内ならNewマーク
	$days=10;
	$today=strtotime("now");
	
	// 最新記事から5件取得
	$args=array(
		'posts_per_page'=>5,
		'offset'=>0,
		'orderby'=>'date',
		'order'=>'DESC',
		'post_type'=>'post',
		'post_status'=>'publish'
	);
	$posts=get_posts($args);
	
	// 件数分列記
	$li="";
	foreach($posts as $post) {
		$new="";
		$post_date=strtotime($post->post_date);
		if(($today-$post_date)/86400<$days) $new="<span style='color:red;font-weight:bold;'> New<span>";
		$date="[".date('y/m/d', $post_date)."] ";
		$li.="<li>{$date}<a href='/{$post->post_name}/'>{$post->post_title}</a>{$new}</li>";
	}
	
	// 新着記事リスト出力
	ob_start();
	echo "<ul>{$li}</ul>";
	return ob_get_clean();
}
add_shortcode("WhatsNew", "WhatsNew");


//require_once "tnk/tnk_include.php";
//require_once "tnk/tnk_card.php";
//require_once "tnk/tnk_command.php";

// 戦技検索ショートコード
function SearchCommand() {
	$SearchCommand=new SearchCommand();
	return $SearchCommand->to_html();
}
add_shortcode("SearchCommand", "SearchCommand");

// 隊士検索ショートコード
function SearchCard() {
	$SearchCard=new SearchCard();
	return $SearchCard->to_html();
}
add_shortcode("SearchCard", "SearchCard");

// tnk_dbメンテナンス画面ショートコード
require_once "tnk/tnk_maintenance.php";
function Maintenance() {
	$Maintenance=new Maintenance();
	return $Maintenance->html;
}
add_shortcode("Maintenance", "Maintenance");

// 戦技登録画面ショートコード
require_once "tnk/tnk_regist_command.php";
function RegistCommand() {
	$RegistCommand=new RegistCommand();
	return $RegistCommand->html;
}
add_shortcode("RegistCommand", "RegistCommand");


// 召喚アビリティリストセレクトボックス用の非同期通信
function ability_list() {
	// 直接のページ遷移を阻止
	$request=isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) : "";
	if($request!=="xmlhttprequest") exit;
	
	// Ajaxからの入力文字列チェック
	$ability_type=(string)filter_input(INPUT_POST, "ability_type");
	if(!preg_match("/\A[0-2]\z/", $ability_type)) {
		echo false;
		die();
	}
	if(!$ability_type) {
		die();
	}
	
	// クエリ生成前処理
	$name="";
	$data[0]="⇒ 全て";
	
	if($ability_type==1) {
		// スキル封じ
		$name="theme_name";
		$query="
			SELECT ability_ID, theme_name
			FROM tnk_ability
			JOIN tnk_theme ON tnk_ability.ability_antiskill_target=tnk_theme.theme_ID
			WHERE ability_type=1
			ORDER BY ability_order
		";
	} else {
		// 所属一致ボーナス
		$name="ability_name";
		$query="
			SELECT ability_ID, CONCAT(ability_regionbonus_rate,'%UP') AS ability_name
			FROM tnk_ability
			WHERE ability_type=2
			ORDER BY ability_order
		";
	}
	
	// 該当するリストをtnk_abilityテーブルから抽出
	global $wpdb;
	$results=$wpdb->get_results($query);
	
	// データの整形
	foreach($results as $row) {
		$data[$row->ability_ID]="⇒ {$row->$name}";
	}
	
	// json形式で配列を返す
	header("Content-Type: application/json");
	echo json_encode($data);
	die();
}
add_action("wp_ajax_ability_list", "ability_list");
add_action("wp_ajax_nopriv_ability_list", "ability_list");


// 戦技カテゴリリストセレクトボックス用の非同期通信
function category_list() {
	// 直接のページ遷移を阻止
	$request=isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) : "";
	if($request!=="xmlhttprequest") exit;
	
	// Ajaxからの入力文字列チェック
	$parentType=(string)filter_input(INPUT_POST, "parent_type");
	if(!preg_match("/\A[[:alpha:]]{0,7}\z/", $parentType)) {
		echo false;
		die();
	}
	
	// クエリ生成前処理
	$type=0;
	$name="";
	$data=[];
	$where="WHERE ";
	
	switch($parentType) {
		case "attack":	$type=1; $name="攻撃系";	break;
		case "defence":	$type=2; $name="防御系";	break;
		case "buff":	$type=3; $name="強化系";	break;
		case "debuff":	$type=4; $name="弱体系";	break;
		case "others":	$type=5; $name="その他";	break;
	}
	
	if(!$type) {
		$where.="command_category_ID!=0";
		$data[0]="全カテゴリー";
	} else {
		$where.="command_category_type={$type}";
		$data[0]="{$name}全て";
	}
	
	// 該当するリストをtnk_abilityテーブルから抽出
	$query="
		SELECT command_category_ID, command_category_name
		FROM tnk_command_category
		{$where}
		ORDER BY command_category_type, command_category_order
	";
	global $wpdb;
	$results=$wpdb->get_results($query);
	
	// データの整形
	foreach($results as $row) {
		$data[$row->command_category_ID]="$row->command_category_name";
	}
	
	// json形式で配列を返す
	header("Content-Type: application/json");
	echo json_encode($data);
	die();
}
add_action("wp_ajax_category_list", "category_list");
add_action("wp_ajax_nopriv_category_list", "category_list");
