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
class ErrorFormatter implements ErrorFormatterInterface
{

    /* @var array PHPエラーレベル */
    private static $errorLevels = [
        E_ERROR => 'Fatal error',
        E_WARNING => 'Warning',
        E_NOTICE => 'Notice',
        E_STRICT => 'Strict standards',
        E_RECOVERABLE_ERROR => 'Catchable fatal error',
        E_DEPRECATED => 'Depricated',
        E_USER_ERROR => 'User Fatal error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_USER_DEPRECATED => 'User Depricated',
    ];

    /**
     * エラー情報を文字列に整形して返します。
     *
     * @param int エラーレベル
     * @param string エラーメッセージ
     * @param string エラー発生元ファイル
     * @param string エラー発生元ファイルの行番号
     * @return string
     */
    public function __invoke($errno, $errstr, $errfile, $errline)
    {
        return $this->format($errno, $errstr, $errfile, $errline);
    }

    /**
     * エラー情報を文字列に整形して返します。
     *
     * @param int エラーレベル
     * @param string エラーメッセージ
     * @param string エラー発生元ファイル
     * @param string エラー発生元ファイルの行番号
     * @return string
     */
    public function format($errno, $errstr, $errfile, $errline)
    {
        return sprintf("%s '%s' in %s on line %u",
            $this->buildHeader($errno),
            $errstr,
            $errfile,
            $errline
        );
    }

    /**
     * PHPエラーレベル別にエラーメッセージ用のヘッダを生成して返します。
     *
     * @param int エラーレベル
     * @return string
     */
    public function buildHeader($errno)
    {
        return sprintf('%s[%d]:', (isset(static::$errorLevels[$errno]))
            ? static::$errorLevels[$errno]
            : 'Unknown error',
            $errno
        );
    }

}
