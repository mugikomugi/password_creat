# password_creat
## パスワードランダム生成
**格納ファイル**
|ファイル・フォルダ | 内容
|--|--
|index.php | トップページ・form
|sanitize.css | 初期設定CSS
|stile.css | 基本CSS
|favicon.cio | ファビコン
|imaje | 画像フォルダー

### 仕様
シングルページ完結<br>
入力 → 生成 → 書き出し<br>

- 英数字のみの場合はopenssl_random_pseudo_bytes関数を使用。
- 記号が入る場合はmt_rand関数で条件分岐。<br>
生成された結果、記号が入らなかったら再度記号を入れて再生成。<br>
一番最初は記号を入れない。

<br>

**DEMO**<br>
[https://spica.okamechan.com/creat/](https://spica.okamechan.com/creat/)

