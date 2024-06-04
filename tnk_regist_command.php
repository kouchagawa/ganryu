<?php
require_once "tnk_include.php";

// カテゴリーセレクトボックス
class SelectBoxCategorySimple extends SelectBox {
    // コンストラクタ
    public function __construct($name, $selectedValue) {
        $this->name=$name;
        $this->selected=$selectedValue;

        global $wpdb;
        $query=""
            ."SELECT command_category_ID, command_category_name "
            ."FROM tnk_command_category "
            ."ORDER BY command_category_type, command_category_order"
        ;
        $results=$wpdb->get_results($query);

        foreach($results as $row) {
            if($row->command_category_ID==0) {
                $this->arr[]=["value"=>0, "label"=>"無し"];
            } else {
                $this->arr[]=["value"=>$row->command_category_ID, "label"=>$row->command_category_name];
            }
        }
    }
}

// コストセレクトボックス
class SelectBoxCommandCost extends SelectBox {
    // コンストラクタ
    public function __construct($selectedValue) {
        $this->name="cost";
        $this->selected=$selectedValue;
        for($i=0; $i<=3; $i++)  $this->arr[]=["value"=>"$i", "label"=>"コスト$i"];
    }
}

// 公式カテゴリーセレクトボックス
class SelectBoxOfficialType extends SelectBox {
    // コンストラクタ
    public function __construct($selectedValue) {
        $this->name="officialType";
        $this->selected=$selectedValue;
        $this->arr=[
            ["value"=>"1", "label"=>"攻撃系"],
            ["value"=>"2", "label"=>"防御系"],
            ["value"=>"3", "label"=>"その他"],
        ];
    }
}

// 戦技効果適用チェックボックス
class CheckBoxApply extends CheckBox {
    // コンストラクタ
    public function __construct($name, $checkedFlag) {
        $this->name=$name;              // チェックボックス名
        $this->value="on";              // 値
        $this->label="適用";            // ラベル
        $this->checked=$checkedFlag;    // チェックフラグ
    }
}

// 戦技登録画面
class RegistCommand {
    private $id="";                 // 戦技ID　整数値
    private $commandName="";        // コマンド名 文字列
    private $commandKana="";        // コマンド名かな 文字列
    private $icon="";               // アイコンURL 文字列 cs_??_xxxxxx_thumb.png のうちの『xxxxxx』の部分
    private $cost="";               // 消費P 整数値 0〜3
    private $limit="";              // 使用制限 文字列 『5回』など
    private $notes="";              // 備考 文字列 戦技内に含まれる戦技効果全体の備考
    private $officialType="";       // 公式カテゴリー 整数値 1:攻撃系 2:防御系 3:その他
    private $officialText="";       // 公式の戦技効果説明テキスト 文字列
    private $officialOrder="";      // 公式並び順挿入位置 整数値

    private $category00="";         // 戦技効果0カテゴリー0 整数値
    private $category01="";         // 戦技効果0カテゴリー1 整数値
    private $effectTime0;           // 戦技効果0有効期間 文字列 『1分30秒』あるいは『次回攻撃まで』など
    private $effectText0;           // 戦技効果0説明テキスト 文字列
    private $effectNotes0;          // 戦技効果0備考 文字列

    private $apply1="";             // 戦技効果1適用 文字列 on:適用 値なし:非適用
    private $category10="";         // 戦技効果1カテゴリー0 整数値
    private $category11="";         // 戦技効果1カテゴリー1 整数値
    private $effectTime1;           // 戦技効果1有効期間 文字列 『1分30秒』あるいは『次回攻撃まで』など
    private $effectText1;           // 戦技効果1説明テキスト 文字列
    private $effectNotes1;          // 戦技効果1備考 文字列

    private $apply2="";             // 戦技効果2適用 文字列 値あり:適用 値なし:非適用
    private $category20="";         // 戦技効果2カテゴリー0 整数値
    private $category21="";         // 戦技効果2カテゴリー1 整数値
    private $effectTime2;           // 戦技効果2有効期間 文字列 『1分30秒』あるいは『次回攻撃まで』など
    private $effectText2;           // 戦技効果2説明テキスト 文字列
    private $effectNotes2;          // 戦技効果2備考 文字列

    private $apply3="";             // 戦技効果3適用 文字列 値あり:適用 値なし:非適用
    private $category30="";         // 戦技効果3カテゴリー0 整数値
    private $category31="";         // 戦技効果3カテゴリー1 整数値
    private $effectTime3;           // 戦技効果3有効期間 文字列 『1分30秒』あるいは『次回攻撃まで』など
    private $effectText3;           // 戦技効果3説明テキスト 文字列
    private $effectNotes3;          // 戦技効果3備考 文字列

    public $html;                   // 戦技検索画面HTML

    // コンストラクタ
    public function __construct() {
        // スーパーグローバル変数の値をクラス変数に受け取る
        $form="";
        $message="";
        $info="";
        if(empty($_POST)) {
            $form=$this->getForm();
        } else {
            // スーパーグローバル変数の値が入力されている場合
            // スーパーグローバル変数の値をクラス変数で受け取る
            foreach($_POST as $key=>$val) {
                $this->$key=$val;
            }
            
            // 入力値範囲チェック
            if(!ctype_digit($this->id)) {
                $message.="<p class='warning'>id: 整数値を入力してください.</p>".EOL;
            } elseif($this->id<1) {
                $message.="<p class='warning'>id: 1以上の整数値を入力してください.</p>".EOL;
            } elseif($this->commandName=="") {
                $message.="<p class='warning'>commandName: 戦技名を入力してください.</p>".EOL;
            } elseif($this->commandKana=="") {
                $message.="<p class='warning'>commandKana: 読み方を入力してください.</p>".EOL;
            } elseif($this->icon=="") {
                $message.="<p class='warning'>icon: アイコンURLを入力してください.</p>".EOL;
            } elseif(!ctype_digit($this->cost)) {
                $message.="<p class='warning'>cost: 整数値を入力してください.</p>".EOL;
            } elseif(!ctype_digit($this->officialType)) {
                $message.="<p class='warning'>officialType: 整数値を入力してください.</p>".EOL;
            } elseif(!ctype_digit($this->officialOrder)) {
                $message.="<p class='warning'>officialOrder: 整数値を入力してください.</p>".EOL;
            } elseif($this->officialOrder<1) {
                $message.="<p class='warning'>officialOrder: 1以上の整数値を入力してください.</p>".EOL;
            } elseif($this->category00<=0) {
                $message.="<p class='warning'>category00: 1以上の整数値を入力してください.</p>".EOL;
            } elseif($this->apply1=="on" && $this->category10<=0) {
                $message.="<p class='warning'>category10: 1以上の整数値を入力してください.</p>".EOL;
            } elseif($this->apply2=="on" && $this->category20<=0) {
                $message.="<p class='warning'>category20: 1以上の整数値を入力してください.</p>".EOL;
            } elseif($this->apply3=="on" && $this->category30<=0) {
                $message.="<p class='warning'>category30: 1以上の整数値を入力してください.</p>".EOL;
            } else {
                // 戦技登録
                $query=$this->insert();
                if($query=="") {
                    $message.="<p class='announce'>戦技ID$this->id: tnk_command, tnk_command_effect のINSERTに成功しました</p>".EOL;
                    $message.="<p class='announce'><a href='".REGIST_COMMAND_PASS."'>戻る</a></p>".EOL;
                } else {
                    $message.="<p class='warning'>Error: クエリが失敗しました: $query</p>".EOL;
                    $form=$this->getForm();
                }
            }
        }

        // 戦技登録画面HTML作成
        $this->html=$form.$message;
    }

    // フォーム
    // 備忘録 HTMLのサニタイズは関数 htmlspecialchars()
    // 例: echo htmlspecialchars($html, ENT_QUOTES, "UTF-8");
    private function getForm() {        
        // 検索結果HTMLに検索フォームを追加
        $SelectBoxCommandCost=new SelectBoxCommandCost($this->cost);
        $SelectBoxOfficialType=new SelectBoxOfficialType($this->officialType);
        $SelectBoxCategory00=new SelectBoxCategorySimple("category00", $this->category00);
        $SelectBoxCategory01=new SelectBoxCategorySimple("category01", $this->category01);
        $SelectBoxCategory10=new SelectBoxCategorySimple("category10", $this->category10);
        $SelectBoxCategory11=new SelectBoxCategorySimple("category11", $this->category11);
        $SelectBoxCategory20=new SelectBoxCategorySimple("category20", $this->category20);
        $SelectBoxCategory21=new SelectBoxCategorySimple("category21", $this->category21);
        $SelectBoxCategory30=new SelectBoxCategorySimple("category30", $this->category30);
        $SelectBoxCategory31=new SelectBoxCategorySimple("category31", $this->category31);
        $CheckBoxApply1=new CheckBoxApply("apply1", $this->apply1);
        $CheckBoxApply2=new CheckBoxApply("apply2", $this->apply2);
        $CheckBoxApply3=new CheckBoxApply("apply3", $this->apply3);
        return ""
            ."<form class='form' action='".REGIST_COMMAND_PASS."' method='post'>".EOL
            ."<div>".EOL
            ."<p class='label'>戦技登録</p>".EOL
            ."<p>戦技ID<input type='text' name='id' value='$this->id'></p>".EOL
            ."<p>戦技名<input type='text' name='commandName' value='$this->commandName'></p>".EOL
            ."<p>よみがな<input type='text' name='commandKana' value='$this->commandKana'></p>".EOL
            ."<p>アイコンURLのローマ字の戦技名部分<input type='text' name='icon' value='$this->icon'></p>".EOL
            ."<p>コスト{$SelectBoxCommandCost->to_s()}</p>".EOL
            ."<p>使用制限<br />(ex.記入なし,x回,自軍全体でx回)<input type='text' name='limit' value='$this->limit'></p>".EOL
            ."<p>備考<textarea name='notes'>$this->notes</textarea></p>".EOL
            ."<p>公式カテゴリー{$SelectBoxOfficialType->to_s()}</p>".EOL
            ."<p>公式戦技効果テキスト<textarea name='officialText'>$this->officialText</textarea></p>".EOL
            ."<p>公式並び順挿入位置<br />(公式カテゴリーごとの順位)<input type='text' name='officialOrder' value='$this->officialOrder'></p>".EOL
            ."</div>".EOL
            ."<div>".EOL
            ."<p class='label'>戦技効果IDオフセット0</p>".EOL
            ."<p>カテゴリー1{$SelectBoxCategory00->to_s()}</p>".EOL
            ."<p>カテゴリー2{$SelectBoxCategory01->to_s()}</p>".EOL
            ."<p>有効期間<br />(ex.記入なし,x分xx秒,回避失敗まで)<input type='text' name='effectTime0' value='$this->effectTime0'></p>".EOL
            ."<p>戦技効果テキスト<textarea name='effectText0'>$this->effectText0</textarea></p>".EOL
            ."<p>備考<textarea name='effectNotes0'>$this->effectNotes0</textarea></p>".EOL
            ."</div>".EOL
            ."<div>".EOL
            ."<p class='label'>戦技効果IDオフセット1</p>".EOL
            ."<p>{$CheckBoxApply1->to_s()}</p>".EOL
            ."<p>カテゴリー1{$SelectBoxCategory10->to_s()}</p>".EOL
            ."<p>カテゴリー2{$SelectBoxCategory11->to_s()}</p>".EOL
            ."<p>有効期間<br />(ex.記入なし,x分xx秒,回避失敗まで)<input type='text' name='effectTime1' value='$this->effectTime1'></p>".EOL
            ."<p>戦技効果テキスト<textarea name='effectText1'>$this->effectText1</textarea></p>".EOL
            ."<p>備考<textarea name='effectNotes1'>$this->effectNotes1</textarea></p>".EOL
            ."</div>".EOL
            ."<div>".EOL
            ."<p class='label'>戦技効果IDオフセット2</p>".EOL
            ."<p>{$CheckBoxApply2->to_s()}</p>".EOL
            ."<p>カテゴリー1{$SelectBoxCategory20->to_s()}</p>".EOL
            ."<p>カテゴリー2{$SelectBoxCategory21->to_s()}</p>".EOL
            ."<p>有効期間<br />(ex.記入なし,x分xx秒,回避失敗まで)<input type='text' name='effectTime2' value='$this->effectTime2'></p>".EOL
            ."<p>戦技効果テキスト<textarea name='effectText2'>$this->effectText2</textarea></p>".EOL
            ."<p>備考<textarea name='effectNotes2'>$this->effectNotes2</textarea></p>".EOL
            ."</div>".EOL
            ."<div>".EOL
            ."<p class='label'>戦技効果IDオフセット3</p>".EOL
            ."<p>{$CheckBoxApply3->to_s()}</p>".EOL
            ."<p>カテゴリー1{$SelectBoxCategory30->to_s()}</p>".EOL
            ."<p>カテゴリー2{$SelectBoxCategory31->to_s()}</p>".EOL
            ."<p>有効期間<br />(ex.記入なし,x分xx秒,回避失敗まで)<input type='text' name='effectTime3' value='$this->effectTime3'></p>".EOL
            ."<p>戦技効果テキスト<textarea name='effectText3'>$this->effectText3</textarea></p>".EOL
            ."<p>備考<textarea name='effectNotes3'>$this->effectNotes3</textarea></p>".EOL
            ."</div>".EOL
            ."<p><input type='submit' value='登録'></p>".EOL
            ."</form>".EOL
        ;
    }

    // DB登録
    // 戻り値 成功時:空文字列 失敗時:失敗したクエリの文字列
    private function insert() {
        global $wpdb;       // WordPressのDBクラスのインスタンス

        $effectID=[$this->id,0,0,0];
        $queryEffect=["","","",""];
        $query="";

        for($offset=0;$offset<=4;$offset++) {
            // 戦技ID+offsetの戦技効果IDを使用しないならforループから抜ける
            if($offset>0) {
                $apply="apply{$offset}";
                if($this->$apply!="on") break;
                else $effectID[$offset]=$this->id+$offset;
            }

            // tnk_command_effectへのInsertクエリ
            $category0="category{$offset}0";
            $category1="category{$offset}1";
            $effectTime="effectTime{$offset}";
            $effectText="effectText{$offset}";
            $effectNotes="effectNotes{$offset}";

            $eid=esc_sql($effectID[$offset]);
            $id=esc_sql($this->id);
            $category0=esc_sql($this->$category0);
            $category1=esc_sql($this->$category1);
            $effectTime=esc_sql($this->$effectTime);
            $effectText=esc_sql($this->$effectText);
            $effectNotes=esc_sql($this->$effectNotes);

            $queryEffect[$offset]=""
                ."INSERT INTO tnk_command_effect "
                ."(command_effect_ID, "
                ."command_id, "
                ."command_category0_id, "
                ."command_category1_id, "
                ."command_effect_time, "
                ."command_effect_text, "
                ."command_effect_notes, "
                ."command_effect_todo) "
                ."VALUES "
                ."('$eid', "
                ."'$id', "
                ."'$category0', "
                ."'$category1', "
                ."'$effectTime', "
                ."'$effectText', "
                ."'$effectNotes', "
                ."'') "
            ;
        }
        
        // 当該公式カテゴリーの公式並び順挿入位置以降のcommand_official_orderを1加算
        $queryUpdate=""
            ."UPDATE tnk_command "
            ."SET command_official_order=command_official_order+1 "
            ."WHERE command_official_type={esc_sql($this->officialType)} "
            ."AND command_official_order>={esc_sql($this->officialOrder)} "
        ;

        $commandName=esc_sql($this->commandName);
        $commandKana=esc_sql($this->commandKana);
        $icon=esc_sql($this->icon);
        $cost=esc_sql($this->cost);
        $limit=esc_sql($this->limit);
        $notes=esc_sql($this->notes);
        $officialType=esc_sql($this->officialType);
        $officialOrder=esc_sql($this->officialOrder);
        $officialText=esc_sql($this->officialText);

        // tnk_commandへのInsertクエリ
        $queryCommand=""
            ."INSERT INTO tnk_command "
            ."(command_ID, "
            ."command_name, "
            ."command_kana, "
            ."command_icon, "
            ."command_cost, "
            ."command_limit_use, "
            ."command_notes, "
            ."command_todo, "
            ."command_official_type, "
            ."command_official_order, "
            ."command_official_text) "
            ."VALUES  "
            ."('$id', "
            ."'$commandName', "
            ."'$commandKana', "
            ."'$icon', "
            ."'$cost', "
            ."'$limit', "
            ."'$notes', "
            ."'', "
            ."'$officialType', "
            ."'$officialOrder', "
            ."'$officialText') "
        ;

        // トランザクション
        $wpdb->query("BEGIN");
        if(FALSE!==$wpdb->query($query=$queryUpdate)) {
            if(1==$wpdb->query($query=$queryCommand)) {
                if(1==$wpdb->query($query=$queryEffect[0])) {
                    if($queryEffect[1]=="" || ($queryEffect[1]!="" && 1==$wpdb->query($query=$queryEffect[1]))) {
                        if($queryEffect[2]=="" || ($queryEffect[2]!="" && 1==$wpdb->query($query=$queryEffect[2]))) {
                            if($queryEffect[3]=="" || ($queryEffect[3]!="" && 1==$wpdb->query($query=$queryEffect[3]))) {
                                $wpdb->query("COMMIT");
                                return "";
                            }
                        }
                    }
                }
            }
        }
        $wpdb->query("ROLLBACK");
        return $query;
    }
}
?>
