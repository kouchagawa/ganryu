<?php
require_once "tnk_include.php";

// 親カテゴリセレクトボックス
class SelectBoxParentCategory extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue) {
		$this->name="parentCategory";
		$this->class="parentCategory";
		$this->selected=$selectedValue;
		
		$this->arr=[
			["value"=>"", "label"=>"全系統"],
			["value"=>"attack", "label"=>"攻撃系"],
			["value"=>"defence", "label"=>"防御系"],
			["value"=>"buff", "label"=>"強化系"],
			["value"=>"debuff", "label"=>"弱体系"],
			["value"=>"others", "label"=>"その他"],
		];
	}
}

// 子カテゴリセレクトボックス
class SelectBoxCategory extends SelectBox {
	// コンストラクタ
	public function __construct($selectedValue, $parentType) {
		$this->name="category";
		$this->class="category";
		$this->selected=$selectedValue;
		$type=0;
		$name="";
		
		switch($parentType) {
			case "attack":	$type=1; $name="攻撃系";	break;
			case "defence":	$type=2; $name="防御系";	break;
			case "buff":	$type=3; $name="強化系";	break;
			case "debuff":	$type=4; $name="弱体系";	break;
			case "others":	$type=5; $name="その他";	break;
		}
		
		$where="WHERE ";
		if($type!=0) {
			$where.="command_category_type=$type";
			$this->arr[0]=["value"=>"", "label"=>"{$name}全て"];
		} else {
			$where.="command_category_ID!=0";
			$this->arr[0]=["value"=>"", "label"=>"全カテゴリー"];
		}
		
		$query="
			SELECT command_category_ID, command_category_name
			FROM tnk_command_category
			{$where}
			ORDER BY command_category_type, command_category_order
		";
		global $wpdb;
		$results=$wpdb->get_results($query);
		
		foreach($results as $row) {
			$this->arr[]=["value"=>$row->command_category_ID, "label"=>"$row->command_category_name"];
		}
	}
}

// TODO絞り込みチェックボックス
class CheckBoxTodo extends CheckBox {
	// コンストラクタ
	public function __construct($checkedFlag) {
		$this->name="todo";
		$this->value="on";
		$this->label="未検証項目のある戦技";
		$this->checked=$checkedFlag;
	}
}

// 戦技効果解説テキスト選択ラジオボタン
class RadioButtonDescribe extends RadioButton {
	// コンストラクタ
	public function __construct($checkedValue) {
		$this->name="describe";
		$this->checked=$checkedValue;
		$this->arr=[
			["value"=>"official", "label"=>"公式"],
			["value"=>"detail", "label"=>"詳細"],
		];
	}
}

// ソート選択ラジオボタン
class RadioButtonSort extends RadioButton {
	// コンストラクタ
	public function __construct($checkedValue) {
		$this->name="sort";
		$this->checked=$checkedValue;
		$this->arr=[
			["value"=>"officialASC", "label"=>"公式順"],
			//["value"=>"officialDESC", "label"=>"公式逆順"],
			["value"=>"ASC", "label"=>"ID昇順"],
			["value"=>"DESC", "label"=>"ID降順"],
		];
	}
}

// 戦技検索画面
class SearchCommand {
	private $id="";				// ID検索 整数値の入力があればそのIDの戦技を検索
	private $commandName="";	// 戦技名検索文字列
	private $parentCategory="";	// 親カテゴリ attack:攻撃系 defence:防御系 buff:強化系 debuff:弱体系 others:その他
	private $category="";		// 子カテゴリ カテゴリ番号
	private $todo="";			// TODO検索 値なし:TODO絞り込み検索なし 値あり:TODO絞り込み検索
	private $describe="detail"; // official:公式戦技効果解説テキスト detail:戦技効果詳細表示
	private $sort="officialASC";// ソート officialASC:公式戦技紹介並び順 (officialDESC:公式逆順) ASC:ID昇順 DESC:ID降順
	private $offset="1";		// 検索結果のオフセット
	private $rows="10";			// 一度に表示する戦技数
	private $message="";		// エラーメッセージ
	private $html="";			// 戦技検索画面HTML
	
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
		wp_enqueue_script("command", get_theme_file_uri("/tnk/js/command.js"), array(), filemtime(get_theme_file_path("/tnk/js/command.js")), true);
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
				["name"=>"id", "message"=>"11以上9999以下の整数値を入力してください", "regex"=>"/\A(|1[1-9]|[2-9][0-9]|[1-9][0-9]{2,3})\z/"],
				["name"=>"commandName", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"<\A[^\x01-\x1F\x21-\x7f]{0,50}\z>"],
				["name"=>"parentCategory", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,7}\z/"],
				["name"=>"category", "message"=>"0以上999以下の整数値を入力してください", "regex"=>"/\A(|[0-9]|[1-9][0-9]{1,2})\z/"],
				["name"=>"todo", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,2}\z/"],
				["name"=>"describe", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,8}\z/"],
				["name"=>"sort", "message"=>"文字列が長すぎるか使用できない文字が含まれています", "regex"=>"/\A[[:alpha:]]{0,12}\z/"],
				["name"=>"offset", "message"=>"1以上9999以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|[1-9][0-9]{1,3})\z/"],
				["name"=>"rows", "message"=>"1以上100以下の整数値を入力してください", "regex"=>"/\A(|[1-9]|[1-9][0-9]|100)\z/"],
			];
			
			// 入力値範囲チェック
			foreach($list as $row) {
				$name=$row["name"];
				if(!preg_match($row["regex"], $this->$name)) {
					$this->message.="<p class='warning'>{$name}: {$row["message"]}</p>".EOL;
				}
			}
		}
	}
	
	// 総戦技数取得
	private function getNumber() {
		global $wpdb;	// WordPressのDBクラスのインスタンス
		$result=$wpdb->get_results("SELECT COUNT(*) AS num FROM tnk_command");
		$num=$result[0]->num-1;
		$this->html.="<p class='num'>現在の登録戦技数:&nbsp;{$num}</p>".EOL;
	}
	
	// フォーム
	private function getForm() {
		// 検索結果HTMLに検索フォームを追加
		$RadioButtonSort=new RadioButtonSort($this->sort);
		$RadioButtonDescribe=new RadioButtonDescribe($this->describe);
		$CheckBoxTodo=new CheckBoxTodo($this->todo);
		$SelectBoxParentCategory=new SelectBoxParentCategory($this->parentCategory);
		$SelectBoxCategory=new SelectBoxCategory($this->category, $this->parentCategory);
		$commandName=esc_html($this->commandName);
		
		$this->html.=""
			."<form id='formCommand' class='form' action='".SEARCH_COMMAND_PASS."#under-form' method='get'>".EOL
			."<div>".EOL
			."<p class='label'>絞り込み</p>".EOL
			."<p><input class='searchText' type='text' name='commandName' value='{$commandName}' placeholder='戦技名 or よみがな …'></p>".EOL
			."<p>{$SelectBoxParentCategory->to_s()} <span id='categoryListContainer' class='dynamicCategoryList'>{$SelectBoxCategory->to_s()}</span></p>".EOL
			."<p>{$CheckBoxTodo->to_s()}</p>".EOL
			."</div>".EOL
			."<div>".EOL
			."<p class='label'>戦技効果解説テキスト</p>".EOL
			."<p>{$RadioButtonDescribe->to_s()}</p>".EOL
			."</div>".EOL
			."<div>".EOL
			."<p class='label'>並び順</p>".EOL
			."<p>{$RadioButtonSort->to_s()}</p>".EOL
			."</div>".EOL
			."<p><input type='submit' value='検索'></p>".EOL
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
			$prev="<a href='{$locate}&offset={$offset}&rows={$this->rows}#under-form'>←前</a>";
		} elseif(1<$this->offset) {
			$prev="<a href='{$locate}&offset=1&rows={$this->rows}#under-form'>←前</a>";
		}
		$next="";
		if(($this->offset+$this->rows)<=$foundRows) {
			$offset=$this->offset+$this->rows;
			$next="<a href='{$locate}&offset={$offset}&rows={$this->rows}#under-form'>次→</a>";
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
				$link="{$prev}{$foundRows}件中{$show}{$next}";
			} else {
				// オフセットがヒット件数を超えていればエラー
				$this->message.="<p class='warning'>offset: ヒット件数を超えています.</p>";
			}
		} else {
			$link="検索結果0件";
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
				$arr[]="$key=$value";
			}
		}
		return implode("&", $arr);
	}
	
	// 戦技情報
	private function getInfo() {
		// DBから戦技情報を取得
		global $wpdb;
		$query="SELECT SQL_CALC_FOUND_ROWS * FROM tnk_command_view ".$this->getWhere().$this->getOrder().$this->getLimit();
		$results=$wpdb->get_results($query);
		$resultsRows=$wpdb->get_results("SELECT FOUND_ROWS() AS found_rows");
		//echo("query:<pre>$query</pre>");
		//view($results, "results: ");
		
		// URLクエリ文字列共通部分
		$keys=array("commandName","parentCategory","category","todo","describe","sort");
		$urlQueryStrings=$this->urlQueryStrings($keys);
		if($urlQueryStrings!="") $urlQueryStrings="?{$urlQueryStrings}";
		
		// ID検索でなければページングを表示
		if(!$this->id) {
			$paging=$this->paging($resultsRows[0]->found_rows, SEARCH_COMMAND_PASS.$urlQueryStrings);
			$paging="<p class='paging'>{$paging}</p>".EOL;
			$this->html.=$paging;
		}
		
		// 戦技情報検索結果から表示内容をセット
		foreach($results as $row) {
			// 詳細情報へのリンク
			$a=$ai=$ac="";
			if($this->id=="") {
				$idSearchLocate=SEARCH_COMMAND_PASS."{$urlQueryStrings}&id={$row->command_id}#under-form";
				$a="<a href='{$idSearchLocate}'>";
				$ai="<a class='image' href='{$idSearchLocate}'>";
				$ac="</a>";
			}
			
			// 画像
			$thumbLocate=COMMAND_ICON_THUMB_PASS."cs_{$row->command_id}_{$row->icon}_thumb.png";
			
			// 戦技名・読み方・コスト
			$name=$row->name;
			$kana=$row->kana;
			$cost=$row->cost;
			
			// 制限回数
			if($row->limit_use=="") $limitUse="";
			else $limitUse="<p><span class='label'>使用制限</span>{$row->limit_use}</p>";
			
			$text=$todo="";
			if($this->describe=="official") {
				// 公式の戦技効果テキスト
				$text.=$row->official_text;
			} else {
				// 戦技効果ではなく、戦技自体の備考とTODO
				if($row->notes!="") $text.=$row->notes;
				if($this->todo!="" && $row->todo!="") {
					$todo.="<p><span class='label'>TODO</span></p>".EOL;
					$todo.="<p class='text'>{$row->todo}</p>".EOL;
				}
			}
			
			// テキストの内容があればタグで囲む
			if($text) {
				$text="<p class='text'>{$text}</p>".EOL;
			}
			
			// テキストもしくはTODOの内容があればタグで囲む
			if($text || $todo) {
				$text=""
					."<div class='headerText'>".EOL
					.$text
					.$todo
					."</div>".EOL
				;
			}
			
			// 詳細検索もしくはID検索の場合は戦技効果を表示
			$effectInfo="";
			if($this->describe=="detail" || $this->id!="") {
				for($i=1; $i<=5; $i++) {
					$e_id="e{$i}_id";
					if($row->$e_id!=0) $effectInfo.=$this->getEffectInfo($row, $i).EOL;
				}
			}
			
			// ID検索の場合所持隊士を表示
			$card="";
			if($this->id) {
				$query=""
					."SELECT "
					."tnk_card.card_ID, "
					."MAX(tnk_card_evolution.card_evolution_ID) AS card_evolution_ID, "
					."card_name, "
					."rarity_name "
					."FROM tnk_card_evolution "
					."JOIN tnk_card ON tnk_card_evolution.card_id=tnk_card.card_ID "
					."JOIN tnk_rarity ON tnk_card_evolution.rarity_id=tnk_rarity.rarity_ID "
					."LEFT JOIN tnk_card_type AS tnk_card_type0 "
					."ON tnk_card_evolution.card_id=tnk_card_type0.card_id "
					."AND tnk_card_evolution.card_evolution_ID=tnk_card_type0.card_evolution_id "
					."AND tnk_card_type0.card_type_ID=0 "
					."WHERE command_id=$this->id GROUP BY card_ID"
				;
				$resultsCard=$wpdb->get_results($query);
				if($wpdb->num_rows>0) {
					$card.="<div class='detail'>".EOL."<p><span class='label'>所有隊士</span></p>".EOL;
					foreach($resultsCard as $rowCard) {
						$rarityImg="";
						if($rowCard->rarity_name) {
							$src=COMMON_IMG_PASS."{$rowCard->rarity_name}.png";
							$rarityImg="<img class='rarity' src='{$src}'>";
						}
						$id=$rowCard->card_ID.$rowCard->card_evolution_ID;
						$href=SEARCH_CARD_PASS."?id={$id}#under-form";
						$card.="<p>{$rarityImg}<a href='{$href}'><span class='card'>{$rowCard->card_name}</span></a></p>".EOL;
					}
					$card.="</div>".EOL;
				}
			}
			
			// 戦技情報表示内容
			$this->html.=""
				.EOL."<div class='infoCommand' id='infoCommand'>".EOL
				."<div class='header'>".EOL
				."{$ai}<figure class='icon'><img src='{$thumbLocate}'></figure>{$ac}".EOL
				."<div class='headline'>".EOL
				."<p class='name'>{$a}{$name}{$ac}</p>".EOL
				."<p class='kana'>{$kana}</p>".EOL
				."<p>".EOL
				."<span class='label'>消費P</span>".EOL
				."<span class='commandCost'>".EOL
				."<span class='spend cost{$cost}'></span>".EOL
				."<span class='partition part1'></span>".EOL
				."<span class='partition part2'></span>".EOL
				."</span>".EOL
				."</p>".EOL
				.$limitUse
				."</div>".EOL
				.$text
				."</div>".EOL
				.$effectInfo
				.$card
				."</div>".EOL;
			;
		}
		
		// 検索結果下側のページング
		if($this->id=="" && $resultsRows[0]->found_rows>0 && $resultsRows[0]->found_rows>=$this->offset) {
			$this->html.=$paging;
		}
	}
	
	// 戦技効果情報
	private function getEffectInfo(&$row, $offset) {
		$prefix="e{$offset}";
		$id="{$prefix}_id";
		$time="{$prefix}_time";
		$text="{$prefix}_text";
		$notes="{$prefix}_notes";
		$todo="{$prefix}_todo";
		$c1_id="{$prefix}c1_id";
		$c1_name="{$prefix}c1_name";
		$c1_notes="{$prefix}c1_notes";
		$c1_todo="{$prefix}c1_todo";
		$c2_id="{$prefix}c2_id";
		$c2_name="{$prefix}c2_name";
		$c2_notes="{$prefix}c2_notes";
		$c2_todo="{$prefix}c2_todo";
		$c3_id="{$prefix}c3_id";
		$c3_name="{$prefix}c3_name";
		$c3_notes="{$prefix}c3_notes";
		$c3_todo="{$prefix}c3_todo";
		$c4_id="{$prefix}c4_id";
		$c4_name="{$prefix}c4_name";
		$c4_notes="{$prefix}c4_notes";
		$c4_todo="{$prefix}c4_todo";
		
		// 有効期間
		$effectTime="";
		if($row->$time!="") $effectTime=$row->$time;
		else $effectTime="なし";
		$effectTime="　<span class='label'>効果時間</span>$effectTime".
		
		// 戦技カテゴリー
		$categoryName="";
		if($row->$c1_name!="") $categoryName.="<p class='text'><a href='".SEARCH_COMMAND_PASS."?category={$row->$c1_id}#under-form'>{$row->$c1_name}</a></p>".EOL;
		if($row->$c2_name!="") $categoryName.="<p class='text'><a href='".SEARCH_COMMAND_PASS."?category={$row->$c2_id}#under-form'>{$row->$c2_name}</a></p>".EOL;
		if($row->$c3_name!="") $categoryName.="<p class='text'><a href='".SEARCH_COMMAND_PASS."?category={$row->$c3_id}#under-form'>{$row->$c3_name}</a></p>".EOL;
		if($row->$c4_name!="") $categoryName.="<p class='text'><a href='".SEARCH_COMMAND_PASS."?category={$row->$c4_id}#under-form'>{$row->$c4_name}</a></p>".EOL;
		
		// 注釈
		$effectNotes="";
		if($row->$notes!="") $effectNotes.="<p class='text'>{$row->$notes}</p>".EOL;
		if($row->$c1_notes!="") $effectNotes.="<p class='text'>{$row->$c1_notes}</p>".EOL;
		if($row->$c2_notes!="") $effectNotes.="<p class='text'>{$row->$c2_notes}</p>".EOL;
		if($row->$c3_notes!="") $effectNotes.="<p class='text'>{$row->$c3_notes}</p>".EOL;
		if($row->$c4_notes!="") $effectNotes.="<p class='text'>{$row->$c4_notes}</p>".EOL;
		if($effectNotes!="") $effectNotes="<p><span class='label'>注釈</span></p>".EOL.$effectNotes;
		
		// TODO
		$effectTodo="";
		if($this->todo!="" || $this->id!="") {
			if($row->$todo!="") $effectTodo.="<p class='text'>{$row->$todo}</p>".EOL;
			if($row->$c1_todo!="") $effectTodo.="<p class='text'>{$row->$c1_todo}</p>".EOL;
			if($row->$c2_todo!="") $effectTodo.="<p class='text'>{$row->$c2_todo}</p>".EOL;
			if($row->$c3_todo!="") $effectTodo.="<p class='text'>{$row->$c3_todo}</p>".EOL;
			if($row->$c4_todo!="") $effectTodo.="<p class='text'>{$row->$c4_todo}</p>".EOL;
			if($effectTodo!="") $effectTodo="<p><span class='label'>TODO</span></p>".EOL.$effectTodo;
		}
		
		// 戦技効果情報
		return ""
			."<div class='detail'>".EOL
			."<p><span class='label'>戦技効果ID</span>{$row->$id}{$effectTime}</p>".EOL
			."<p><span class='label'>戦技カテゴリー</span>{$categoryName}</p>".EOL
			."<p><span class='label'>戦技効果</span></p>".EOL
			."<p class='text'>{$row->$text}</p>".EOL
			.$effectNotes
			.$effectTodo
			."</div>".EOL;
		;
	}
	
	// 戦技情報クエリWHERE句
	private function getWhere() {
		$where="WHERE command_id!=0 ";
		if($this->id!="" ) $where.="AND command_id={$this->id} ";
		
		if($this->category){
			// 子カテゴリの指定があれば子カテゴリでの絞り込み
			$where.=""
				."AND (e1c1_id=$this->category "
				."OR e1c2_id=$this->category "
				."OR e1c3_id=$this->category "
				."OR e1c4_id=$this->category "
				."OR e2c1_id=$this->category "
				."OR e2c2_id=$this->category "
				."OR e2c3_id=$this->category "
				."OR e2c4_id=$this->category "
				."OR e3c1_id=$this->category "
				."OR e3c2_id=$this->category "
				."OR e3c3_id=$this->category "
				."OR e3c4_id=$this->category "
				."OR e4c1_id=$this->category "
				."OR e4c2_id=$this->category "
				."OR e4c3_id=$this->category "
				."OR e4c4_id=$this->category) "
			;
		} else {
			// 子カテゴリの指定がなければ親カテゴリでの絞り込み
			$type=0;
			switch($this->parentCategory) {
				case "attack":	$type=1;	break;
				case "defence":	$type=2;	break;
				case "buff":	$type=3;	break;
				case "debuff":	$type=4;	break;
				case "others":	$type=5;	break;
			}
			
			$where.=""
				."AND (e1c1_type=$type "
				."OR e1c2_type=$type "
				."OR e1c3_type=$type "
				."OR e1c4_type=$type "
				."OR e2c1_type=$type "
				."OR e2c2_type=$type "
				."OR e2c3_type=$type "
				."OR e2c4_type=$type "
				."OR e3c1_type=$type "
				."OR e3c2_type=$type "
				."OR e3c3_type=$type "
				."OR e3c4_type=$type "
				."OR e4c1_type=$type "
				."OR e4c2_type=$type "
				."OR e4c3_type=$type "
				."OR e4c4_type=$type) "
			;
		}
		
		if($this->todo=="on") {
			// TODOの指定があればTODO絞り込み
			$where.=""
				."AND (todo!='' "
				."OR e1_todo!='' "
				."OR e2_todo!='' "
				."OR e3_todo!='' "
				."OR e4_todo!='' "
				."OR e1c1_todo!='' "
				."OR e1c2_todo!='' "
				."OR e1c3_todo!='' "
				."OR e1c4_todo!='' "
				."OR e2c1_todo!='' "
				."OR e2c2_todo!='' "
				."OR e2c3_todo!='' "
				."OR e2c4_todo!='' "
				."OR e3c1_todo!='' "
				."OR e3c2_todo!='' "
				."OR e3c3_todo!='' "
				."OR e3c4_todo!='' "
				."OR e4c1_todo!='' "
				."OR e4c2_todo!='' "
				."OR e4c3_todo!='' "
				."OR e4c4_todo!='') "
			;
		}
		
		// 名前もしくはよみがなで絞り込み
		if($this->commandName!="") {
			$where.="AND (name LIKE '%$this->commandName%' ";
			$where.="OR kana LIKE '%$this->commandName%')";
		}
		return $where;
	}
	
	// 戦技情報クエリORDER句
	private function getOrder() {
		$order="ORDER BY ";
		if($this->sort=="DESC") $order.="command_id DESC ";
		elseif($this->sort=="ASC") $order.="command_id ASC ";
		//elseif($this->sort=="officialDESC") $order.="official_type DESC, official_order DESC ";
		else $order.="official_type ASC, official_order ASC ";
		return $order;
	}
	
	// 戦技情報クエリLIMIT句
	private function getLimit() {
		$offset=$this->offset-1;
		return "LIMIT $offset, $this->rows";
	}
}

// 戦技リスト画面
class CommandList {
}

// 戦技詳細画面
class CommandDetail {
}
?>