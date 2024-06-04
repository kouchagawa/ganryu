<?php
require_once "tnk_include.php";

// DBからデータを持ってくるセレクトボックスの基底クラス
class SelectBoxTnk extends SelectBox {
	protected $namae;	// セレクトボックス名(日本語)
	
	// コンストラクタ
	public function __construct() {
		$ID="{$this->name}_ID";
		$name="{$this->name}_name";
		$table="tnk_{$this->name}";
		$order="{$this->name}_order";
		
		global $wpdb;
		$query="SELECT $ID, $name FROM $table ORDER BY $order";
		$results=$wpdb->get_results($query);
		$this->arr[]=["value"=>"", "label"=>"全ての{$this->namae}"];
		foreach($results as $row) {
			if($row->$ID!=0) $this->arr[]=["value"=>$row->$ID, "label"=>$row->$name];
		}
	}
}

// 地方セレクトボックス
class SelectBoxRegion extends SelectBoxTnk {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="region";
		$this->namae="地方";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		parent::__construct();
	}
}

// 県セレクトボックス
class SelectBoxPrefecture extends SelectBoxTnk {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="prefecture";
		$this->namae="県";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		parent::__construct();
	}
}

// 隊士タイプセレクトボックス
class SelectBoxTheme extends SelectBoxTnk {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="theme";
		$this->namae="タイプ";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		parent::__construct();
	}
}

// レア度セレクトボックス
class SelectBoxRarity extends SelectBoxTnk {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="rarity";
		$this->namae="レア度";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		parent::__construct();
	}
}
// 召喚アビリティタイプセレクトボックス
class SelectBoxAbilityType extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="abilityType";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		
		global $wpdb;
		$query="SELECT ability_type, ability_name FROM tnk_ability WHERE ability_type!=0 GROUP BY ability_type";
		$results=$wpdb->get_results($query);
		$this->arr[]=["value"=>"", "label"=>"召喚アビリティ"];
		foreach($results as $row) {
			$this->arr[]=["value"=>"$row->ability_type", "label"=>$row->ability_name];
		}
	}
}

// 召喚アビリティセレクトボックス
class SelectBoxAbility extends SelectBox {
	// コンストラクタ
	public function __construct() {
		$this->name="abilityID";
		$this->class="narrowDown";
		$this->id="abilityID";
	}
}

// 召喚アビリティスキル封じセレクトボックス
class SelectBoxAbilityAntiSkill extends SelectBoxAbility {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->selected=$selectedValue;
		
		global $wpdb;
		$query=
			"SELECT ability_ID, theme_name
			FROM tnk_ability
			JOIN tnk_theme ON tnk_ability.ability_antiskill_target=tnk_theme.theme_ID
			WHERE ability_type=1
			ORDER BY ability_order";
		$results=$wpdb->get_results($query);
		$this->arr[]=["value"=>"0", "label"=>"⇒ 全て"];
		foreach($results as $row) {
			$this->arr[]=["value"=>$row->ability_ID, "label"=>"⇒ $row->theme_name"];
		}
		parent::__construct();
	}
}

// 召喚アビリティ所属一致ボーナスセレクトボックス
class SelectBoxAbilityRegionBonus extends SelectBoxAbility {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->selected=$selectedValue;
		
		global $wpdb;
		$query=
			"SELECT ability_ID, CONCAT(ability_regionbonus_rate,'%UP') AS ability_name
			FROM tnk_ability
			WHERE ability_type=2
			ORDER BY ability_order";
		$results=$wpdb->get_results($query);
		$this->arr[]=["value"=>"0", "label"=>"⇒ 全て"];
		foreach($results as $row) {
			$this->arr[]=["value"=>$row->ability_ID, "label"=>"⇒ $row->ability_name"];
		}
		parent::__construct();
	}
}

// スキルセレクトボックス
class SelectBoxSkill extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="skill";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		$this->arr=[
			["value"=>"", "label"=>"全てのスキル"],
			["value"=>"offensive", "label"=>"攻撃スキル"],
			["value"=>"attackUp", "label"=>"攻UP"],
			["value"=>"defenceDown", "label"=>"防DOWN"],
			["value"=>"defensive", "label"=>"防御スキル"],
			["value"=>"defenceUp", "label"=>"防UP"],
			["value"=>"attackDown", "label"=>"攻DOWN"],
		];
	}
}

// 進化段階セレクトボックス
class SelectBoxEvolution extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="evolution";
		$this->selected=$selectedValue;
		$this->class="narrowDown";
		$this->arr=[
			["value"=>"0", "label"=>"全ての進化段階"],
			["value"=>"1", "label"=>"1進"],
			["value"=>"2", "label"=>"2進"],
			["value"=>"3", "label"=>"3進"],
			["value"=>"4", "label"=>"4進"],
			["value"=>"5", "label"=>"5進"],
			["value"=>"6", "label"=>"Max進化"],
		];
	}
}

// コストセレクトボックス
class SelectBoxCost extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="cost";
		$this->selected=$selectedValue;
		$this->class="cost";
		for($i=1; $i<=40; $i++)	 $this->arr[]=["value"=>"$i", "label"=>"コスト$i"];
	}
}

// ソートセレクトボックス
class SelectBoxSort extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="sort";
		$this->selected=$selectedValue;
		$this->arr=[
			["value"=>"", "label"=>""],
			["value"=>"rarityDESC", "label"=>"レア度の高い順"],
			["value"=>"rarityASC", "label"=>"レア度の低い順"],
			["value"=>"attackDESC", "label"=>"攻撃力の高い順"],
			["value"=>"attackASC", "label"=>"攻撃力の低い順"],
			["value"=>"defenceDESC", "label"=>"防御力の高い順"],
			["value"=>"defenceASC", "label"=>"防御力の低い順"],
			["value"=>"totalDESC", "label"=>"総パラメータの高い順"],
			["value"=>"totalASC", "label"=>"総パラメータの低い順"],
		];
	}
}

// スキル値ソートセレクトボックス
class SelectBoxSortSkill extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="sortSkill";
		$this->selected=$selectedValue;
		$this->arr=[
			["value"=>"", "label"=>""],
			["value"=>"hidebuAttackUpDESC", "label"=>"武系攻UP効果の高い順"],
			["value"=>"yomeiAttackUpDESC", "label"=>"妖系攻UP効果の高い順"],
			["value"=>"shinichiAttackUpDESC", "label"=>"飲系攻UP効果の高い順"],
			["value"=>"hidebuDefenceDownDESC", "label"=>"武系防DOWN効果の高い順"],
			["value"=>"yomeiDefenceDownDESC", "label"=>"妖系防DOWN効果の高い順"],
			["value"=>"shinichiDefenceDownDESC", "label"=>"飲系防DOWN効果の高い順"],
			["value"=>"hidebuDefenceUpDESC", "label"=>"武系防UP効果の高い順"],
			["value"=>"yomeiDefenceUpDESC", "label"=>"妖系防UP効果の高い順"],
			["value"=>"shinichiDefenceUpDESC", "label"=>"飲系防UP効果の高い順"],
			["value"=>"hidebuAttackDownDESC", "label"=>"武系攻DOWN効果の高い順"],
			["value"=>"yomeiAttackDownDESC", "label"=>"妖系攻DOWN効果の高い順"],
			["value"=>"shinichiAttackDownDESC", "label"=>"飲系攻DOWN効果の高い順"],
		];
	}
}

// ソート選択ラジオボタン
class RadioButtonSortID extends RadioButton {
	// コンストラクタ
	public function __construct($checkedValue) {
		$this->name="sortID";
		$this->checked=$checkedValue;
		$this->arr=[
			["value"=>"ASC", "label"=>"ID昇順"],
			["value"=>"DESC", "label"=>"ID降順"],
		];
	}
}

// 隊士検索画面
class SearchCard {
	private $id="";			// 隊士ID 整数値
	private $cardName="";	// カード名検索文字列
	private $evolution="6"; // 進化段階 整数値 SelectBoxEvolution 参照
	private $region="";		// 地方ID 整数値
	private $prefecture=""; // 県ID 整数値
	private $theme="";		// テーマID 整数値
	private $rarity="";		// レアリティID 整数値
	private $type="";		// タイプ切替 0or1or2
	private $abilityType="";// 召喚アビリティタイプ
	private $abilityID="";	// 召喚アビリティID
	private $cost="25";		// コスト 整数値
	private $skill="";		// スキル 文字列 クラスSelectBoxSkill参照
	private $sort="";		// ソート 文字列 クラスSelectBoxSort参照
	private $sortSkill="";	// スキル効果ソート 文字列 クラスSelectBoxSortSkill参照
	private $sortID="DESC"; // IDソート ASC:ID昇順 DESC:ID降順
	private $offset="1";	// 検索結果のオフセット 整数値 1以上
	private $rows="10";		// 一度に表示する隊士数 整数値 1〜100
	private $message="";	// エラーメッセージ
	private $html="";		// 検索画面HTML
	
	// コンストラクタ
	public function __construct() {
		// 入力文字列チェック
		$this->validation();
		
		// 検索画面HTML文字列の前半部分を作成
		$this->getNumber();
		$this->getForm();
		$this->html.=AdsenseUnderForm();
		
		if($this->message=="") {
			// 検索結果部分のHTML文字列を作成
			$this->getInfo();
		}
		
		// エラーがある場合はメッセージを表示
		$this->html.=$this->message;
	}
	
	// 戦技検索画面HTML文字列へのアクセサ
	public function to_html() {
		wp_enqueue_script("card", get_theme_file_uri("/tnk/js/card.js"), array(), filemtime(get_theme_file_path("/tnk/js/card.js")), true);
		return $this->html;
	}
	
	// 入力文字列チェック
	private function validation() {
		if($_GET) {
			// GETの値がある場合はクラス変数に受け取り、値がない場合はデフォルト値
			foreach($_GET as $key=>$val) {
				$this->$key=(string)filter_input(INPUT_GET, $key);
			}
			
			// 入力値チェックリスト
			// 隊士名で全角文字以外で使用可能な文字(ブラケット[]も含む) → [-' .]
			// 半角アンパサンド&も隊士『ロミオ&ジュリエット』で名前に使われているが
			// 送信時に&がクエリストリングの一部として認識されてしまう
			// サーバー側で&を入力不可にはしてあるが、&は少なくともinputフォームからは送れない模様
			// シングルクォートはSQLインジェクション対策でエスケープする必要がある
			// SQL文中ではシングルクォート2回でシングルクォートそのものを指す
			// wordpressではesc_sql()でエスケープする
			$list=[
				["name"=>"id", "message"=>"11以上999999以下の整数値を入力してください", "regex"=>"/\A(|1[1-9]|[2-9][0-9]|[1-9][0-9]{2,5})\z/"],
				["name"=>"cardName", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"<\A[^\x01-\x1F\x21-\x26\x28-\x2C\x2F\x3A-\x40\x5C\x5E-\x60\x7B-\x7f]{0,100}\z>"],
				["name"=>"evolution", "message"=>"0以上6以下の整数値を入力してください", "regex"=>"/\A(|[0-6])\z/"],
				["name"=>"region", "message"=>"1以上12以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|1[0-2])\z/"],
				["name"=>"prefecture", "message"=>"1以上47以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|[1-3][0-9]|4[0-7])\z/"],
				["name"=>"theme", "message"=>"57以上70以下の整数値を入力してください", "regex"=>"/\A(|5[7-9]|6[0-9]|70)\z/"],
				["name"=>"rarity", "message"=>"1以上8以下の整数値を入力してください", "regex"=>"/\A(|[1-8])\z/"],
				["name"=>"type", "message"=>"0以上2以下の整数値を入力してください", "regex"=>"/\A(|[0-2])\z/"],
				["name"=>"abilityType", "message"=>"1以上2以下の整数値を入力してください", "regex"=>"/\A(|[1-2])\z/"],
				["name"=>"abilityID", "message"=>"0以上9以下の整数値を入力してください", "regex"=>"/\A(|[0-9])\z/"],
				["name"=>"cost", "message"=>"1以上999以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|[1-9][0-9]{1,2})\z/"],
				["name"=>"skill", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,11}\z/"],
				["name"=>"sort", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,11}\z/"],
				["name"=>"sortSkill", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,23}\z/"],
				["name"=>"sortID", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,4}\z/"],
				["name"=>"offset", "message"=>"1以上999999以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|[1-9][0-9]{1,5})\z/"],
				["name"=>"rows", "message"=>"1以上100以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|[1-9][0-9]|100)\z/"],
			];
			
			// 入力値範囲チェック
			foreach($list as $row) {
				$name=$row["name"];
				if($this->$name!="") {
					if(!preg_match($row["regex"], $this->$name)) {
						$this->message.="<p class='warning'>{$name}: {$row["message"]}</p>".EOL;
					}
				}
			}
		}
	}
	
	// 総隊士数取得
	private function getNumber() {
		global $wpdb;	// WordPressのDBクラスのインスタンス
		$result=$wpdb->get_results("SELECT COUNT(*) AS num FROM tnk_card_evolution");
		$num=$result[0]->num-1;
		$this->html.="<p class='num'>現在の登録隊士数:&nbsp;$num</p>".EOL;
	}
	
	// フォーム
	private function getForm() {		
		// 検索結果HTMLに検索フォームを追加
		$SelectBoxRegion=new SelectBoxRegion($this->region);
		$SelectBoxPrefecture=new SelectBoxPrefecture($this->prefecture);
		$SelectBoxTheme=new SelectBoxTheme($this->theme);
		$SelectBoxRarity=new SelectBoxRarity($this->rarity);
		$SelectBoxSkill=new SelectBoxSkill($this->skill);
		$SelectBoxEvolution=new SelectBoxEvolution($this->evolution);
		$SelectBoxAbilityType=new SelectBoxAbilityType($this->abilityType);
		switch($this->abilityType) {
			case 1: $SelectBox=new SelectBoxAbilityAntiSkill($this->abilityID); $SelectBoxAbility=$SelectBox->to_s(); break;
			case 2: $SelectBox=new SelectBoxAbilityRegionBonus($this->abilityID); $SelectBoxAbility=$SelectBox->to_s(); break;
			default: $SelectBoxAbility=""; break;
		}
		$SelectBoxCost=new SelectBoxCost($this->cost);
		$SelectBoxSortSkill=new SelectBoxSortSkill($this->sortSkill);
		$SelectBoxSort=new SelectBoxSort($this->sort);
		$RadioButtonSortID=new RadioButtonSortID($this->sortID);
		$cardName=esc_html($this->cardName);
		
		$this->html.=""
			."<form id='formCard' class='form' action='".SEARCH_CARD_PASS."#under-form' method='get'>".EOL
			."<div>".EOL
			."<p class='label'>絞り込み</p>".EOL
			."<p><input class='searchText' type='text' name='cardName' value='{$cardName}' placeholder='隊士名 or よみがな …'></p>".EOL
			."<p>{$SelectBoxRegion->to_s()} {$SelectBoxPrefecture->to_s()}</p>".EOL
			."<p>{$SelectBoxTheme->to_s()} {$SelectBoxRarity->to_s()}</p>".EOL
			."<p>{$SelectBoxSkill->to_s()} {$SelectBoxEvolution->to_s()}</p>".EOL
			."<p>{$SelectBoxAbilityType->to_s()} <span id='abilityListContainer' class='dynamicAbilityList'>{$SelectBoxAbility}</span></p>".EOL
			."<p></p>".EOL
			."</div>".EOL
			."<div>".EOL
			."<p class='label'>オプション</p>".EOL
			."<p>{$SelectBoxCost->to_s()}</p>".EOL
			."</div>".EOL
			."<div>".EOL
			."<p class='label'>並び順</p>".EOL
			."<p>{$SelectBoxSortSkill->to_s()}</p>".EOL
			."<p>{$SelectBoxSort->to_s()}</p>".EOL
			."<p>{$RadioButtonSortID->to_s()}</p>".EOL
			."</div>".EOL
			."<p><button type='submit'>検索</button></p>".EOL
			."</form>".EOL
		;
	}
	
	// ページング文字列を返す
	// $foundRows 検索ヒット件数
	// $locate リンクの共通部分
	// 戻り値 ページング文字列
	private function paging($foundRows, $locate) {
		// ページングのリンク
		$prev="";
		if(1<=($this->offset-$this->rows)) {
			$offset=$this->offset-$this->rows;
			$prev="<a href='$locate&offset=$offset&rows=$this->rows#under-form'>←前</a>";
		} elseif(1<$this->offset) {
			$prev="<a href='$locate&offset=1&rows=$this->rows#under-form'>←前</a>";
		}
		$next="";
		if(($this->offset+$this->rows)<=$foundRows) {
			$offset=$this->offset+$this->rows;
			$next="<a href='$locate&offset=$offset&rows=$this->rows#under-form'>次→</a>";
		}
		
		// ページングのターミナル処理
		$currentTermNum=$this->offset+$this->rows-1;
		if($this->offset==$foundRows || $this->rows==1) $show=$this->offset;
		elseif($currentTermNum>$foundRows) $show=$this->offset."〜".$foundRows;
		else $show=$this->offset."〜".$currentTermNum;
		
		// リンク文字列生成
		$link="";
		if($foundRows>0) {
			if($foundRows>=$this->offset) {
				// オフセットがヒット件数を超えていなければリンク文字列を返す
				$link="<p class='paging'>{$prev}{$foundRows}件中{$show}{$next}</p>".EOL;
			} else {
				// オフセットがヒット件数を超えていればエラー
				$this->message.="<p class='warning'>offset: ヒット件数を超えています</p>".EOL;
			}
		} else {
			$link="<p class='paging'>検索結果0件</p>".EOL;
		}
		return $link;
	}
	
	// URLクエリ文字列生成
	// $keys パラメーター名を値に持つ配列
	// 戻り値 "?"無しのクエリ文字列
	private function urlQueryStrings($keys) {
		$arr=[];
		foreach($keys as $key) {
			$value=$this->$key;
			if($value!="") {
				$arr[]=esc_html("$key=$value");
			}
		}
		return implode("&", $arr);
	}
	
	// 隊士情報
	private function getInfo() {
		// DBから隊士情報を取得
		global $wpdb;
		$query=$this->getSelect().$this->getFrom().$this->getWhere().$this->getOrder().$this->getLimit();
		$results=$wpdb->get_results($query);
		$resultsRows=$wpdb->get_results("SELECT FOUND_ROWS() AS found_rows");
		//echo("query:<pre>$query</pre>");
		//view($results, "results: ");
		
		// URLクエリ文字列共通部分
		$keys=array("cardName","region","prefecture","theme","rarity","skill","evolution","abilityType","abilityID","cost","sortSkill","sort","sortID");
		$urlQueryStrings=$this->urlQueryStrings($keys);
		
		// ID検索でなければページングを表示
		if($this->id=="") {
			$paging=$this->paging($resultsRows[0]->found_rows, SEARCH_CARD_PASS."?{$urlQueryStrings}");
			$this->html.=$paging;
		} else {
			if(!$resultsRows[0]->found_rows) {
				// ID検索でヒット件数0の場合
				$this->html.="<p class='paging'>検索結果0件</p>".EOL;
			}
		}
		
		// 隊士情報検索結果から表示内容をセット
		foreach($results as $row) {
			// 所属
			$regionName="";
			$regionColor="";
			if($row->region_name) {
				$regionName=$row->region_name;
				$regionColor=$row->region_color;
			} elseif($row->prefecture_name) {
				$regionName=$row->prefecture_name;
				$regionColor=$row->prefecture_color;
			}
			
			// 隊士名に付ける隊士ID検索リンク
			$type="0";
			$optionType="";
			if($row->card_type_change) {
				if($this->id!="") {
					if($this->type!="") $type=$this->type;
				} elseif($this->sortSkill!="") {
					$type=$row->max_rate_sum_type;
				} elseif($this->theme!="") {
					if($this->theme==$row->theme_id1) $type="1";
					elseif($this->theme==$row->theme_id2)  $type="2";
				} else {
					if(strpos($row->card_name0,$this->cardName)!==false || strpos($row->card_kana0,$this->cardName)!==false) $type="0";
					elseif(strpos($row->card_name1,$this->cardName)!==false || strpos($row->card_kana1,$this->cardName)!==false) $type="1";
					elseif(strpos($row->card_name2,$this->cardName)!==false || strpos($row->card_kana2,$this->cardName)!==false) $type="2";
				}
				$optionType="type=$type&";
			} elseif($this->type!="") {
				$type=$this->type;
				$optionType="type=$type&";
			}
			$idSearchLocate=SEARCH_CARD_PASS."?{$optionType}id={$row->card_ID}{$row->card_evolution_ID}&{$urlQueryStrings}#under-form";
			
			// レアリティ画像
			$rarityImg="";
			if($row->rarity_name) {
				$src=COMMON_IMG_PASS."{$row->rarity_name}.png";
				$rarityImg="<img class='rarity' src='$src'>";
			}
			
			// 新生画像
			$shinseiImg="";
			if(mb_substr($row->card_name0,1,2)=="新生") {
				$src=COMMON_IMG_PASS."shinsei.png";
				$shinseiImg="<img class='shinsei' src='$src'>";
			}
			
			// 隊士画像
			$suffix="";
			if($row->card_type_change) {
				switch($type) {
					case "1":	$suffix="_1";	break;
					case "2":	$suffix="_2";	break;
					default:	$suffix="_0";	break;
				}
			}
			$serial=$row->card_ID.$row->card_evolution_ID.$suffix;
			$evolution=sprintf("%02s", $row->card_evolution_ID);
			$currentCardThumbSrc=CARD_THUMB_PASS."ill_{$serial}_{$row->card_image}{$evolution}_thumbCard.jpg";
			
			// レベル
			if($row->card_level_max<=0) {
				$level="??";
				$levelMax="??";
			} else {
				$level=$row->card_level_max;
				$levelMax=$row->card_level_max;
			}
			
			// 限界突破スター
			$limitStar="";
			if($row->card_evolution_ID==$row->card_evolution_max) {
				if($row->card_limitbreak_max!=0) {
					$spanStart="<span class='limitStar'>";
					$spanClose="</span>";
					if($row->card_limitbreak_max<0) {
						$limitStar=$spanStart."？？？？".$spanClose;
					} else {
						$limitStar=$spanStart;
						for($i=0; $i<$row->card_limitbreak_max; $i++) $limitStar.="★";
						$limitStar.=$spanClose;
					}
				}
			}
			
			if($row->card_type_change) {
				// タイプ切り替え隊士の場合
				switch($type) {
					case "1":	$name=$row->card_name1; $kana=$row->card_kana1; $theme=$row->theme_name1;
								$skillName=$row->card_skill_name1; $skillText=$row->card_skill_text1;
								break;
					case "2":	$name=$row->card_name2; $kana=$row->card_kana2; $theme=$row->theme_name2;
								$skillName=$row->card_skill_name2; $skillText=$row->card_skill_text2;
								break;
					default:	$name=$row->card_name0; $kana=$row->card_kana0; $theme=$row->theme_name0;
								$skillName=$row->card_skill_name0; $skillText=$row->card_skill_text0;
								break;
				}
			} else {
				// タイプ切り替え隊士ではない場合
				$name=$row->card_name0;
				$kana=$row->card_kana0;
				$theme=$row->theme_name0;
				$skillName=$row->card_skill_name0;
				$skillText=$row->card_skill_text0;
			}
			
			// 隊士名・読み方
			if($name=="") $name="？？？？";
			if($kana=="") $kana="？？？？";
			
			// タイプ
			if($theme=="") {
				if($regionName=="特殊素材" || $regionName=="進化素材") $theme="　";
				else $theme="？？".EOL;
			}
			$theme="<dt>タイプ</dt><dd>$theme</dd>".EOL;
			
			// スキル名・スキル効果
			$skill="";
			$exposition="";
			if($skillName=="" && $row->card_skill_level!=0) $skillName="？？";
			if($regionName=="進化素材") {
				// 進化素材かつ隊士詳細画面の場合だけ『スキル』->『説明』(公式謎仕様)
				if($this->id=="") $skill="<dt>スキル</dt><dd>進化素材です</dd>".EOL;
				else $exposition="<dt>説明</dt><dd>進化素材です</dd>".EOL;
			} elseif($skillName!="") {
				$sum="";
				if($this->id=="") {
					if($this->sortSkill!="") $sum="効果値合計:$row->max_rate_sum";
					else $sum="";
				}
				if($skillText!="") {
					$skillLevel="&nbsp;Lv:";
					if($row->card_skill_level==0) $skillLevel.="??";
					elseif($row->card_skill_level<10) $skillLevel.="<span class='notice'>$row->card_skill_level/10</span>";
					else $skillLevel.=$row->card_skill_level;
					$skillName.=$skillLevel;
				}
				if($skillText=="") $skillText="？？？？";
				$skill="<dt>スキル</dt><dd>$skillName<p class='skillText'>$skillText</p>$sum</dd>".EOL;
			}
			
			// アビリティ
			$ability="";
			if($row->ability_name!="") $ability="<dt>召喚<br>ｱﾋﾞﾘﾃｨ</dt><dd>$row->ability_name<p class='skillText'>$row->ability_text</p></dd>".EOL;
			
			// 備考
			$notes="";
			if($row->card_notes!="") $notes="<dt>備考</dt><dd>$row->card_notes</dd>".EOL;
			
			// 戦技
			$searchCommandLocate=SEARCH_COMMAND_PASS."?id={$row->command_ID}#under-form";
			$command="";
			if($row->command_name!="") {
				$commandIconThumbFile="cs_{$row->command_ID}_{$row->command_icon}_thumb.png";
				$commandIconThumbSrc=COMMAND_ICON_THUMB_PASS.$commandIconThumbFile;
				$command=""
					."<div class='command'>".EOL
					."<figure class='commandIcon'><a class='image' href='{$searchCommandLocate}'><img src='{$commandIconThumbSrc}'></a></figure>".EOL
					."<dl>".EOL
					."<dt>戦技</dt>".EOL
					."<dd><span class='commandName'><a href='{$searchCommandLocate}'>{$row->command_name}</a></span>"
					."<p class='commandText'>{$row->command_official_text}</p></dd>".EOL
					."<dt>消費P</dt>".EOL
					."<dd><span class='commandCost'><span class='spend cost{$row->command_cost}'></span>".EOL
					."<span class='partition part1'></span><span class='partition part2'></span></span></dd>".EOL
					."</dl>".EOL
					."</div>".EOL
				;
			}
			
			if($this->id=="") {
				// ID検索ではない場合は隊士リストを表示
				if($regionName=="進化素材" || $regionName=="特殊素材") {
					// 進化強化素材は実際のコスト・攻・防をセット
					$cost=$row->real_cost;
					$attack=$row->real_attack;
					$defence=$row->real_defence;
				} else {
					// 通常の隊士は指定されたコストで算出された攻防値をセット(算出による攻防値の誤差±3程度)
					if($row->card_rate_cost<=0) $cost="??";
					else $cost=$row->card_rate_cost;
					if($row->card_rate_attack<=0) $attack="?????";
					else $attack=$row->card_rate_attack;
					if($row->card_rate_defence<=0) $defence="?????";
					else $defence=$row->card_rate_defence;
				}
				
				// 隊士リスト表示内容
				$this->html.=""
					."<div class='infoCard' id='infoCard'>".EOL
					."<div class='header'>".EOL
					."<p>"
					."<span class='region {$regionColor}'>{$regionName}</span>"
					.$rarityImg
					.$shinseiImg
					."<span class='name'><a href='{$idSearchLocate}'>$name</a></span>"
					."</p>".EOL
					."</div>".EOL
					."<div class='contentCard'>".EOL
					."<figure class='card'><a class='image' href='{$idSearchLocate}'><img src='{$currentCardThumbSrc}'></a></figure>".EOL
					."<div class='items'>".EOL
					."<p class='level'><span class='levelLabel'>Lv:</span>{$level}/{$levelMax}{$limitStar}</p>".EOL
					."<dl>".EOL
					."<dt>コスト</dt><dd>{$cost}</dd>".EOL
					."<dt>攻</dt><dd>{$attack}</dd>".EOL
					."<dt>防</dt><dd>{$defence}</dd>".EOL
					.$theme
					.$skill
					.$ability
					."</dl>".EOL
					."</div>".EOL
					.$command
					."</div>".EOL
					."</div>".EOL
				;
			} else {
				// ID検索の場合は隊士情報詳細を表示
				$switchCardType="";
				if($row->card_type_change) {
					// タイプ切替隊士の場合はタイプ切替リンクを表示
					$isActive0=$isActive1=$isActive2="";
					switch($this->type) {
						case "1":	$isActive1=" isActive";	 break;
						case "2":	$isActive2=" isActive";	 break;
						default:	$isActive0=" isActive";	 break;
					}
					$optionExt="id={$row->card_ID}{$row->card_evolution_ID}&{$urlQueryStrings}#under-form";
					$herf0=SEARCH_CARD_PASS."?type=0&$optionExt";
					$herf1=SEARCH_CARD_PASS."?type=1&$optionExt";
					$herf2=SEARCH_CARD_PASS."?type=2&$optionExt";
					$switchCardType.=""
						."<p class='switchCardTypeTitle'>タイプ切り替え</p>".EOL
						."<div class='switchCardType'>".EOL
						."<a class='cardChange{$isActive0}' href='{$herf0}'>{$row->theme_name0}</a>"
						."<a class='cardChange{$isActive1}' href='{$herf1}'>{$row->theme_name1}</a>"
						."<a class='cardChange{$isActive2}' href='{$herf2}'>{$row->theme_name2}</a>"
						."</div>".EOL
					;
				}
				
				// 攻防値
				$query=""
					."SELECT card_rate_attack, card_rate_defence, card_rate_cost "
					."FROM tnk_card_rate "
					."WHERE card_id=$row->card_ID AND card_evolution_id=$row->card_evolution_ID"
				;
				$resultsRate=$wpdb->get_results($query);
				$rate="<dt>ｻﾝﾌﾟﾙ</dt>";
				foreach($resultsRate as $rowRate) {
					// コスト
					if($rowRate->card_rate_cost=="0") $cost="??";
					else $cost=$rowRate->card_rate_cost;
					
					// 攻
					if($rowRate->card_rate_attack=="0") $attack="?????";
					else $attack=$rowRate->card_rate_attack;
					
					// 防
					if($rowRate->card_rate_defence=="0") $defence="?????";
					else $defence=$rowRate->card_rate_defence;
					
					$rate.=""
						."<dd><span class='label'>ｺｽﾄ</span><span class='value'>{$cost}</span>"
						."<span class='label'>攻</span><span class='value'>{$attack}</span>"
						."<span class='label'>防</span><span class='value'>{$defence}</span></dd>"
					;
				}
					
				// 基礎コスト
				if($row->card_base_cost=="0") $baseCost="?";
				else $baseCost=$row->card_base_cost;
				
				// コスト1あたりの基礎攻撃力
				if($row->card_base_attack=="0") $baseAttack="???";
				else $baseAttack=$row->card_base_attack;
				
				// コスト1あたりの基礎防御力
				if($row->card_base_defence=="0") $baseDefence="???";
				else $baseDefence=$row->card_base_defence;
				
				$upgradeBase="";
				if($row->card_level_max>1) {
					// コスト1あたりの強化による攻撃力増加量
					if($row->card_upgrade_base_attack=="0") $upgradeBaseAttack="????";
					else $upgradeBaseAttack=$row->card_upgrade_base_attack;
					
					// コスト1あたりの強化による防御力増加量
					if($row->card_upgrade_base_defence=="0") $upgradeBaseDefence="????";
					else $upgradeBaseDefence=$row->card_upgrade_base_defence;
					
					$upgradeBase.="<dd><span class='label'>強化攻/ｺｽﾄ</span><span class='value'>{$upgradeBaseAttack}</span>".EOL;
					$upgradeBase.="<span class='label'>強化防/ｺｽﾄ</span><span class='value'>{$upgradeBaseDefence}</span></dd>".EOL;
				}
				
				$limitbreakBase="";
				if($row->card_limitbreak_max!=0 && $row->card_evolution_ID==$row->card_evolution_max) {
					// レベル1あたりの限界突破による攻撃力増加量
					if($row->card_limitbreak_base_attack=="0") $limitbreakBaseAttack="???";
					else $limitbreakBaseAttack=$row->card_limitbreak_base_attack;
					
					// レベル1あたりの限界突破による防御力増加量
					if($row->card_limitbreak_base_defence=="0") $limitbreakBaseDefence="???";
					else $limitbreakBaseDefence=$row->card_limitbreak_base_defence;
					
					$limitbreakBase.="<dd><span class='label'>限突攻/ﾚﾍﾞﾙ</span><span class='value'>{$limitbreakBaseAttack}</span>".EOL;
					$limitbreakBase.="<span class='label'>限突防/ﾚﾍﾞﾙ</span><span class='value'>{$limitbreakBaseDefence}</span></dd>".EOL;
				}
				
				// ウンチク
				$flavorColName="card_flavor_text$type";
				$flavorText=$row->$flavorColName;
				if($flavorText!="") {
					$flavorText=""
						."<div class='flavorText'>".EOL
						."<p>{$flavorText}</p>".EOL
						."</div>".EOL
					;
				}
				
				// 進化段階
				$book="";
				for($i=1; $i<=$row->card_evolution_max; $i++) {
					$query="SELECT * FROM tnk_card_evolution WHERE card_id={$row->card_ID} AND card_evolution_ID={$i}";
					$resultsBook=$wpdb->get_results($query);
					$img=$cur="";
					if(!$resultsBook) {
						$img="<p class='unKnownEvolution'>?</p>";
					} else {
//						  echo "i={$i} card_type_change={$resultsBook[0]->card_type_change}<br>";
//						  view($resultsBook[0]);
						$suffix_each="";
						if($resultsBook[0]->card_type_change) {
							if($suffix) $suffix_each=$suffix;
							else $suffix_each="_0";
						}
						$serial=$row->card_ID.$i.$suffix_each;
						$evolution=sprintf("%02s", $i);
						$cardThumbSrc=CARD_THUMB_PASS."ill_{$serial}_{$resultsBook[0]->card_image}{$evolution}_thumbCard.jpg";
						$img="<img src='{$cardThumbSrc}'>";
						if($i==$row->card_evolution_ID) {
							$cur="<span class='current'>●</span>";
						} else {
							$idSearchLocate=SEARCH_CARD_PASS."?id={$row->card_ID}{$i}";
							if($type!="") $idSearchLocate.="&type={$type}";
							if($this->theme!="") $idSearchLocate.="&theme={$this->theme}";
							$idSearchLocate.="&{$urlQueryStrings}#under-form";
							$img="<a href='{$idSearchLocate}'>{$img}</a>";
						}
					}
					$book.="<dd><figure class='book'>{$img}"
						."<figcaption><p class='evolutionProgress'>{$cur}進化Lv{$i}</p></figcaption></figure></dd>".EOL;
				}
				
				// 隊士情報詳細表示内容
				$this->html.=""
					."<div class='infoCardDetail'>".EOL
					."<p class='header'>"
					."<span class='region {$regionColor}'>{$regionName}</span>"
					.$rarityImg
					.$shinseiImg
					."<span class='name'>{$name}</span>"
					."</p>".EOL
					."<div class='contentCard'>".EOL
					."<figure class='card'><img src='{$currentCardThumbSrc}'></figure>".EOL
					.$switchCardType
					."<div class='items'>".EOL
					."<p class='level'><span class='levelLabel'>Lv:</span>{$level}{$limitStar}</p>".EOL
					."<dl>".EOL
					."<dt>読み方</dt><dd>{$kana}</dd>".EOL
					.$rate
					."<dt>基礎値</dt>"
					."<dd><span class='label'>ｺｽﾄ</span><span class='value'>{$baseCost}〜</span>"
					."<span class='label'>攻/ｺｽﾄ</span><span class='value'>$baseAttack</span>"
					."<span class='label'>防/ｺｽﾄ</span><span class='value'>$baseDefence</span></dd>".EOL
					.$upgradeBase
					.$limitbreakBase
					.$theme
					.$skill
					.$ability
					.$exposition
					.$notes
					."</dl>".EOL
					."</div>".EOL
					.$command
					."</div>".EOL
					.$flavorText
					."<div class='innerBox'>".EOL
					."<dl>".EOL
					."<dt>進化段階</dt>".EOL
					.$book
					."</dl>".EOL
					."</div>".EOL
					."</div>".EOL
				;
			}
		}
		
		// 検索結果下側のページング
		if($this->id=="" && $resultsRows[0]->found_rows>0 && $resultsRows[0]->found_rows>=$this->offset) {
			$this->html.=$paging;
		}
	}
	
	// 隊士情報クエリSELECT句
	private function getSelect() {
		$rate00=$rate01=$rate10=$rate11=$rate20=$rate21=$rateSum0=$rateSum1=$rateSum2=$maxRateSum=$maxRateSumType="";
		if($this->sortSkill!="") {
			// 系列UP/DOWNスキルソート指定ありの場合、スキル効果テキストをパースしてスキル効果値を評価する
			// クエリで正規表現が使える環境ならばもっと簡潔に書けるはずだが、現在のXREAのMySQLバージョンでは正規表現は使用できない
			switch($this->sortSkill) {
				case "hidebuAttackUpDESC":
				case "hidebuAttackUpASC":		$t0="武"; $t1="姫"; $t2="伝"; $ad="攻"; $ud="U"; break;
				case "yomeiAttackUpDESC":
				case "yomeiAttackUpASC":		$t0="妖"; $t1="名"; $t2="偉"; $ad="攻"; $ud="U"; break;
				case "shinichiAttackUpDESC":
				case "shinichiAttackUpASC":		$t0="飲"; $t1="神"; $t2="知"; $ad="攻"; $ud="U"; break;
				case "hidebuDefenceDownDESC":
				case "hidebuDefenceDownASC":	$t0="武"; $t1="姫"; $t2="伝"; $ad="防"; $ud="D"; break;
				case "yomeiDefenceDownDESC":
				case "yomeiDefenceDownASC":		$t0="妖"; $t1="名"; $t2="偉"; $ad="防"; $ud="D"; break;
				case "shinichiDefenceDownDESC":
				case "shinichiDefenceDownASC":	$t0="飲"; $t1="神"; $t2="知"; $ad="防"; $ud="D"; break;
				case "hidebuDefenceUpDESC":
				case "hidebuDefenceUpASC":		$t0="武"; $t1="姫"; $t2="伝"; $ad="防"; $ud="U"; break;
				case "yomeiDefenceUpDESC":
				case "yomeiDefenceUpASC":		$t0="妖"; $t1="名"; $t2="偉"; $ad="防"; $ud="U"; break;
				case "shinichiDefenceUpDESC":
				case "shinichiDefenceUpASC":	$t0="飲"; $t1="神"; $t2="知"; $ad="防"; $ud="U"; break;
				case "hidebuAttackDownDESC":
				case "hidebuAttackDownASC":		$t0="武"; $t1="姫"; $t2="伝"; $ad="攻"; $ud="D"; break;
				case "yomeiAttackDownDESC":
				case "yomeiAttackDownASC":		$t0="妖"; $t1="名"; $t2="偉"; $ad="攻"; $ud="D"; break;
				case "shinichiAttackDownDESC":
				case "shinichiAttackDownASC":	$t0="飲"; $t1="神"; $t2="知"; $ad="攻"; $ud="D"; break;
				default:						$t0="も"; $t1="も"; $t2="く"; $ad="ろ"; $ud="Z"; break;
			}
			
			// タイプごとのスキル効果テキスト
			$skill00="tnk_card_type0.card_skill_text0";
			$skill01="tnk_card_type0.card_skill_text1";
			$skill10="tnk_card_type1.card_skill_text0";
			$skill11="tnk_card_type1.card_skill_text1";
			$skill20="tnk_card_type2.card_skill_text0";
			$skill21="tnk_card_type2.card_skill_text1";
			
			// タイプごとのスキル効果数値を抜き出す
			$rate00="@r00:=SUBSTRING_INDEX(IF($skill00 LIKE '%防%',SUBSTRING_INDEX($skill00,'防',-1),SUBSTRING_INDEX($skill00,'攻',-1)),'％',1) AS rate00, ";
			$rate01="@r01:=SUBSTRING_INDEX(IF($skill01 LIKE '%防%',SUBSTRING_INDEX($skill01,'防',-1),SUBSTRING_INDEX($skill01,'攻',-1)),'％',1) AS rate01, ";
			$rate10="@r10:=SUBSTRING_INDEX(IF($skill10 LIKE '%防%',SUBSTRING_INDEX($skill10,'防',-1),SUBSTRING_INDEX($skill10,'攻',-1)),'％',1) AS rate10, ";
			$rate11="@r11:=SUBSTRING_INDEX(IF($skill11 LIKE '%防%',SUBSTRING_INDEX($skill11,'防',-1),SUBSTRING_INDEX($skill11,'攻',-1)),'％',1) AS rate11, ";
			$rate20="@r20:=SUBSTRING_INDEX(IF($skill20 LIKE '%防%',SUBSTRING_INDEX($skill20,'防',-1),SUBSTRING_INDEX($skill20,'攻',-1)),'％',1) AS rate20, ";
			$rate21="@r21:=SUBSTRING_INDEX(IF($skill21 LIKE '%防%',SUBSTRING_INDEX($skill21,'防',-1),SUBSTRING_INDEX($skill21,'攻',-1)),'％',1) AS rate21, ";
			
			// タイプごとのスキル効果数値を合計する
			// ただし、ここでの合計値は例えば武系メインデッキならば武：姫：伝＝3:3:3の標準的な構成のときの参考値であって、
			// 4:3:2等の変則的構成デッキではまた状況が違うことに留意すべき
			// 例：『タイプ姫・伝承の攻20%UP / タイプ武人の攻15%UP』 → 姫20+伝20+武15=55
			$all="全タ";
			$rateSum0="@rs0:=
				IF(
					LOCATE('$ad',$skill00) AND LOCATE('$ud',$skill00),(
						IF(LOCATE('$t0',$skill00) OR LOCATE('$all',$skill00),1,0)
						+IF(LOCATE('$t1',$skill00) OR LOCATE('$all',$skill00),1,0)
						+IF(LOCATE('$t2',$skill00) OR LOCATE('$all',$skill00),1,0)
					)*@r00,0
				)+IF(
					LOCATE('$ad',$skill01) AND LOCATE('$ud',$skill01),(
						IF(LOCATE('$t0',$skill01) OR LOCATE('$all',$skill01),1,0)
						+IF(LOCATE('$t1',$skill01) OR LOCATE('$all',$skill01),1,0)
						+IF(LOCATE('$t2',$skill01) OR LOCATE('$all',$skill01),1,0)
					)*@r01,0
				) AS rate_sum0, ";
			$rateSum1="@rs1:=
				IF(
					LOCATE('$ad',$skill10) AND LOCATE('$ud',$skill10),(
						IF(LOCATE('$t0',$skill10) OR LOCATE('$all',$skill10),1,0)
						+IF(LOCATE('$t1',$skill10) OR LOCATE('$all',$skill10),1,0)
						+IF(LOCATE('$t2',$skill10) OR LOCATE('$all',$skill10),1,0)
					)*@r10,0
				)+IF(
					LOCATE('$ad',$skill11) AND LOCATE('$ud',$skill11),(
						IF(LOCATE('$t0',$skill11) OR LOCATE('$all',$skill11),1,0)
						+IF(LOCATE('$t1',$skill11) OR LOCATE('$all',$skill11),1,0)
						+IF(LOCATE('$t2',$skill11) OR LOCATE('$all',$skill11),1,0)
					)*@r11,0
				) AS rate_sum1, ";
			$rateSum2="@rs2:=
				IF(
					LOCATE('$ad',$skill20) AND LOCATE('$ud',$skill20),(
						IF(LOCATE('$t0',$skill20) OR LOCATE('$all',$skill20),1,0)
						+IF(LOCATE('$t1',$skill20) OR LOCATE('$all',$skill20),1,0)
						+IF(LOCATE('$t2',$skill20) OR LOCATE('$all',$skill20),1,0)
					)*@r20,0
				)+IF(
					LOCATE('$ad',$skill21) AND LOCATE('$ud',$skill21),(
						IF(LOCATE('$t0',$skill21) OR LOCATE('$all',$skill21),1,0)
						+IF(LOCATE('$t1',$skill21) OR LOCATE('$all',$skill21),1,0)
						+IF(LOCATE('$t2',$skill21) OR LOCATE('$all',$skill21),1,0)
					)*@r21,0
				) AS rate_sum2, ";
				
			// スキルの最大効果値合計とその戦技効果の切替タイプ番号を得る
			if($this->theme) {
					// $this->typeよりも検索フォームからユーザーが任意に指定できる$this->themeを優先したほうが、
					// ユーザーにとっては意図した動作に映るはずなので、先に$this->themeを処理する。
					if($this->type!=="") {
					// $this->themeで武人・姫等の隊士タイプ指定がある場合で
					// $this->typeで0・1・2のタイプ切替指定がある場合には、そちらのタイプ指定を優先して、その切替タイプ番号と効果値合計をセットする
					// 通常、max_rate_sumを使用する隊士リスト画面では検索フォームから$this->typeのタイプ指定をユーザーが任意に行うことはない。
					// ただ、URLのGET文字列で指定しようと思えばできないこともないので、この部分の実装が必要になる。
					// そもそも、隊士リスト画面と隊士詳細画面でちゃんとクラス分けしていればこの部分の実装は必要ない気もするけど。
					$maxRateSum="CAST(@rs$this->type AS UNSIGNED) AS max_rate_sum, ";
					$maxRateSumType="$this->type AS max_rate_sum_type, ";
				} else {
					// $this->themeで武人・姫等の隊士タイプ指定がある場合で
					// $this->typeで0・1・2のタイプ切替指定がない場合には、指定された武人・姫等の隊士タイプの切替タイプ番号と効果値合計をセットする
					$maxRateSum="CAST(IF(tnk_card_type1.theme_id=$this->theme,@rs1,IF(tnk_card_type2.theme_id=$this->theme,@rs2,@rs0)) AS UNSIGNED) AS max_rate_sum, ";
					$maxRateSumType="IF(tnk_card_type1.theme_id=$this->theme,1,IF(tnk_card_type2.theme_id=$this->theme,2,0)) AS max_rate_sum_type, ";
				}
			} elseif($this->type!=="") {
				// 隊士がタイプ切替不可の場合はタイプ0の効果値合計をセットする
				// 隊士がタイプ切替可で、$this->typeで0・1・2のタイプ切替指定がある場合には、その切替タイプ番号と効果値合計をセットする
				$maxRateSum="CAST(IF(card_type_change=0,@rs0,@rs$this->type) AS UNSIGNED) AS max_rate_sum, ";
				$maxRateSumType="IF(card_type_change=0,0,$this->type) AS max_rate_sum_type, ";
			} else {
				// $this->typeも$this->themeもいずれも指定がなければ、効果値合計最大となる切替タイプ番号と効果値合計をセットする
				$maxRateSum="CAST(IF(@rs0>=@rs1,IF(@rs0>=@rs2,@rs0,@rs2),IF(@rs1>=@rs2,@rs1,@rs2)) AS UNSIGNED) AS max_rate_sum, ";
				$maxRateSumType="IF(@rs0>=@rs1,IF(@rs0>=@rs2,0,2),IF(@rs1>=@rs2,1,2)) AS max_rate_sum_type, ";
			}
		}
		
		return ""
			."SELECT SQL_CALC_FOUND_ROWS "
			."tnk_card.card_ID, "
			."tnk_card_evolution.card_evolution_ID, "
			."card_evolution_max, "
			."card_type_change, "
			."tnk_rarity.rarity_ID, "
			."rarity_name, "
			."tnk_region.region_ID, "
			."region_name, "
			."region_color, "
			."tnk_prefecture.prefecture_ID, "
			."prefecture_name, "
			."prefecture_color, "
			."tnk_card_type0.card_name AS card_name0, "
			."tnk_card_type1.card_name AS card_name1, "
			."tnk_card_type2.card_name AS card_name2, "
			."tnk_card_type0.card_kana AS card_kana0, "
			."tnk_card_type1.card_kana AS card_kana1, "
			."tnk_card_type2.card_kana AS card_kana2, "
			."card_image, "
			."card_skill_level, "
			."card_level_max, "
			."card_limitbreak_max, "
			."card_limitbreak_base_attack, "
			."card_limitbreak_base_defence, "
			."card_base_cost, "
			."card_base_attack, "
			."card_base_defence, "
			."card_upgrade_base_attack, "
			."card_upgrade_base_defence, "
			."card_rate_cost AS real_cost, "
			."card_rate_attack AS real_attack, "
			."card_rate_defence AS real_defence, "
			."$this->cost AS card_rate_cost, "
			."@att:=TRUNCATE(card_rate_attack/card_rate_cost*$this->cost,0) AS card_rate_attack, "
			."@def:=TRUNCATE(card_rate_defence/card_rate_cost*$this->cost,0) AS card_rate_defence, "
			."@att+@def AS card_rate_total, "
			."tnk_card_type0.theme_id AS theme_id0, "
			."tnk_theme0.theme_name AS theme_name0, "
			."tnk_card_type1.theme_id AS theme_id1, "
			."tnk_theme1.theme_name AS theme_name1, "
			."tnk_card_type2.theme_id AS theme_id2, "
			."tnk_theme2.theme_name AS theme_name2, "
			."tnk_card_type0.card_skill_name AS card_skill_name0, "
			."tnk_card_type1.card_skill_name AS card_skill_name1, "
			."tnk_card_type2.card_skill_name AS card_skill_name2, "
			."CONCAT(tnk_card_type0.card_skill_text0,IF(CHAR_LENGTH(tnk_card_type0.card_skill_text1),'　/　',''),tnk_card_type0.card_skill_text1) AS card_skill_text0, "
			."CONCAT(tnk_card_type1.card_skill_text0,IF(CHAR_LENGTH(tnk_card_type1.card_skill_text1),'　/　',''),tnk_card_type1.card_skill_text1) AS card_skill_text1, "
			."CONCAT(tnk_card_type2.card_skill_text0,IF(CHAR_LENGTH(tnk_card_type2.card_skill_text1),'　/　',''),tnk_card_type2.card_skill_text1) AS card_skill_text2, "
			.$rate00
			.$rate01
			.$rate10
			.$rate11
			.$rate20
			.$rate21
			.$rateSum0
			.$rateSum1
			.$rateSum2
			.$maxRateSum
			.$maxRateSumType
			."tnk_ability.ability_type, "
			."tnk_ability.ability_ID, "
			."CONCAT(tnk_ability.ability_name,IF(tnk_ability.ability_type=2,tnk_ability.ability_regionbonus_rate,''),IF(tnk_ability.ability_type=2,'%UP','')) AS ability_name, "
			."ability_text, "
			."card_notes, "
			."tnk_card_type0.card_flavor_text AS card_flavor_text0, "
			."tnk_card_type1.card_flavor_text AS card_flavor_text1, "
			."tnk_card_type2.card_flavor_text AS card_flavor_text2, "
			."tnk_command.command_ID, "
			."command_icon, "
			."command_name, "
			."command_cost, "
			."command_official_text "
		;
	}
	
	// 隊士情報クエリFROM句
	private function getFrom() {
		return ""
			."FROM tnk_card_evolution "
			."JOIN tnk_card_rate "
			."ON tnk_card_evolution.card_id=tnk_card_rate.card_id "
			."AND tnk_card_evolution.card_evolution_ID=tnk_card_rate.card_evolution_id "
			."LEFT JOIN tnk_card_type AS tnk_card_type0 "
			."ON tnk_card_evolution.card_id=tnk_card_type0.card_id "
			."AND tnk_card_evolution.card_evolution_ID=tnk_card_type0.card_evolution_id "
			."AND tnk_card_type0.card_type_ID=0 "
			."LEFT JOIN tnk_theme AS tnk_theme0 "
			."ON tnk_card_type0.theme_id=tnk_theme0.theme_ID "
			."LEFT JOIN tnk_card_type AS tnk_card_type1 "
			."ON tnk_card_evolution.card_id=tnk_card_type1.card_id "
			."AND tnk_card_evolution.card_evolution_ID=tnk_card_type1.card_evolution_id "
			."AND tnk_card_type1.card_type_ID=1 "
			."LEFT JOIN tnk_theme AS tnk_theme1 "
			."ON tnk_card_type1.theme_id=tnk_theme1.theme_ID "
			."LEFT JOIN tnk_card_type AS tnk_card_type2 "
			."ON tnk_card_evolution.card_id=tnk_card_type2.card_id "
			."AND tnk_card_evolution.card_evolution_ID=tnk_card_type2.card_evolution_id "
			."AND tnk_card_type2.card_type_ID=2 "
			."LEFT JOIN tnk_theme AS tnk_theme2 "
			."ON tnk_card_type2.theme_id=tnk_theme2.theme_ID "
			."JOIN tnk_region "
			."ON tnk_card_evolution.region_id=tnk_region.region_ID "
			."JOIN tnk_prefecture "
			."ON tnk_card_evolution.prefecture_id=tnk_prefecture.prefecture_ID "
			."JOIN tnk_rarity "
			."ON tnk_card_evolution.rarity_id=tnk_rarity.rarity_ID "
			."JOIN tnk_card "
			."ON tnk_card_evolution.card_id=tnk_card.card_ID "
			."JOIN tnk_ability "
			."ON tnk_card_evolution.ability_type=tnk_ability.ability_type "
			."AND tnk_card_evolution.ability_id=tnk_ability.ability_ID "
			."JOIN tnk_command "
			."ON tnk_card.command_id=tnk_command.command_ID "
		;
	}
	
	// 隊士情報クエリWHERE句
	private function getWhere() {
		$where="WHERE tnk_card.card_ID!=0 ";
		if($this->id!="") {
			$where.="AND tnk_card.card_ID=".substr($this->id,0,-1)." AND tnk_card_evolution.card_evolution_ID=".substr($this->id,-1)." ";
		} else {
			if($this->region!="") $where.="AND tnk_region.region_ID=$this->region ";
			if($this->prefecture!="") $where.="AND tnk_prefecture.prefecture_ID=$this->prefecture ";
			if($this->rarity!="") $where.="AND tnk_rarity.rarity_ID=$this->rarity ";
			switch($this->evolution) {
				case "1":	$where.="AND tnk_card_evolution.card_evolution_ID=1 ";					break;
				case "2":	$where.="AND tnk_card_evolution.card_evolution_ID=2 ";					break;
				case "3":	$where.="AND tnk_card_evolution.card_evolution_ID=3 ";					break;
				case "4":	$where.="AND tnk_card_evolution.card_evolution_ID=4 ";					break;
				case "5":	$where.="AND tnk_card_evolution.card_evolution_ID=5 ";					break;
				case "6":	$where.="AND tnk_card_evolution.card_evolution_ID=card_evolution_max ";	break;
			}
		}
		if($this->skill!="") {
			$text0="tnk_card_type0.card_skill_text0";
			$text1="tnk_card_type0.card_skill_text1";
			$attackUp0="$text0 LIKE '%攻%UP%'";
			$defenceDown0="$text0 LIKE '%防%DOWN%'";
			$defenceUp0="$text0 LIKE '%防%UP%'";
			$attackDown0="$text0 LIKE '%攻%DOWN%'";
			$attackUp1="$text1 LIKE '%攻%UP%'";
			$defenceDown1="$text1 LIKE '%防%DOWN%'";
			$defenceUp1="$text1 LIKE '%防%UP%'";
			$attackDown1="$text1 LIKE '%攻%DOWN%'";
			switch($this->skill) {
				case "offensive":	$where.="AND ($attackUp0 OR $attackUp1 OR $defenceDown0 OR $defenceDown1) ";	break;
				case "attackUp":	$where.="AND ($attackUp0 OR $attackUp1) ";										break;
				case "defenceDown": $where.="AND ($defenceDown0 OR $defenceDown1) ";								break;
				case "defensive":	$where.="AND ($defenceUp0 OR $defenceUp1 OR $attackDown0 OR $attackDown1) ";	break;
				case "defenceUp":	$where.="AND ($defenceUp0 OR $defenceUp1) ";									break;
				case "attackDown":	$where.="AND ($attackDown0 OR $attackDown1) ";									break;
			}
		}
		
		if($this->abilityType!="") {
			$where.="AND tnk_ability.ability_type=$this->abilityType ";
			if($this->abilityID) $where.="AND tnk_ability.ability_ID=$this->abilityID ";
		}
		
		$cardName=esc_sql($this->cardName);
		if($this->theme!="") {
			$name0_match="";
			$name1_match="";
			$name2_match="";
			if($cardName!="") {
				$name0_match="AND (tnk_card_type0.card_name LIKE '%$cardName%' OR tnk_card_type0.card_kana LIKE '%$cardName%')";
				$name1_match="AND (tnk_card_type1.card_name LIKE '%$cardName%' OR tnk_card_type1.card_kana LIKE '%$cardName%')";
				$name2_match="AND (tnk_card_type2.card_name LIKE '%$cardName%' OR tnk_card_type2.card_kana LIKE '%$cardName%')";
			}
			$where.=""
				."AND (tnk_card_type0.theme_id=$this->theme $name0_match "
				."OR tnk_card_type1.theme_id=$this->theme $name1_match "
				."OR tnk_card_type2.theme_id=$this->theme $name2_match) "
			;
		} else {
			if($cardName!="") {
				$where.=""
					."AND (tnk_card_type0.card_name LIKE '%$cardName%' "
					."OR tnk_card_type1.card_name LIKE '%$cardName%' "
					."OR tnk_card_type2.card_name LIKE '%$cardName%' "
					."OR tnk_card_type0.card_kana LIKE '%$cardName%' "
					."OR tnk_card_type1.card_kana LIKE '%$cardName%' "
					."OR tnk_card_type2.card_kana LIKE '%$cardName%') "
				;
			}
		}
		$where.="GROUP BY tnk_card.card_ID, tnk_card_evolution.card_evolution_ID ";
		return $where;
	}
	
	// 隊士情報クエリORDER句
	private function getOrder() {
		$order="";
		switch($this->sortSkill) {
			case "hidebuAttackUpDESC":
			case "yomeiAttackUpDESC":
			case "shinichiAttackUpDESC":
			case "hidebuDefenceDownDESC":
			case "yomeiDefenceDownDESC":
			case "shinichiDefenceDownDESC":
			case "hidebuDefenceUpDESC":
			case "yomeiDefenceUpDESC":
			case "shinichiDefenceUpDESC":
			case "hidebuAttackDownDESC":
			case "yomeiAttackDownDESC":
			case "shinichiAttackDownDESC":	  $order.="max_rate_sum DESC, "; break;
			case "hidebuDefenceUpASC":
			case "yomeiDefenceUpASC":
			case "shinichiDefenceUpASC":
			case "hidebuAttackDownASC":
			case "yomeiAttackDownASC":
			case "shinichiAttackDownASC":
			case "hidebuAttackUpASC":
			case "yomeiAttackUpASC":
			case "shinichiAttackUpASC":
			case "hidebuDefenceDownASC":
			case "yomeiDefenceDownASC":
			case "shinichiDefenceDownASC":	  $order.="max_rate_sum ASC, ";
		}
		switch($this->sort) {
			case "rarityDESC":	$order.="rarity_ID DESC, ";			  break;
			case "rarityASC":	$order.="rarity_ID ASC, ";			  break;
			case "attackDESC":	$order.="card_rate_attack DESC, ";	  break;
			case "attackASC":	$order.="card_rate_attack ASC, ";	  break;
			case "defenceDESC": $order.="card_rate_defence DESC, ";	  break;
			case "defenceASC":	$order.="card_rate_defence ASC, ";	  break;
			case "totalDESC":	$order.="card_rate_total DESC, ";	  break;
			case "totalASC":	$order.="card_rate_total ASC, ";	  break;
		}
		if($this->sortID=="DESC") $order.="tnk_card.card_ID DESC, tnk_card_evolution.card_evolution_ID DESC";
		else $order.="tnk_card.card_ID ASC, tnk_card_evolution.card_evolution_ID ASC";
		return "ORDER BY $order ";
	}
	
	// 隊士情報クエリLIMIT句
	private function getLimit() {
		$offset=$this->offset-1;
		return "LIMIT $offset, $this->rows";
	}
}
?>