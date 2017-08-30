<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * ErrorHandler
 *
 * @author k.holy74@gmail.com
 */
class ErrorHandler
{

    /* @const int エラーレベル定数 */
    const LEVEL_NONE = 0;
    const LEVEL_EXCEPTION = 1;
    const LEVEL_ERROR = 2;
    const LEVEL_WARNING = 4;
    const LEVEL_NOTICE = 8;
    const LEVEL_INFO = 16;
    const LEVEL_UNKNOWN = 32;
    const LEVEL_ALL = 32767;

    /* @var array オプション設定 */
    private $options = [

        // 出力エンコーディング
        'output_encoding' => null,

        // エラー発生時にlog()を実行するエラーレベル
        'log_level' => null,

        // エラー発生時にdisplay()を実行するエラーレベル
        'display_level' => null,

        // エラー発生時にforward()を実行するエラーレベル
        'forward_level' => null,

        // display()をHTMLとして出力するかどうか
        'display_html' => false,

        // display()実行結果を終了時にまとめて出力するかどうか
        'display_buffering' => true,
    ];

    /* @var callable エラーメッセージフォーマット関数 */
    private $messageFormatter;

    /* @var \Volcanus\Error\TraceFormatterInterface スタックトレースフォーマット関数 */
    private $traceFormatter;

    /* @var \Volcanus\Error\ErrorFormatterInterface エラーフォーマット関数 */
    private $errorFormatter;

    /* @var \Volcanus\Error\ExceptionFormatterInterface 例外フォーマット関数 */
    private $exceptionFormatter;

    /* @var callable ログ関数 */
    private $logger;

    /* @var callable エラー表示関数 */
    private $display;

    /* @var callable エラー画面遷移関数 */
    private $forward;

    /* @var array エラー表示結果のバッファ用配列 */
    private $buffer = [];

    /**
     * コンストラクタ
     *
     * @param array $options オプション設定
     */
    public function __construct(array $options = [])
    {
        $this->init($options);
    }

    /**
     * デストラクタ
     * display_buffering が有効な場合、バッファ出力します。
     */
    public function __destruct()
    {
        if ($this->getOption('display_buffering')) {
            $this->flushBuffer();
        }
    }

    /**
     * インスタンスを生成して返します。
     *
     * @param array $options オプション設定
     * @return $this
     */
    public static function instance(array $options = [])
    {
        return new self($options);
    }

    /**
     * インスタンスを初期化して返します。
     *
     * @param array $options オプション設定
     * @return $this
     */
    public function init(array $options = [])
    {
        $this->options = [];
        $this->options['output_encoding'] = mb_internal_encoding();
        $this->options['log_level'] = self::LEVEL_ALL;
        $this->options['display_level'] = self::LEVEL_ALL;
        $this->options['forward_level'] = self::LEVEL_EXCEPTION | self::LEVEL_ERROR;
        $this->options['display_html'] = true;
        $this->options['display_buffering'] = false;
        $this->messageFormatter = function ($header, $message, $file, $line, $trace) {
            return sprintf("%s '%s' in %s on line %d%s", $header, $message, $file, $line, $trace);
        };
        $this->traceFormatter = new TraceFormatter();
        $this->errorFormatter = new ErrorFormatter();
        $this->exceptionFormatter = new ExceptionFormatter();
        $this->logger = null;
        $this->display = null;
        $this->forward = null;
        $this->clearBuffer();
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $this->setOption($name, $value);
            }
        }
        return $this;
    }

    /**
     * オプション項目の値を返します。
     *
     * @param string $name オプション項目名
     * @return mixed オプション項目値
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \RuntimeException(
                sprintf('The option "%s" unsupported.', $name));
        }
        return $this->options[$name];
    }

    /**
     * オプション項目の値をセットします。
     *
     * @param string $name オプション項目名
     * @param mixed $value オプション項目値
     * @return $this
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \RuntimeException(
                sprintf('The option "%s" unsupported.', $name));
        }
        switch ($name) {
            case 'output_encoding':
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('The %s is not string.', $name));
                }
                break;
            case 'log_level':
            case 'display_level':
            case 'forward_level':
                if (!is_int($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('The %s is not integer.', $name));
                }
                break;
            case 'display_html':
            case 'display_buffering':
                if (!is_int($value) && !is_bool($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('The %s is not integer and not bool.', $name));
                }
                break;
        }
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * PHPエラーのフォーマット関数をセットします。
     *
     * @param callable $errorFormatter PHPエラーのフォーマット関数
     * @return $this
     */
    public function setErrorFormatter($errorFormatter)
    {
        if (!is_callable($errorFormatter)) {
            throw new \InvalidArgumentException(
                sprintf('The errorFormatter is not callable. type:%s',
                    is_object($errorFormatter) ? get_class($errorFormatter) : gettype($errorFormatter)
                )
            );
        }
        $this->errorFormatter = $errorFormatter;
        return $this;
    }

    /**
     * 例外のフォーマット関数をセットします。
     *
     * @param callable $exceptionFormatter 例外のフォーマット関数
     * @return $this
     */
    public function setExceptionFormatter($exceptionFormatter)
    {
        if (!is_callable($exceptionFormatter)) {
            throw new \InvalidArgumentException(
                sprintf('The exceptionFormatter is not callable. type:%s',
                    is_object($exceptionFormatter) ? get_class($exceptionFormatter) : gettype($exceptionFormatter)
                )
            );
        }
        $this->exceptionFormatter = $exceptionFormatter;
        return $this;
    }

    /**
     * エラーメッセージのフォーマット関数をセットします。
     *
     * @param callable $messageFormatter エラーメッセージのフォーマット関数
     * @return $this
     */
    public function setMessageFormatter($messageFormatter)
    {
        if (!is_callable($messageFormatter)) {
            throw new \InvalidArgumentException(
                sprintf('The messageFormatter is not callable. type:%s',
                    is_object($messageFormatter) ? get_class($messageFormatter) : gettype($messageFormatter)
                )
            );
        }
        $this->messageFormatter = $messageFormatter;
        return $this;
    }

    /**
     * スタックトレースのフォーマット関数をセットします。
     *
     * @param callable $traceFormatter スタックトレースのフォーマット関数
     * @return $this
     */
    public function setTraceFormatter($traceFormatter)
    {
        if (!is_callable($traceFormatter)) {
            throw new \InvalidArgumentException(
                sprintf('The traceFormatter is not callable. type:%s',
                    (is_object($traceFormatter)) ? get_class($traceFormatter) : gettype($traceFormatter)));
        }
        $this->traceFormatter = $traceFormatter;
        return $this;
    }

    /**
     * ログ関数をセットします。
     *
     * @param callable $logger ログ関数
     * @return $this
     */
    public function setLogger($logger)
    {
        if (!is_callable($logger)) {
            throw new \InvalidArgumentException(
                sprintf('The logger is not callable. type:%s',
                    (is_object($logger)) ? get_class($logger) : gettype($logger)));
        }
        $this->logger = $logger;
        return $this;
    }

    /**
     * エラー表示関数をセットします。
     *
     * @param callable $display エラー表示関数
     * @return $this
     */
    public function setDisplay($display)
    {
        if (!is_callable($display)) {
            throw new \InvalidArgumentException(
                sprintf('The display is not callable. type:%s',
                    (is_object($display)) ? get_class($display) : gettype($display)));
        }
        $this->display = $display;
        return $this;
    }

    /**
     * エラー画面遷移関数をセットします。
     *
     * @param callable $forward エラー画面遷移関数
     * @return $this
     */
    public function setForward($forward)
    {
        if (!is_callable($forward)) {
            throw new \InvalidArgumentException(
                sprintf('The forward is not callable. type:%s',
                    (is_object($forward)) ? get_class($forward) : gettype($forward)));
        }
        $this->forward = $forward;
        return $this;
    }

    /**
     * 例外ハンドラを返します。
     *
     * @return callable
     */
    public function getExceptionHandler()
    {
        return [&$this, 'handleException'];
    }

    /**
     * エラーハンドラを返します。
     *
     * @return callable
     */
    public function getErrorHandler()
    {
        return [&$this, 'handleError'];
    }

    /**
     * 例外を処理します。
     *
     * @param \Exception|\Throwable $exception 例外オブジェクト
     */
    public function handleException($exception)
    {
        $this->handle(
            $this->formatException($exception),
            self::LEVEL_EXCEPTION,
            $exception
        );
    }

    /**
     * 例外を発生元およびスタックトレースが付与されたメッセージに加工して返します。
     *
     * @param \Exception|\Throwable $exception 例外オブジェクト
     * @return string 例外メッセージ
     */
    public function formatException($exception)
    {
        return $this->formatMessage(
            $this->buildExceptionHeader($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $this->formatTrace($exception->getTrace())
        );
    }

    /**
     * PHPエラーを処理します。
     *
     * @param int $errno エラーレベル
     * @param string $errstr エラーメッセージ
     * @param string $errfile エラー発生元ファイル
     * @param string $errline エラー発生元ファイルの行番号
     * @param array $errcontext エラー発生元スコープでの全ての変数を格納した配列
     * @return bool|null
     */
    public function handleError(
        /** @noinspection PhpUnusedParameterInspection */
        $errno, $errstr, $errfile, $errline, $errcontext
    ) {
        if (!(error_reporting() & $errno)) {
            return null;
        }
        $trace = debug_backtrace();
        if (!empty($trace)) {
            $trace = array_slice($trace, 2, count($trace));
        } else {
            $trace = [];
        }
        $this->handle(
            $this->formatError($errno, $errstr, $errfile, $errline, $trace),
            $this->convertErrorLevel($errno)
        );
        return true;
    }

    /**
     * PHPエラーを発生元およびスタックトレースが付与されたメッセージに加工して返します。
     *
     * @param int $errno エラーレベル
     * @param string $errstr エラーメッセージ
     * @param string $errfile エラー発生元ファイル
     * @param string $errline エラー発生元ファイルの行番号
     * @param array $trace スタックトレース
     * @return string メッセージ
     */
    public function formatError($errno, $errstr, $errfile, $errline, $trace)
    {
        return $this->formatMessage(
            $this->buildErrorHeader($errno),
            $errstr,
            $errfile,
            $errline,
            $this->formatTrace($trace)
        );
    }

    /**
     * ログ処理を実行します。
     *
     * @param string $message エラーメッセージ
     * @param int $error_level エラーレベル定数
     * @param \Exception|\Throwable $exception 例外オブジェクト
     */
    public function log($message, $error_level = null, $exception = null)
    {
        if (!isset($error_level) || ($this->getOption('log_level') & $error_level)) {
            if (isset($this->logger)) {
                call_user_func($this->logger, $message, $exception);
                return;
            }
        }
    }

    /**
     * エラー表示処理を実行します。
     *
     * @param string $message エラーメッセージ
     * @param int $error_level エラーレベル定数
     * @param \Exception|\Throwable $exception 例外オブジェクト
     */
    public function display($message, $error_level = null, $exception = null)
    {
        if (!isset($error_level) || ($this->getOption('display_level') & $error_level)) {
            if ($this->getOption('display_buffering')) {
                ob_start();
            }
            if (isset($this->display)) {
                call_user_func($this->display, $message, $exception);
            } else {
                echo ($this->getOption('display_html'))
                    ? '<pre>' . $this->escapeHtml($message) . '</pre>'
                    : $message;
            }
            if ($this->getOption('display_buffering')) {
                $this->buffer[] = ob_get_contents();
                ob_end_clean();
            }
        }
    }

    /**
     * エラー画面遷移処理を実行します。
     *
     * @param string $message エラーメッセージ
     * @param int $error_level エラーレベル定数
     * @param \Exception|\Throwable $exception 例外オブジェクト
     */
    public function forward($message, $error_level = null, $exception = null)
    {
        if (!isset($error_level) || ($this->getOption('forward_level') & $error_level)) {
            if (isset($this->forward)) {
                call_user_func($this->forward, $message, $exception);
            }
        }
    }

    /**
     * エラー表示結果のバッファを返します。
     *
     * @return array
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * エラー表示結果のバッファをクリアします。
     */
    public function clearBuffer()
    {
        $this->buffer = [];
    }

    /**
     * エラー表示結果のバッファをフラッシュします。
     */
    public function flushBuffer()
    {
        echo implode("\n", $this->buffer);
    }

    /**
     * 例外からエラーメッセージ用のヘッダを生成して返します。
     *
     * @param \Exception|\Throwable $exception 例外オブジェクト
     * @return string
     */
    public function buildExceptionHeader($exception)
    {
        return $this->exceptionFormatter->buildHeader($exception);
    }

    /**
     * PHPエラーレベル別にエラーメッセージ用のヘッダを生成して返します。
     *
     * @param int $errno PHPエラーレベル
     * @return string
     */
    public function buildErrorHeader($errno)
    {
        return $this->errorFormatter->buildHeader($errno);
    }

    /**
     * 出力用のエラーメッセージを生成して返します。
     *
     * @param string $header エラーヘッダ
     * @param string $message エラーメッセージ
     * @param string $file エラー発生元ファイルパス
     * @param string $line エラー発生元ファイルの行番号
     * @param string $trace 整形済みのスタックトレース
     * @return string
     */
    private function formatMessage($header, $message, $file, $line, $trace)
    {
        $messageFormatter = $this->messageFormatter;
        return $messageFormatter($header, $message, $file, $line, $trace);
    }

    /**
     * スタックトレースの配列を文字列に整形して返します。
     *
     * @param array $trace スタックトレース
     * @return string
     */
    private function formatTrace(array $trace)
    {
        $formatter = $this->traceFormatter;
        return $formatter($trace);
    }

    /**
     * エラーメッセージを処理します。
     *
     * @param string $message エラーメッセージ
     * @param int $error_level エラーレベル
     * @param \Exception|\Throwable $exception 例外オブジェクト
     */
    private function handle($message, $error_level = null, $exception = null)
    {
        if (isset($exception) && !($exception instanceof \Exception) && !($exception instanceof \Throwable)) {
            throw new \InvalidArgumentException(
                sprintf('The Exception is not valid type:%s',
                    (is_object($exception)) ? get_class($exception) : gettype($exception)));
        }
        $this->log($message, $error_level, $exception);
        $this->display($message, $error_level, $exception);
        $this->forward($message, $error_level, $exception);
    }

    /**
     * HTML出力用に文字列をエスケープして返します。
     *
     * @param string $var 文字列
     * @return string
     */
    private function escapeHtml($var)
    {
        return htmlspecialchars($var, ENT_QUOTES, $this->getOption('output_encoding'));
    }

    /**
     * PHPエラー定数値をこのクラスのエラーレベル定数に変換して返します。
     *
     * @param int $errno PHPエラー定数
     * @return int
     */
    private function convertErrorLevel($errno)
    {
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                return self::LEVEL_NOTICE;
            case E_WARNING:
            case E_USER_WARNING:
                return self::LEVEL_WARNING;
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                return self::LEVEL_ERROR;
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return self::LEVEL_INFO;
        }
        return self::LEVEL_UNKNOWN;
    }

    /**
     * __get
     * $this->foo で $this->getFoo() または $this->getOption('foo') が呼ばれます。
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        return $this->getOption($name);
    }

    /**
     * __set
     * $this->foo = $var で $this->setFoo($var) または $this->setOption('foo', $var) が呼ばれます。
     *
     * @param string $name
     * @param mixed $value
     * @return \Volcanus\Error\ErrorHandler
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->{$method}($value);
        }
        return $this->setOption($name, $value);
    }

    /**
     * __invoke
     * 引数に応じて例外ハンドラまたはエラーハンドラとして振る舞います。
     *
     * @param  mixed $error
     */
    public function __invoke($error /*[,$errstr[,$errfile[,$errline[,$errcontext]]]]*/)
    {
        if ($error instanceof \Exception || $error instanceof \Throwable) {
            $this->handleException($error);
        } else {
            $args = func_get_args();
            $this->handleError($args[0], $args[1], $args[2], $args[3], $args[4]);
        }
    }

}
