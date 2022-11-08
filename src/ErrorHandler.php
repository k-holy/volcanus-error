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
    private array $options = [

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

    /* @var \Closure|TraceFormatterInterface スタックトレースフォーマット関数 */
    private \Closure|TraceFormatterInterface $traceFormatter;

    /* @var callable ログ関数 */
    private $logger;

    /* @var callable エラー表示関数 */
    private $display;

    /* @var callable エラー画面遷移関数 */
    private $forward;

    /* @var array エラー表示結果のバッファ用配列 */
    private array $buffer = [];

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
     * @return self
     */
    public static function instance(array $options = []): self
    {
        return new self($options);
    }

    /**
     * インスタンスを初期化して返します。
     *
     * @param array $options オプション設定
     * @return self
     */
    public function init(array $options = []): self
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
        $this->logger = null;
        $this->display = null;
        $this->forward = null;
        $this->buffer = [];
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
    public function getOption(string $name): mixed
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
     * @return self
     */
    public function setOption(string $name, mixed $value): self
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
     * エラーメッセージのフォーマット関数をセットします。
     *
     * @param callable $messageFormatter エラーメッセージのフォーマット関数
     * @return self
     */
    public function setMessageFormatter(callable $messageFormatter): self
    {
        $this->messageFormatter = $messageFormatter;
        return $this;
    }

    /**
     * スタックトレースのフォーマット関数をセットします。
     *
     * @param callable $traceFormatter スタックトレースのフォーマット関数
     * @return self
     */
    public function setTraceFormatter(callable $traceFormatter): self
    {
        $this->traceFormatter = $traceFormatter;
        return $this;
    }

    /**
     * ログ関数をセットします。
     *
     * @param callable $logger ログ関数
     * @return self
     */
    public function setLogger(callable $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * エラー表示関数をセットします。
     *
     * @param callable $display エラー表示関数
     * @return self
     */
    public function setDisplay(callable $display): self
    {
        $this->display = $display;
        return $this;
    }

    /**
     * エラー画面遷移関数をセットします。
     *
     * @param callable $forward エラー画面遷移関数
     * @return self
     */
    public function setForward(callable $forward): self
    {
        $this->forward = $forward;
        return $this;
    }

    /**
     * 例外ハンドラを返します。
     *
     * @return callable|array
     */
    public function getExceptionHandler(): callable|array
    {
        return [&$this, 'handleException'];
    }

    /**
     * エラーハンドラを返します。
     *
     * @return callable|array
     */
    public function getErrorHandler(): callable|array
    {
        return [&$this, 'handleError'];
    }

    /**
     * 例外を処理します。
     *
     * @param \Throwable|\Exception $exception 例外オブジェクト
     */
    public function handleException(\Throwable|\Exception $exception): void
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
     * @param \Throwable|\Exception $exception 例外オブジェクト
     * @return string 例外メッセージ
     */
    public function formatException(\Throwable|\Exception $exception): string
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
     * @return bool|null
     */
    public function handleError(int $errno, string $errstr, string $errfile, string $errline): ?bool
    {
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
    public function formatError(int $errno, string $errstr, string $errfile, string $errline, array $trace): string
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
     * @param int|null $error_level エラーレベル定数
     * @param \Throwable|\Exception|null $exception 例外オブジェクト
     */
    public function log(string $message, int $error_level = null, \Throwable|\Exception $exception = null): void
    {
        if (!isset($error_level) || ($this->getOption('log_level') & $error_level)) {
            if (isset($this->logger)) {
                call_user_func($this->logger, $message, $exception);
            }
        }
    }

    /**
     * エラー表示処理を実行します。
     *
     * @param string $message エラーメッセージ
     * @param int|null $error_level エラーレベル定数
     * @param \Throwable|\Exception|null $exception 例外オブジェクト
     */
    public function display(string $message, int $error_level = null, \Throwable|\Exception $exception = null): void
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
     * @param int|null $error_level エラーレベル定数
     * @param \Throwable|\Exception|null $exception 例外オブジェクト
     */
    public function forward(string $message, int $error_level = null, \Throwable|\Exception $exception = null): void
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
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    /**
     * エラー表示結果のバッファをクリアします。
     */
    public function clearBuffer(): void
    {
        $this->buffer = [];
    }

    /**
     * エラー表示結果のバッファをフラッシュします。
     */
    public function flushBuffer(): void
    {
        echo implode("\n", $this->buffer);
    }

    /**
     * 例外からエラーメッセージ用のヘッダを生成して返します。
     *
     * @param \Throwable|\Exception $exception 例外オブジェクト
     * @return string
     */
    public function buildExceptionHeader(\Throwable|\Exception $exception): string
    {
        return ExceptionFormatter::buildHeader($exception);
    }

    /**
     * PHPエラーレベル別にエラーメッセージ用のヘッダを生成して返します。
     *
     * @param int $errno PHPエラーレベル
     * @return string
     */
    public function buildErrorHeader(int $errno): string
    {
        return ErrorFormatter::buildHeader($errno);
    }

    /**
     * 出力用のエラーメッセージを生成して返します。
     *
     * @param string $header エラーヘッダ
     * @param string $message エラーメッセージ
     * @param string $file エラー発生元ファイル
     * @param string $line エラー発生元ファイルの行番号
     * @param string $trace 整形済みのスタックトレース
     * @return string
     */
    private function formatMessage(string $header, string $message, string $file, string $line, string $trace): string
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
    private function formatTrace(array $trace): string
    {
        $formatter = $this->traceFormatter;
        return $formatter($trace);
    }

    /**
     * エラーメッセージを処理します。
     *
     * @param string $message エラーメッセージ
     * @param int|null $error_level エラーレベル
     * @param \Throwable|\Exception|null $exception 例外オブジェクト
     */
    private function handle(string $message, int $error_level = null, \Throwable|\Exception $exception = null): void
    {
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
    private function escapeHtml(string $var): string
    {
        return htmlspecialchars($var, ENT_QUOTES, $this->getOption('output_encoding'));
    }

    /**
     * PHPエラー定数値をこのクラスのエラーレベル定数に変換して返します。
     *
     * @param int $errno PHPエラー定数
     * @return int
     */
    private function convertErrorLevel(int $errno): int
    {
        return match ($errno) {
            E_NOTICE, E_USER_NOTICE => self::LEVEL_NOTICE,
            E_WARNING, E_USER_WARNING => self::LEVEL_WARNING,
            E_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR => self::LEVEL_ERROR,
            E_STRICT, E_DEPRECATED, E_USER_DEPRECATED => self::LEVEL_INFO,
            default => self::LEVEL_UNKNOWN,
        };
    }

    /**
     * __get
     * $this->foo で $this->getFoo() または $this->getOption('foo') が呼ばれます。
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
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
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->{$method}($value);
        }
        $this->setOption($name, $value);
    }

    /**
     * __invoke
     * 引数に応じて例外ハンドラまたはエラーハンドラとして振る舞います。
     *
     * @param mixed $error
     */
    public function __invoke(mixed $error /*[,$errstr[,$errfile[,$errline]]]*/): void
    {
        if ($error instanceof \Throwable) {
            $this->handleException($error);
        } else {
            $args = func_get_args();
            $this->handleError($args[0], $args[1], $args[2], $args[3]);
        }
    }

}
