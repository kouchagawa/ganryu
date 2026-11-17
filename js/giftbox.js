function show() {
  // 現在日時
  var nowTime = new Date();
  var nowYear = nowTime.getFullYear();
  var nowMonth= ('00' + (nowTime.getMonth() + 1)).slice(-2);
  var nowDate = ('00' + nowTime.getDate()).slice(-2);
  var nowHour = ('00' + nowTime.getHours()).slice(-2);
  var nowMin  = ('00' + nowTime.getMinutes()).slice(-2);
  var nowSec  = ('00' + nowTime.getSeconds()).slice(-2);
  var thisTime= nowYear + "年"  + nowMonth + "月" + nowDate + "日 " + nowHour + "時" + nowMin + "分" + nowSec + "秒";
  document.getElementById("now").innerHTML = thisTime;

  // 削除対象の日時
  var delTime = new Date();
  delTime.setDate(delTime.getDate() - 180);
  var delYear = delTime.getFullYear();
  var delMonth= ('00' + (delTime.getMonth() + 1)).slice(-2);
  var delDate = ('00' + delTime.getDate()).slice(-2);
  var delHour = ('00' + delTime.getHours()).slice(-2);
  var delMin  = ('00' + delTime.getMinutes()).slice(-2);
  var delSec  = ('00' + delTime.getSeconds()).slice(-2);
  var theTime = delYear + "年"  + delMonth + "月" + delDate + "日 " + delHour + "時" + delMin + "分" + delSec + "秒";
  document.getElementById("del").innerHTML = theTime;
}
show();
setInterval(show, 1000);