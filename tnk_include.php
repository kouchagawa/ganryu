<?php
// 隊士・戦技検索機能用includeファイル

// 定数
if(strpos($_SERVER["HTTP_HOST"], 'localhost')===false) {
	define('IS_LOCAL', false);
	define("EOL", "");
} else {
	define('IS_LOCAL', true);
	define("EOL", PHP_EOL);
}
define("XREA_FREE", true);
define("IS_SITEKIT", true);
define("COMMON_IMG_PASS", "/tnk47/ratio20/images/common/");
define("COMMAND_ICON_IMG_PASS", "/tnk47/ratio20/illustrations/command/");
define("COMMAND_ICON_THUMB_PASS", COMMAND_ICON_IMG_PASS."thumb/");
define("CARD_IMG_PASS", "/tnk47/ratio20/illustrations/card/");
define("CARD_THUMB_PASS", CARD_IMG_PASS."thumb/");
define("SEARCH_COMMAND_PASS", "/command/");
define("SEARCH_CARD_PASS", "/card/");
define("REGIST_COMMAND_PASS", "/regist-command/");
define("MAINTENANCE_PASS", "/maintenance/");


// デバッグ用画面表示関数
function view($val, $name="view: ") {
	echo($name.'<br /><pre>');
	var_export($val);
	echo('</pre>');
}

// テンプレート出力時の変数エスケープ処理
function h($str) {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// セレクトボックス基底クラス
class SelectBox {
	protected $name="";				// セレクトボックス名
	protected $arr=[];				// 値の連想配列
	protected $selected;			// 選択済み項目
	protected $id="";				// CSS id名
	protected $class="selectBox";	// CSS クラス名
	
	// HTML文字列を返す
	public function to_s() {
		$id=($this->id) ? " id='{$this->id}'" : "";
		$class=($this->class) ? " class='{$this->class}'" : "";
		$str="<select{$id}{$class} name='{$this->name}'>".EOL;
		foreach($this->arr as $row) {
			$value="";
			$label="";
			if(!$row["value"] && $row["label"]) {
				$value.=" value='".$row["value"]."'";
				$label.=$row["label"];
			} elseif($row["value"] && $row["label"]) {
				$selected=($row["value"]==$this->selected) ? " selected" : "";
				$value.=" value='".$row["value"]."'$selected";
				$label=$row["label"];
			}
			$str.="<option$value>$label</option>".EOL;
		}
		$str.="</select>";
		return $str;
	}
}

// チェックボックス基底クラス
class CheckBox {
	protected $name="";				// チェックボックス名
	protected $value="";			// 値
	protected $label="";			// ラベル
	protected $checked="";			// チェックフラグ
	protected $id="";				// CSS id名
	protected $class="checkBox";	// CSS クラス名
	
	// HTML文字列を返す
	public function to_s() {
		$id=($this->id) ? " id='{$this->id}'" : "";
		$class=($this->class) ? " class='{$this->class}'" : "";
		$checked=($this->checked) ? " checked='checked'" : "";
		$str="<label><input{$id}{$class} type='checkbox' name='$this->name' value='$this->value'$checked>$this->label</label>";
		return $str;
	}
}

// 管理画面用チェックボックス
class CheckBoxExcute extends CheckBox {
	// コンストラクタ
	public function __construct($name, $value, $label) {
		$this->name=$name;
		$this->value=$value;
		$this->label=$label;
	}
}

// ラジオボタン基底クラス
class RadioButton {
	protected $name="";				// ラジオボタン名
	protected $arr=[];				// 値の連想配列
	protected $checked="";			// 選択済み項目
	protected $id="";				// CSS id名
	protected $class="radioButton"; // CSS クラス名

	// HTML文字列を返す
	public function to_s() {
		$id=($this->id) ? " id='{$this->id}'" : "";
		$class=($this->class) ? " class='{$this->class}'" : "";
		$str="";
		foreach($this->arr as $row) {
			$checked=($row["value"]==$this->checked) ? " checked" : "";
			$str.="<label><input{$id}{$class} type='radio' name='$this->name' value='".$row["value"]."'$checked>".$row["label"]."</label>";
		}
		return $str;
	}
}
?>