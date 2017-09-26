<?php
/**
 * Volcanus libraries for PHP
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
class ExceptionFormatter implements ExceptionFormatterInterface
{

    /**
     * 例外オブジェクトを文字列に整形して返します。
     *
     * @param \Exception|\Throwable $e 例外オブジェクト
     * @return string
     */
    public function __invoke($e)
    {
        return $this->format($e);
    }

    /**
     * 例外オブジェクトを文字列に整形して返します。
     *
     * @param \Exception|\Throwable $e 例外オブジェクト
     * @return string
     */
    public function format($e)
    {
        return sprintf("%s '%s' in %s on line %u",
            $this->buildHeader($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }

    /**
     * 例外からエラーメッセージ用のヘッダを生成して返します。
     *
     * @param \Exception|\Throwable $e 例外オブジェクト
     * @return string
     */
    public function buildHeader($e)
    {
        return sprintf("Uncaught Exception %s[%d]:",
            get_class($e),
            $e->getCode()
        );
    }

}
