<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * トレース
 *
 * @property $location
 * @property $function
 * @property $argument
 *
 * @author k.holy74@gmail.com
 */
class Trace implements \ArrayAccess
{

    /**
     * @var TraceFormatterInterface トレースフォーマッタ
     */
    private $formatter;

    /**
     * @var array トレース情報の連想配列
     */
    private $trace;

    /**
     * コンストラクタ
     *
     * @param TraceFormatterInterface $formatter トレースフォーマッタ
     * @param array $trace トレース情報の連想配列
     */
    public function __construct(TraceFormatterInterface $formatter, array $trace)
    {
        $this->formatter = $formatter;
        $this->initialize($trace);
    }

    /**
     * オブジェクトを初期化します。
     *
     * @param array|null $trace トレース情報の連想配列
     * @return self
     */
    public function initialize(array $trace = null): self
    {
        $this->trace = ($trace !== null) ? $trace : [];
        return $this;
    }

    /**
     * ファイル情報を文字列に整形して返します。
     *
     * @return string
     */
    public function formatLocation(): string
    {
        return $this->formatter->formatLocation(
            $this->trace['file'] ?? null,
            $this->trace['line'] ?? null
        );
    }

    /**
     * 関数呼び出し情報を文字列に整形して返します。
     *
     * @return string
     */
    public function formatFunction(): string
    {
        return $this->formatter->formatFunction(
            $this->trace['class'] ?? null,
            $this->trace['type'] ?? null,
            $this->trace['function'] ?? null
        );
    }

    /**
     * 関数呼び出しの引数を文字列に整形して返します。
     *
     * @return string
     */
    public function formatArgument(): string
    {
        return $this->formatter->formatArguments(
            $this->trace['args'] ?? null
        );
    }

    /**
     * 配列に整形して返します。
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'location' => $this->formatLocation(),
            'function' => $this->formatFunction(),
            'argument' => $this->formatArgument(),
        ];
    }

    /**
     * 文字列に整形して返します。
     *
     * @return string
     */
    public function __toString()
    {
        return $this->formatter->format($this->trace);
    }

    /**
     * __isset
     *
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return method_exists($this, 'format' . ucfirst($name));
    }

    /**
     * __get
     *
     * @param mixed $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if (method_exists($this, 'format' . ucfirst($name))) {
            return $this->{'format' . ucfirst($name)}();
        }
        throw new \InvalidArgumentException(
            sprintf('The property "%s" does not exists.', $name)
        );
    }

    /**
     * ArrayAccess::offsetExists()
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess::offsetGet()
     *
     * @param mixed $offset
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * __clone for clone
     */
    public function __clone()
    {
        $this->formatter = clone $this->formatter;
    }

    /**
     * __sleep for serialize()
     *
     * @return array
     */
    public function __sleep()
    {
        return array_keys(get_object_vars($this));
    }

    /**
     * __set
     *
     * @param mixed $name
     * @param mixed $value
     * @throws \LogicException
     */
    final public function __set($name, $value)
    {
        throw new \LogicException(
            sprintf('The property "%s" could not set.', $name)
        );
    }

    /**
     * __unset
     *
     * @param mixed $name
     * @throws \LogicException
     */
    final public function __unset($name)
    {
        throw new \LogicException(
            sprintf('The property "%s" could not unset.', $name)
        );
    }

    /**
     * ArrayAccess::offsetSet()
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws \LogicException
     */
    final public function offsetSet($offset, $value)
    {
        throw new \LogicException(
            sprintf('The property "%s" could not set.', $offset)
        );
    }

    /**
     * ArrayAccess::offsetUnset()
     *
     * @param mixed $offset
     * @throws \LogicException
     */
    final public function offsetUnset($offset)
    {
        throw new \LogicException(
            sprintf('The property "%s" could not unset.', $offset)
        );
    }

}
