<?php
require_once "tnk_include.php";

// 配列基底クラス
class Arr {
    public $arr;
    // コンストラクタ
    public function __construct($type) {
        global $wpdb;
        $typeID=$type."_ID";
        $typeName=$type."_name";
        $tnkType="tnk_".$type;
        if($type=="command") $typeOrder="command_ID";
        else $typeOrder=$type."_order";
        $query="SELECT $typeID, $typeName FROM $tnkType ORDER BY $typeOrder";
        $results=$wpdb->get_results($query);
        foreach($results as $row) {
            $this->arr[$row->$typeName]=$row->$typeID;
        }
    }
}

// 地方
class Region extends Arr {
    // コンストラクタ
    public function __construct() {
        parent::__construct("region");
    }
}

// 県
class Prefecture extends Arr {
    // コンストラクタ
    public function __construct() {
        parent::__construct("prefecture");
    }
}

// 隊士タイプ
class Theme extends Arr {
    // コンストラクタ
    public function __construct() {
        parent::__construct("theme");
    }
}

// レア度
class Rarity extends Arr {
    // コンストラクタ
    public function __construct() {
        parent::__construct("rarity");
    }
}

// 戦技
class Command extends Arr {
    // コンストラクタ
    public function __construct() {
        parent::__construct("command");
    }
}

// アビリティ
class Ability {
    public $arr;
    // コンストラクタ
    public function __construct() {
        global $wpdb;
        $query="SELECT ability_type, ability_ID, ability_text FROM tnk_ability ORDER BY ability_type, ability_ID";
        $results=$wpdb->get_results($query);
        foreach($results as $row) {
            if($row->ability_ID!=0) $this->arr[$row->ability_text]=[$row->ability_type,$row->ability_ID];
        }
    }
}

// ガチャデータインポート
class gachaImport {
    public $html="";

    public function __construct() {
        global $wpdb;
        $Region=new Region();
        $Pref=new Prefecture();
        $Theme=new Theme();
        $Rarity=new Rarity();
        $Ability=new Ability();
        $Cmd=new Command();

        $this->html.="<form action='/gacha-import/' method='get'>".EOL;
        $fileNum=0;
        $dirName="gacha_csv";
        $dir=scandir($dirName);
        foreach($dir as $fileName) {
            if(!($fileName==".") && !($fileName=="..")) {
                $CheckBox=new CheckBoxExcute("csv$fileNum", "on", $fileName);
                $this->html.="<p>{$CheckBox->to_s()}</p>".EOL;
                if(array_key_exists("csv$fileNum",$_GET)) {
                    if($_GET["csv$fileNum"]=="on") {
                        $handleCSV=fopen("$dirName/$fileName", "r");
                        $csv="";
                        while(($buffer=fgets($handleCSV,4096)) !== false) {
                            $csv.=$buffer;
                        }
                        if(!feof($handleCSV)) {
                            fclose($handleCSV);
                            $this->html.="<p class='warning'>Error: unexpected fgets() fail</p>".EOL;
                        } else {
                            fclose($handleCSV);
                            $rows=explode("\n",$csv);
                            $numRows=$gachaRows=$year=$cardOrder=$result=$gachaID=0;
                            $cardThumbLocate="";
                            foreach($rows as $row) {
                                $columns=trim($row);
                                $column=explode(",",$columns);
                                if($numRows==0){
                                    $year=$column[0];
                                } else {
                                    $error="";
                                    if($column[0]=="") {
                                        $gachaRows=0;
                                        $cardOrder=1;
                                        $eventName=$gachaTitle=$gacha0Name=$gacha0Start=$gacha0End=$gacha1Name=$gacha1Start=$gacha1End=$gachaNotes="";
                                    } else {
                                        $arr=$arr2=$arr3=[];
                                        if($gachaRows==1) {
                                            if(2==count($arr=explode("(",$column[0]))) {
                                                $arr2=explode(")",$arr[1]);
                                                $gacha=$arr[0];
                                                $gachaNotes=$arr2[0];
                                            } else {
                                                $gacha=$column[0];
                                            }
                                            if(2==count($arr3=explode(" ",$gacha))) {
                                                $eventName=$arr3[0];
                                                $gachaTitle=$arr3[1];
                                            } else {
                                                $gachaTitle=$gacha;
                                            }
                                        } elseif(2==$gachaRows) {
                                            if("("==substr($column[0],0,1)) {
                                                $arr=explode("(",$column[0]);
                                                $arr2=explode(")",$arr[1]);
                                                $gacha0Name=$arr2[0];
                                                $date=$arr2[1];
                                            } else {
                                                $date=$column[0];
                                            }
                                            $arr3=explode("～",$date);
                                            $gacha0Start="$year/".$arr3[0];
                                            $gacha0End="$year/".$arr3[1];
                                        } elseif(3==$gachaRows && "("==substr($column[0],0,1)) {
                                            $arr=explode("(",$column[0]);
                                            $arr2=explode(")",$arr[1]);
                                            $gacha1Name=$arr2[0];
                                            $date=$arr2[1];
                                            if($date) {
                                                $arr3=explode("～",$date);
                                                $gacha1Start=$year."/".$arr3[0];
                                                $gacha1End=$year."/".$arr3[1];
                                            } else {
                                                $gacha1Start=$gacha0Start;
                                                $gacha1End=$gacha0End;
                                            }
                                        } elseif(!is_numeric(substr($column[0],0,1))) {
                                            if($gachaNotes!="") $gachaNotes.="<br />";
                                            $gachaNotes.=$column[0];
                                        } else {
                                            if($cardOrder==1) {
                                                $queryGacha="INSERT INTO tnk_gacha VALUES ('0','$eventName','$gachaTitle','$gacha0Name','$gacha0Start','$gacha0End','$gacha1Name','$gacha1Start','$gacha1End','$gachaNotes')";
                                                if(1==$wpdb->query($queryGacha)) {
                                                    $results=$wpdb->get_results("SELECT LAST_INSERT_ID() AS gacha_ID");
                                                    $gachaID=$results[0]->gacha_ID;
                                                    $this->html.="<p>ガチャ登録しました: $gachaID $eventName $gachaTitle $gacha0Start</p>".EOL;
                                                } else {
                                                    $error.="<p class='warning'>Error: ガチャ登録に失敗しました: $eventName $gachaTitle $gacha0Start</p>".EOL;
                                                }
                                            }
                                            $cardID=substr($column[0],0,-1);
                                            $evoID=substr($column[0],-1);
                                            if(is_numeric($column[6])) $attack=$column[6];
                                            else $attack=0;
                                            if(is_numeric($column[7])) $defence=$column[7];
                                            else $defence=0;
                                            if(is_numeric($column[8])) $cost=$column[8];
                                            else $cost=0;
                                            if(array_key_exists($column[1],$Rarity->arr)) $rarity=$Rarity->arr[$column[1]];
                                            else $error.="<p class='warning'>Error: 存在しないレア度です: ".$column[1]."</p>".EOL;
                                            $levelMax=0;
                                            switch($rarity) {
                                                case 1: $levelMax=20; break;
                                                case 2: $levelMax=30; break;
                                                case 3: $levelMax=40; break;
                                                case 4: $levelMax=50; break;
                                                case 5: $levelMax=60; break;
                                                case 6: $levelMax=70; break;
                                                case 7: $levelMax=80; break;
                                                default: $levelMax=0;
                                            }
                                            $cardNotes=$regionName="";
                                            $region=$pref=0;
                                            if(strpos($column[2],"→")!==false) {
                                                $regionName=explode("→",$column[2])[1];
                                                $cardNotes.=$column[2]."(".$evoID."進)";
                                            } else {
                                                $regionName=$column[2];
                                            }
                                            if(array_key_exists($regionName,$Region->arr)) $region=$Region->arr[$regionName];
                                            elseif(array_key_exists($regionName,$Pref->arr)) $pref=$Pref->arr[$regionName];
                                            else $error.="<p class='warning'>Error: 存在しない所属名です: ".$regionName."</p>".EOL;
                                            $name=esc_sql($column[3]);
                                            $img=$column[4];
                                            $kana=$column[5];
                                            if(array_key_exists($column[9],$Theme->arr)) $themeID=$Theme->arr[$column[9]];
                                            else $error.="<p class='warning'>Error: 存在しない隊士タイプです: ".$column[9]."</p>".EOL;
                                            if($column[10]!="？") $skillName=$column[10];
                                            else $skillName="";
                                            if($column[11]!="？") $skill=$column[11];
                                            else $skill="";
                                            if(strpos($skill,"　/　")!==false) {
                                                $arrSkill=explode("　/　",$skill);
                                                $skillText0=$arrSkill[0];
                                                $skillText1=$arrSkill[1];
                                            } elseif(strpos($skill," / ")!==false) {
                                                $arrSkill=explode(" / ",$skill);
                                                $skillText0=$arrSkill[0];
                                                $skillText1=$arrSkill[1];
                                            } else {
                                                $skillText0=$skill;
                                                $skillText1="";
                                            }
                                            $abilityType=$abilityID=0;
                                            if($column[13]!="―") {
                                                if(array_key_exists($column[13],$Ability->arr)) {
                                                    $abilityType=$Ability->arr[$column[13]][0];
                                                    $abilityID=$Ability->arr[$column[13]][1];
                                                } else {
                                                    $error.="<p class='warning'>Error: 存在しない召喚アビリティです: ".$column[13]."</p>".EOL;
                                                }
                                            }
                                            $cmdID=0;
                                            if($column[14]!="―") {
                                                if(array_key_exists($column[14],$Cmd->arr)) $cmdID=$Cmd->arr[$column[14]];
                                                else $error.="<p class='warning'>Error: 存在しない戦技です: ".$column[14]."</p>".EOL;
                                            }
                                            if($column[17]!="") {
                                                if($column[17]!="5進" && $column[17]!="3進" && $column[17]!="2進" && $column[17]!="1進") {
                                                    if($cardNotes!="") $cardNotes.="<br />";
                                                    $cardNotes.=$column[17];
                                                }
                                            }
                                            $gachaCardNotes="";
                                            if($column[18]!="") $gachaCardNotes.=$column[18];
                                            $limitBreakMax=4;
                                            if(($gachaTitle=="天クロ演義ガチャ" || $gachaTitle=="クリスマス福引ガチャ") && $evoID==1 && ($rarity==6 || $rarity==5)) {
                                                $limitBreakMax=0;
                                            }

                                            if($error!="") {
                                                $this->html.=$error;
                                            } else {
                                                $queryCard="INSERT INTO tnk_card VALUES ('$cardID','$evoID','$limitBreakMax','0','0','10','$cmdID','$cardNotes')";
                                                $queryEvo="INSERT INTO tnk_card_evolution VALUES ('$cardID','$evoID','$region','$pref','$rarity','$img','$levelMax','0','0','0','0','0','0','$abilityType','$abilityID')";
                                                $queryType="INSERT INTO tnk_card_Type VALUES ('$cardID','$evoID','0','$themeID','$name','$kana','$skillName','$skillText0','$skillText1','')";
                                                $queryRate="INSERT INTO tnk_card_rate VALUES ('$cardID','$evoID','$cost','$attack','$defence')";
                                                $queryGachaCard="INSERT INTO tnk_gacha_card VALUES ('0','$gachaID','$cardID','$evoID','$cost','$cardOrder','$gachaCardNotes')";

                                                $queryNumCard="SELECT * FROM tnk_card WHERE card_ID=$cardID";
                                                $numCard=$wpdb->query($queryNumCard);
                                                if($numCard===0) {
                                                    $wpdb->query("BEGIN");
                                                    if(1==$wpdb->query($queryCard)) {
                                                        if(1==$wpdb->query($queryEvo)) {
                                                            if(1==$wpdb->query($queryType)) {
                                                                if(1==$wpdb->query($queryRate)) {
                                                                    if(1==$wpdb->query($queryGachaCard)) {
                                                                        $wpdb->query("COMMIT");
                                                                        $this->html.="<p>隊士ID$cardID$evoID コスト$cost: tnk_card, tnk_card_evolution, tnk_card_rate, tnk_gacha_card のINSERTに成功しました</p>".EOL;
                                                                    } else {
                                                                        $wpdb->query("ROLLBACK");
                                                                        $error.="<p class='warning'>Error: クエリが失敗しました: $queryGachaCard</p>".EOL;
                                                                    }
                                                                } else {
                                                                    $wpdb->query("ROLLBACK");
                                                                    $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryRate</p>".EOL;
                                                                }
                                                                $pre="https://stat100.ameba.jp/tnk47/ratio20/illustrations/card/thumb/ill_";
                                                                $suf="_thumbCard.jpg";
                                                                if($evoID==5) {
                                                                    $cardThumbLocate.=$pre.$cardID."5_".$img."05".$suf."\r\n";
                                                                    $cardThumbLocate.=$pre.$cardID."4_".$img."04".$suf."\r\n";
                                                                }
                                                                if($evoID==5 || $evoID==3) {
                                                                    $cardThumbLocate.=$pre.$cardID."3_".$img."03".$suf."\r\n";
                                                                    $cardThumbLocate.=$pre.$cardID."2_".$img."02".$suf."\r\n";
                                                                }
                                                                $cardThumbLocate.=$pre.$cardID."1_".$img."01".$suf."\r\n";
                                                            } else {
                                                                $wpdb->query("ROLLBACK");
                                                                $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryType</p>".EOL;
                                                            }
                                                        } else {
                                                            $wpdb->query("ROLLBACK");
                                                            $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryEvo</p>".EOL;
                                                        }
                                                    } else {
                                                        $wpdb->query("ROLLBACK");
                                                        $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryCard</p>".EOL;
                                                    }
                                                } elseif($numCard===1) {
                                                    $queryNumEvo="SELECT * FROM tnk_card_evolution WHERE card_id=$cardID AND card_evolution_ID=$evoID";
                                                    $numEvo=$wpdb->query($queryNumEvo);
                                                    if($numEvo===0) {
                                                        $wpdb->query("BEGIN");
                                                        if(1==$wpdb->query($queryEvo)) {
                                                            if(1==$wpdb->query($queryType)) {
                                                                if(1==$wpdb->query($queryRate)) {
                                                                    if(1==$wpdb->query($queryGachaCard)) {
                                                                        $wpdb->query("COMMIT");
                                                                        $this->html.="<p>隊士ID$cardID$evoID コスト$cost: tnk_card_evolution, tnk_card_rate, tnk_gacha_card のINSERTに成功しました</p>".EOL;
                                                                    } else {
                                                                        $wpdb->query("ROLLBACK");
                                                                        $error.="<p class='warning'>Error: クエリが失敗しました: $queryGachaCard</p>".EOL;
                                                                    }
                                                                } else {
                                                                    $wpdb->query("ROLLBACK");
                                                                    $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryRate</p>".EOL;
                                                                }
                                                            } else {
                                                                $wpdb->query("ROLLBACK");
                                                                $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryType</p>".EOL;
                                                            }
                                                        } else {
                                                            $wpdb->query("ROLLBACK");
                                                            $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryEvo</p>".EOL;
                                                        }
                                                    } elseif($numEvo===1) {
                                                        $queryNumRate="SELECT * FROM tnk_card_rate WHERE card_id=$cardID AND card_evolution_id=$evoID AND card_rate_cost=$cost";
                                                        $numRate=$wpdb->query($queryNumRate);
                                                        if($numRate===0) {
                                                            $wpdb->query("BEGIN");
                                                            if(1==$wpdb->query($queryRate)) {
                                                                if(1==$wpdb->query($queryGachaCard)) {
                                                                    $wpdb->query("COMMIT");
                                                                    $this->html.="<p>隊士ID$cardID$evoID コスト$cost: tnk_card_rate, tnk_gacha_card のINSERTに成功しました</p>".EOL;
                                                                } else {
                                                                    $wpdb->query("ROLLBACK");
                                                                    $error.="<p class='warning'>Error: クエリが失敗しました: $queryGachaCard</p>".EOL;
                                                                }
                                                            } else {
                                                                $wpdb->query("ROLLBACK");
                                                                $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryRate</p>".EOL;
                                                            }
                                                        } elseif($numRate===1) {
                                                            if(1==$wpdb->query($queryGachaCard)) {
                                                                $this->html.="<p>隊士ID$cardID$evoID コスト$cost: tnk_gacha_card のINSERTに成功しました</p>".EOL;
                                                            } else {
                                                                $error.="<p class='warning'>Error: クエリが失敗しました: $queryGachaCard</p>".EOL;
                                                            }
                                                        } else {
                                                            $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryNumRate</p>".EOL;
                                                        }
                                                    } else {
                                                        $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryNumEvo</p>".EOL;
                                                    }
                                                } else {
                                                    $this->html.="<p class='warning'>Error: クエリが失敗しました: $queryNumCard</p>".EOL;
                                                }
                                            }
                                            $cardOrder++;
                                        }
                                    }
                                }
                                $gachaRows++;
                                $numRows++;
                            }
                            $handleThumb=fopen("thumb_url/gacha".pathinfo($fileName)["filename"]."Thumb.txt","w");
                            fwrite($handleThumb,$cardThumbLocate);
                            fclose($handleThumb);
                        }
                    }
                }
                $fileNum++;
            }
        }
        $this->html.="<p><input type='submit' value='実行'></p></form>".EOL;
    }
}
?>