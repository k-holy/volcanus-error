<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * 例外フォーマッタ
 *
 * @author k.holy74@gmail.com
 */
class ExceptionFormatter
{

    /**
     * 例外オブジェクトを文字列に整形して返します。
     *
     * @param \Throwable $e 例外オブジェクト
     * @return string
     */
    public function __invoke(\Throwable $e): string
    {
        return self::format($e);
    }

    /**
     * 例外オブジェクトを文字列に整形して返します。
     *
     * @param \Throwable $e 例外オブジェクト
     * @return string
     */
    public static function format(\Throwable $e): string
    {
        return sprintf("%s '%s' in %s on line %u",
            self::buildHeader($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }

    /**
     * 例外からエラーメッセージ用のヘッダを生成して返します。
     *
     * @param \Throwable $e 例外オブジェクト
     * @return string
     */
    public static function buildHeader(\Throwable $e): string
    {
        return sprintf("Uncaught Exception %s[%d]:",
            get_class($e),
            $e->getCode()
        );
    }

}
