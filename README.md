# 巌流島（ganryujima）

ソーシャルゲーム「天下統一クロニクル」[^1] の攻略情報および、カード・戦技検索システムのソースコードです。

## 概要（Overview）

WordPress 上で動作するカスタムデータベース検索システムです。投稿記事内にショートコードを配置することで、ゲーム内のカードデータやコマンドデータを条件指定して検索・表示できます。

### デモページ（Reference）

* [カード検索（Card search）](https://kouchagawa.s270.xrea.com/card/)
* [コマンド検索（Command search）](https://kouchagawa.s270.xrea.com/command/)

## 動作環境（Requirement）

* PHP 8.2.18 以上
* MariaDB 10.6.17 以上
* jQuery 3.5.1 以上
* WordPress（Twenty Sixteen 子テーマ `twentysixteen-child`）

## 使い方（Usage）

WordPress の投稿・固定ページ内に専用のショートコードを記述して呼び出します。

## 主な機能（Features）

* **カード・コマンド検索機能**: PHP と MariaDB データを連携させ、複数条件での絞り込み検索に対応。
* **ショートコードによる柔軟な埋め込み**: WordPress の記事内に検索フォームと結果一覧をスムーズに統合。
* **広告表示の一括制御機能**: 設定ファイル（`tnk_include.php`）の定数により、コードを削除せず広告枠のON/OFFを切り替えたり、ローカルテスト環境との切り替えなどを実現。

## ディレクトリ構成（Directory Structure）

保守性と可読性を高めるため、役割・拡張子ごとにディレクトリを整理しています。

```text
ganryu/
├── css/          # スタイルシート
├── csv/          # マスタデータ・インポート用CSV
├── js/           # フロントエンド制御用JavaScript（jQuery）
├── sql/          # データベース定義・クエリ関連ファイル
├── src/          # 検索ロジック・インクルードファイル（tnk_include.php 等）
├── functions.php # WordPress子テーマ機能拡張
├── style.css     # 子テーマ定義・基本スタイル
└── README.md     # 本ドキュメント
```

## 最近の主な改修（Recent Improvements）

* **広告枠の制御実装とレイアウト改善**: 無効化されていた Google AdSense タグによる空白領域と不要な外部通信を削除し、定数による一括制御を実装。
* **コードのディレクトリ整理とリファクタリング**: 軽微な修正。ルート直下に混在していたファイルを役割別に分類。Gitによるバージョン管理を導入。

## 作者（Author）

紅茶川樽之介

* [X](https://x.com/kouchagawa)
* [Ameblo](https://ameblo.jp/powerslash/)

## ライセンス（License）

未定（Haven't decided yet.）

[^1]: 天下統一クロニクル - サービスを終了した日本のソーシャルゲームです。
