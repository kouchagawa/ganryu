$(function(){
	// submit押下時に値の入っていないURLクエリパラメーターを取り除く
	$('#formCommand').on('submit', function(event) {
		// サブミットをキャンセルする
		event.preventDefault();
		
		// フォームデータ集合をクエリ文字列で取得する
		var query=$(this).serialize();
		
		// クエリ文字列から値が空のパラメータを取り除く
		query=cleanQuery(query)
		
		// 元々の遷移先URLからパスとハッシュをパース
		var path=this.action.split('#')[0];
		var hash=this.action.split('#')[1] ? this.action.split('#')[1] : '';
		
		// 新しいURLを作成して移動
		location.href=path+'?'+query+'#'+hash;
		
		// 元々の遷移先URLへの移動を阻止
		return false;
	});
	
	function cleanQuery(query) {
		var arr=[];
		$.each(query.split('&'), function(i, param) {
			if(param.split('=')[1]) { arr.push(param); }
		});
		return arr.join('&');
	}
	
	// parentCategoryのselectBoxが変更された場合
	$('[name=parentCategory]').on('change',function(){
		// 子カテゴリselectタグを削除
		$('#categoryListContainer select').remove();
		
		// コンテナに子カテゴリリストを再表示
		var val=$(this).val();
		categoryListContainer(val);
	});
	
	// コンテナにカテゴリリストを表示
	function categoryListContainer(val){
		// 画面のロック
		var lockID='lockID';
		lockScreen(lockID);
		
		// ロードスピナー読み込み
		loadSpinner('categoryListContainer');
		
		// 親カテゴリーのタイプを /wp-admin/admin-ajax.php へ渡す
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				'action': 'category_list',
				'parent_type': val,
			},
		})
		
		// 読み込み完了時
		.done(function(data){
			// ロードスピナー削除
			removeSpinner('categoryListContainer');
			
			// admin-ajax.phpから戻って来たdataの値から
			// categoryListContainerにselectタグとoptionタグを追加する
			$('#categoryListContainer').append($('<select>').attr('id', 'category').attr('class', 'category').attr('name', 'category'));
			$.each(data, function(id, name){
				$('#category').append($('<option>').text(name).attr('value', id));
			});
			
			// 勝手に0にされた最初の選択肢を非同期通信ではない時と仕様を統一させるために値なしにする
			$('#category option:first-child').attr('value', '');
			unlockScreen(lockID);
		})
		
		// 失敗時
		.fail(function(){
			removeSpinner('categoryListContainer');
			unlockScreen(lockID);
			console.log('データの読み込みに失敗しました');
		});
	}
	
	// 画面のロック
	function lockScreen(id) {
		// 現在画面を覆い隠すためのDIVタグを作成する
		var divTag=$('<div />').attr('id',id);
		
		// スタイルを設定
		divTag.css('z-index', '999')
			.css('position', 'fixed')
			.css('top', '0')
			.css('width', '100%')
			.css('height', '100%');
		
		// BODYタグに作成したDIVタグを追加
		$('body').append(divTag);
	}
	
	// 画面のロックを解除する
	function unlockScreen(id) {
		$('#'+id).remove();
	}
	
	// ロードスピナー読み込み
	function loadSpinner(id) {
		$('#'+id).append($('<figure>').attr('class', 'spinner'));
		$('.spinner').append($('<img>').attr('src', themeuri+'/tnk/img/spinner16x16.gif').attr('class', 'spinnerImg'));
	}
	
	// ロードスピナー削除
	function removeSpinner(id) {
		$('#'+id+' figure').remove();
	}
});
