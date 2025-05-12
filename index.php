<?php
//htmlを認識させる
header('Content-Type: text/html; charset=UTF-8');
//XSS
function html_esc($word)
{
  return htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
}

//エラーの配列
$err = array(
  'mark' => '',
  'num' => ''
);

//生成する文字数、ラジオボタンの値
$piece = [6, 8, 10, 12, 16, 24, 32];

//文字数の初期化
$word_num = 0;

//生成されるワード候補の数
$li_length = 6;
for ($i = 0; $i < $li_length; $i++) {
  $generation[$i] = '';
}
//ワードが生成されたらツールチップのイベントを発火
$balloon = '';

//文字列を配列に
$passStr_a = range('a', 'z');
$passStr_A = range('A', 'Z');
$passStr_int = range('0', '9');
$passStr_mark = ['*', '-', '_', '#', '!', '~', '?'];
//配列を結合するarray_merge()
$passStr = array_merge($passStr_a, $passStr_A, $passStr_int, $passStr_mark);
$passstr_only = array_merge($passStr_a, $passStr_A, $passStr_int);

//記号ありのパスワード生成
function creat_pass($length)
{
  //関数内にグローバル変数をつけないと配列が読み込めない
  global $passStr_int, $passStr, $passstr_only, $passStr_mark;

  $creat_word = '';
  for ($i = 0; $i < $length; $i++) {
    //記号が文字列の先頭に来ないように設定
    if ($i === 0) {
      $creat_word .= $passStr[mt_rand(0, count($passstr_only) - 1)];
    } else {
      $creat_word .= $passStr[mt_rand(0, count($passStr) - 1)];
    }
  }

  //文字を一文字づつ配列化
  $str_one = str_split($creat_word);
  //生成文字に記号と数字が入っていなかったら記号と数字を一つ入れる
  if (!array_intersect($str_one, $passStr_mark)) {
    //substr_replace( 置換対象の文字列, 置換する文字列, 開始位置 [, 範囲] )
    //指定した配列からキーをランダムに抽出array_rand( 配列, 抽出する数 )
    $creat_word = substr_replace($creat_word, $passStr_mark[array_rand($passStr_mark)], 3, 1);
  }
  if (!array_intersect($str_one, $passStr_int)) {
    $creat_word = substr_replace($creat_word, $passStr_int[array_rand($passStr_int)], 2, 1);
  }
  return $creat_word;
}

// 記号なしパスワード生成（大文字小文字数字を含む）claudeで修正
function str_only($length)
{
  global $passStr_a, $passStr_A, $passStr_int;
  $str_chars = array_merge($passStr_a, $passStr_A, $passStr_int);
  $pass = '';

  for ($i = 0; $i < $length; $i++) {
    $pass .= $str_chars[mt_rand(0, count($str_chars) - 1)];
  }

  // 生成文字に大文字・小文字・数字が少なくとも1つずつ含まれるよう確認
  $str_one = str_split($pass);

  if (!array_intersect($str_one, $passStr_A)) {
    $pass = substr_replace($pass, $passStr_A[array_rand($passStr_A)], mt_rand(1, $length - 1), 1);
  }

  if (!array_intersect($str_one, $passStr_a)) {
    $pass = substr_replace($pass, $passStr_a[array_rand($passStr_a)], mt_rand(1, $length - 1), 1);
  }

  if (!array_intersect($str_one, $passStr_int)) {
    $pass = substr_replace($pass, $passStr_int[array_rand($passStr_int)], mt_rand(1, $length - 1), 1);
  }

  return $pass;
}

// 生成文字に小文字・数字が少なくとも1つずつ含まれるよう確認
function str_small($length)
{
  global $passStr_a, $passStr_int;
  $str_chars = array_merge($passStr_a, $passStr_int);
  $pass = '';

  for ($i = 0; $i < $length; $i++) {
    $pass .= $str_chars[mt_rand(0, count($str_chars) - 1)];
  }

  // 小文字が含まれていない場合
  if (!preg_match('/[a-z]/', $pass)) {
    $pass = substr_replace(
      $pass,
      $passStr_a[array_rand($passStr_a)],
      mt_rand(1, $length - 1),
      1
    );
  }

  // Ensure at least one number
  if (!preg_match('/[0-9]/', $pass)) {
    $pass = substr_replace(
      $pass,
      $passStr_int[array_rand($passStr_int)],
      mt_rand(1, $length - 1),
      1
    );
  }

  return $pass;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $formValid = true; // フォームが有効かどうかのフラグ
  $mark = ''; // デフォルト値を設定

  // 記号の有無のチェック
  if (isset($_POST['mark'])) {
    $mark = html_esc($_POST['mark']);
    $err['mark'] = '';
  } else {
    $err['mark'] = '<p class="err"><span class="material-icons-outlined">error_outline</span>未選択です</p>';
    $formValid = false;
  }

  // 文字数のチェック
  if (isset($_POST['word_num'])) {
    $word_num = html_esc($_POST['word_num']);
    $word_num = (int)$word_num;
    $err['num'] = '';
  } else {
    $err['num'] = '<p class="err"><span class="material-icons-outlined">error_outline</span>未選択です</p>';
    $formValid = false;
  }

  // 入力が有効な場合のみ処理を続行
  if ($formValid) {
    switch ($mark) {
      case '記号あり':
        for ($i = 0; $i < $li_length; $i++) {
          $generation[$i] = creat_pass($word_num);
        }
        //ツールチップ表示
        $balloon = '<img class="balloon" src="image/balloon.svg" alt="コピー">';
        break;
      case '記号無し英数小文字':
        for ($i = 0; $i < $li_length; $i++) {
          $generation[$i] = str_small($word_num);
        }
        //ツールチップ表示
        $balloon = '<img class="balloon" src="image/balloon.svg" alt="コピー">';
        break;
      case '記号無し':
        for ($i = 0; $i < $li_length; $i++) {
          $generation[$i] = str_only($word_num);
        }
        //ツールチップ表示
        $balloon = '<img class="balloon" src="image/balloon.svg" alt="コピー">';
        break;
      default:
        // 何も選択されていない場合は何もしない
        break;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>パスワードジェネレーター</title>
  <meta name="format-detection" content="telephone=no">
  <!--webフォント-->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
  <!-- Outlined -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"
    rel="stylesheet">
  <link rel="stylesheet" href="sanitize.css">
  <link rel="stylesheet" type="text/css" href="style.css">
  <!--お気に入りアイコン152x152-->
  <link rel="apple-touch-icon-precomposed" href="apple-touch-icon.png">
  <!--ファビコン32x32-->
  <link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon">
</head>

<body>
  <div class="wrapper">

    <header class="header">
      <h1 class="topH1"><span class="material-icons-outlined">shuffle_on</span>PASSWORD<span class="subH1">GENERATER</span></h1>
    </header>

    <main class="main">
      <noscript>JavaScriptが非対応になっています</noscript>
      <section class="intro">
        <h2 class="subH2">Annotation<span></span></h2>
        <p class="mb20">文字列をランダムに組み合わせて、キーワード生成するシステムを作りました。<br>
          openssl_random_pseudo_bytes関数は記号を含めないので、英字、数値、記号の配列を作りmt_rand関数で組み合わせて記号入りのワードも作る出せるようにしました。<br>
          文字数を選んで、「パスワードを作る」ボタンを押すと6種類の候補が表示されます。
        </p>
      </section>

      <!-- form送信 -->
      <form id="qrForm" method="post" action="#submibtn">
        <div class="formGroup">
          <p class="groupNum"><span>1</span>記号入りか記号無しを選んでください</p>
          <?php echo $err['mark']; ?>
          <div class="qrImg">
            <label class="qrBox">
              <input type="radio" class="qrSize" name="mark" value="記号無し英数小文字">英数小文字<span class="notesB">記号無し</span>
            </label>
            <label class="qrBox">
              <input type="radio" class="qrSize" name="mark" value="記号無し">英数大小文字<span class="notesB">記号無し</span>
            </label>
            <label class="qrBox">
              <input class="qrSize" type="radio" name="mark" value="記号あり">英数大小文字<span class="notesP">記号あり</span>
            </label>
          </div>
        </div>
        <!-- //.formGroup -->
        <div class="formGroup">
          <p class="groupNum"><span>2</span>文字数を選んでください</p>
          <?php echo $err['num']; ?>
          <div class="qrImg">
            <?php $i = 0;
            while ($i < count($piece)): ?>
              <label class="wordNum">
                <input type="radio" class="qrSize" name="word_num" value="<?php echo $piece[$i]; ?>"><?php echo $piece[$i]; ?>文字
              </label>
            <?php $i++;
            endwhile; ?>
          </div>
        </div>
        <!-- //.formGroup -->

        <input type="submit" value="パスワードを作る" id="submibtn">
      </form>

      <!-- 生成コード表示 -->
      <div id="qr_panel">
        <p class="center">生成されたテキストをクリックするとコピーできます。選択中のものは色が変わります。</p>
        <ul class="export">
          <?php for ($i = 0; $i < $li_length; $i++): ?>
            <li class="exportLi">
              <?php echo $balloon; ?>
              <div class="exportBox"><?php echo $generation[$i]; ?></div>
            </li>
          <?php endfor; ?>
        </ul>
      </div>
      <!-- //#qr_panel -->
      <p>記号無しは英字（大文字・小文字）と数字のみで生成します。<br>
        記号を含めたものは先頭以外に記号が1文字以上入るようmt_rand関数で組み合わせて条件設定しています。<br>
        選んだWEBフォントのせいか数字の0と大文字Oの違いが分かりづらくなってしまいました。ちなみに小文字はo。
      </p>

      <section class="infoBox">
        <h3 class="infoBoxH3">動作確認について</h3>
        ブラウザはChromeかFirefox、Safariの最新バージョンを推奨いたします。<br>
        Microsoft IEとWindows10以降標準ブラウザであるEdgeは動作確認をしていません。<br>
        スマホにおいてはiPhoneのみ確認できています。
      </section>

    </main>

    <footer class="footer">
      <a class="footerImg" href="https://github.com/mugikomugi/password_creat" target="_blank" rel="noopener"><img src="image/github.svg" alt=""></a>
      <small>Copyright 2022 Mugikomugi All Rights Reserved.</small>
    </footer>

  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script>
    jQuery(function() {

      //ツールチップを表示
      jQuery('.exportLi').hover(function() {
        jQuery('.balloon', this).stop().fadeIn(300);
      }, function() {
        jQuery('.balloon', this).stop().fadeOut(300);
      });

      //生成されたワードをクリップボードにコピー
      jQuery('.exportBox').on('click', function() {
        const copyWord = jQuery(this).text();
        //コピーするにはtextareaに入れないと選択出来ない
        jQuery(this).append('<textarea>' + copyWord + '</textarea>');
        jQuery('textarea').select();
        //クリップボードにコピー
        document.execCommand('copy');
        jQuery('textarea').remove();
        //選択したワードをピックアップ
        jQuery(this).parent('.exportLi').css('background', 'rgba(55, 201, 201, 0.3) url(image/icon_copy.svg) 99% center no-repeat').siblings('.exportLi').css('background', '#fff url(image/icon_copy.svg) 99% center no-repeat');
        console.log(copyWord);
      });

    });
  </script>
</body>

</html>