<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * トレースフォーマッタ
 *
 * @author k.holy74@gmail.com
 */
class TraceFormatter implements TraceFormatterInterface
{

    /**
     * スタックトレースを文字列に整形して返します。
     *
     * @param array $stackTrace スタックトレース
     * @return string
     */
    public function __invoke(array $stackTrace): string
    {
        return $this->arrayToString($stackTrace);
    }

    /**
     * スタックトレースを文字列に整形して返します。
     *
     * @param array $stackTrace スタックトレース
     * @return string
     */
    public function arrayToString(array $stackTrace): string
    {
        $results = [];
        foreach ($stackTrace as $trace) {
            $results[] = $this->format($trace);
        }
        return (count($results) >= 1)
            ? sprintf("\nStack trace:\n%s", implode("\n", $results))
            : '';
    }

    /**
     * 1レコード分のトレースを文字列に整形して返します。
     *
     * @param array $trace 1レコード分のトレース
     * @return string
     */
    public function format(array $trace): string
    {
        return sprintf('%s: %s(%s)',
            $this->formatLocation(
                $trace['file'] ?? null,
                $trace['line'] ?? null
            ),
            $this->formatFunction(
                $trace['class'] ?? null,
                $trace['type'] ?? null,
                $trace['function'] ?? null
            ),
            $this->formatArguments(
                $trace['args'] ?? null
            )
        );
    }

    /**
     * トレースのファイル情報を文字列に整形して返します。
     *
     * @param string|null $file ファイルパス
     * @param string|null $line 行番号
     * @return string
     */
    public function formatLocation(?string $file, ?string $line): string
    {
        return (isset($file) && isset($line))
            ? sprintf('%s(%d)', $file, $line)
            : '[internal function]';
    }

    /**
     * トレースの関数呼び出し情報を文字列に整形して返します。
     *
     * @param string|null $class クラス名
     * @param string|null $type 呼び出し種別
     * @param string|null $function 関数名/メソッド名
     * @return string
     */
    public function formatFunction(?string $class, ?string $type, ?string $function): string
    {
        return sprintf('%s%s%s', $class ?? '', $type ?? '', $function ?? '');
    }

    /**
     * トレースの関数呼び出しの引数を文字列に整形して返します。
     *
     * @param array|null $arguments 引数の配列
     * @return string
     */
    public function formatArguments(?array $arguments): string
    {
        if (empty($arguments)) {
            return '';
        }
        return implode(', ', array_map(function ($arg) {
            $self = $this;
            if (is_array($arg)) {
                $vars = [];
                foreach ($arg as $key => $var) {
                    $vars[] = sprintf('%s=>%s',
                        $self->formatVar($key),
                        $self->formatVar($var)
                    );
                }
                return sprintf('[%s]', implode(', ', $vars));
            }
            return $self->formatVar($arg);
        }, $arguments));
    }

    /**
     * 変数の型の文字列表現を返します。
     *
     * @param mixed $var
     * @return string
     */
    public function formatVar(mixed $var): string
    {
        if (is_null($var)) {
            return 'NULL';
        }

        if (is_int($var)) {
            return sprintf('Int(%d)', $var);
        }

        if (is_float($var)) {
            return sprintf('Float(%F)', $var);
        }

        if (is_string($var)) {
            return sprintf("'%s'", $var);
        }

        if (is_bool($var)) {
            return sprintf('Bool(%s)', $var ? 'true' : 'false');
        }

        if (is_array($var)) {
            return 'Array';
        }

        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        return sprintf('%s', gettype($var));
    }

}
