<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * エラーフォーマッタ
 *
 * @author k.holy74@gmail.com
 */
class ErrorFormatter
{

    /* @var array PHPエラーレベル */
    private static array $errorLevels = [
        E_ERROR => 'Fatal error',
        E_WARNING => 'Warning',
        E_NOTICE => 'Notice',
        E_STRICT => 'Strict standards',
        E_RECOVERABLE_ERROR => 'Catchable fatal error',
        E_DEPRECATED => 'Deprecated',
        E_USER_ERROR => 'User Fatal error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    /**
     * エラー情報を文字列に整形して返します。
     *
     * @param int $errno エラーレベル
     * @param string $errstr エラーメッセージ
     * @param string $errfile エラー発生元ファイル
     * @param string $errline エラー発生元ファイルの行番号
     * @return string
     */
    public function __invoke(int $errno, string $errstr, string $errfile, string $errline): string
    {
        return self::format($errno, $errstr, $errfile, $errline);
    }

    /**
     * エラー情報を文字列に整形して返します。
     *
     * @param int $errno エラーレベル
     * @param string $errstr エラーメッセージ
     * @param string $errfile エラー発生元ファイル
     * @param string $errline エラー発生元ファイルの行番号
     * @return string
     */
    public static function format(int $errno, string $errstr, string $errfile, string $errline): string
    {
        return sprintf("%s '%s' in %s on line %u",
            self::buildHeader($errno),
            $errstr,
            $errfile,
            $errline
        );
    }

    /**
     * PHPエラーレベル別にエラーメッセージ用のヘッダを生成して返します。
     *
     * @param int $errno エラーレベル
     * @return string
     */
    public static function buildHeader(int $errno): string
    {
        return sprintf('%s[%d]:', static::$errorLevels[$errno] ?? 'Unknown error', $errno);
    }

}
