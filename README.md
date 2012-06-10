Volcanus\Error\ErrorHandler
===============

エラーおよび例外処理用クラスです。
以下の機能をサポートしています。

* エラーハンドラ/例外ハンドラとしての機能を提供します。
* ユーザー定義のcallableなエラーログ関数、エラーメッセージ表示関数、エラー画面遷移関数をエラーハンドラ/例外ハンドラで実行します。
* PHPエラーおよび例外メッセージを統一フォーマットに整形します。ユーザー定義のcallableなメッセージフォーマット関数、スタックトレースフォーマット関数を利用することも可能です。
* エラーレベル毎に、エラーログ/エラーメッセージ表示/エラー画面遷移の可否を設定できます。

以下の機能はサポートしません。

* エラー発生時のメール送信…必要に応じてユーザー定義のエラーログ関数で行なって下さい。
* 実行中の環境へのエラーハンドラ/例外ハンドラの設定および解除…必要に応じて利用者自身で行ってください。
* デフォルトのエラーログ関数、エラー画面遷移関数の提供…エラーメッセージ表示関数のみデフォルト実装しています。

以下は利用手順の一例です。

    use Volcanus\Error\ErrorHandler;

    class HttpException extends Exception {}

    // 設定オプションを指定してインスタンスを生成する
    $error = new ErrorHandler(array(

        // HTML出力時のエンコーディング
        'output_encoding' => 'UTF-8',

        // エラーログ関数を実行するエラーレベル
        'log_level' => ErrorHandler::LEVEL_ALL,

        // エラーメッセージ表示関数を実行するエラーレベル
        'display_level' => ErrorHandler::LEVEL_ALL,

        // エラー画面遷移関数を実行するエラーレベル
        'forward_level' => ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR,

        // デフォルトのエラーメッセージ表示時にHTMLとして出力するかどうか
        'display_html' => true,

        // エラーメッセージ表示関数を出力バッファリングするかどうか
        'display_buffering' => true,
    ));

    // ユーザー定義のエラーログ関数を指定する
    $error->setLogger(function($message, $exception = null) {
        error_log(sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message), 3,
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log'
                . DIRECTORY_SEPARATOR . 'php_error.log');
    });

    // ユーザー定義のエラー画面遷移関数を指定する
    $error->setForward(function($message, $exception = null) {
        $status = '500 Internal Server Error';
        if (isset($exception) && $exception instanceof HttpException) {
            switch ($exception->getCode()) {
            case 400:
                $status = '400 Bad Request';
                break;
            case 403:
                $status = '403 Forbidden';
                break;
            case 404:
                $status = '404 Not Found';
                break;
            case 405:
                $status = '405 Method Not Allowed';
                break;
            }
        }
        if (!headers_sent()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status);
        }
        $body = <<< HTML
    <!DOCTYPE html>
    <html lang="ja">
    <head>
    <meta charset="utf-8" />
    <title>%s</title>
    </head>
    <body>
    <h1>%s</h1>
    <h2>システムエラーが発生しました。</h2>
    <hr />
    <p><a href="%s">戻る</a></p>
    </body>
    </html>
    HTML;
        echo sprintf($body,
            htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'));
        exit();
    });

    // エラーハンドラおよび例外ハンドラとして設定する
    set_error_handler($error->getErrorHandler());
    set_exception_handler($error->getExceptionHandler());

    // 例外ハンドラを発動
    throw new HttpException('Invalid Parameter', 400);

