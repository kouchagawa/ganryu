<?php
require_once "tnk_include.php";

class Maintenance {
    public $html="";

    public function __construct() {
        global $wpdb;
        $handle=fopen("tnk_db/createdb.txt", "r");
        $createdb="";
        while(($buffer=fgets($handle,4096)) !== false) {
            $createdb.=$buffer;
        }
        if(!feof($handle)) {
            fclose($handle);
            $this->html.="<p class='warning'>Error: unexpected fgets() fail</p>".EOL;
        } else {
            fclose($handle);
            $rows=explode(";",$createdb);
            $numRows=0;
            $this->html.="<form action='".MAINTENANCE_PASS."' method='get'>".EOL;
            foreach($rows as $row) {
                $query=trim($row);
                if($query!="") {
                    $CheckBox=new CheckBoxExcute("query$numRows", "on", $query);
                    $this->html.="<p>{$CheckBox->to_s()}</p>".EOL;
                    if(array_key_exists("query$numRows",$_GET)) {
                        if($_GET["query$numRows"]=="on") {
                            if(substr($query,0,18)=="LOAD DATA INFILE '") {
                                $path=substr($query,19,strpos($query,"'",19)-19);
                                $parts=pathinfo($path);
                                if($parts['extension']=="csv") {
                                    $handleCSV=fopen("tnk_db/".$parts['basename'], "r");
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
                                        $i=$result=0;
                                        foreach($rows as $row) {
                                            $columns=trim($row);
                                            //if($columns!=""&&$i!=0) {
                                            if($columns!="") {
                                                $arr=explode(",",$columns);
                                                for($j=0;$j<count($arr);$j++) {
                                                    if(!$arr[$j]&&$arr[$j]!=0) $arr[$j]='""';
                                                    else {
                                                        $s=$e="";
                                                        if(substr($arr[$j],0,1)!='"') $s='"';
                                                        if(substr($arr[$j],-1)!='"') $e='"';
                                                        $arr[$j]=$s.$arr[$j].$e;
                                                    }
                                                }
                                                $result+=$wpdb->query("INSERT INTO ".$parts['filename']." VALUES (".implode(",",$arr).")");
                                            }
                                            $i++;
                                        }
                                        $this->html.="result: $result".EOL."<pre>$query</pre>".EOL;
                                    }
                                }
                            } else {
                                $result=$wpdb->query($query);
                                $this->html.="result: $result".EOL."<pre>$query</pre>".EOL;
                            }
                        }
                    }
                }
                $numRows++;
            }
            $this->html.="<p><input type='submit' value='実行'></p></form>".EOL;
        }
    }
}
?>