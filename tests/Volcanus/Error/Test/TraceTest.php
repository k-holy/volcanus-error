<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error\Test;

/**
 * TraceTest
 *
 * @author k.holy74@gmail.com
 */
class TraceTest extends \PHPUnit\Framework\TestCase
{

    private function getSource()
    {
        return [
            'file' => '/path/to/class/Test.php',
            'line' => 5,
            'function' => '__construct',
            'class' => 'Test',
            'type' => '->',
            'args' => [1, 2, 3],
        ];
    }

    public function testFormatLocation()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatLocation')
            ->with(
                $this->equalTo($source['file']),
                $this->equalTo($source['line'])
            );

        $trace = new \Volcanus\Error\Trace($formatter, $source);
        $trace->formatLocation();
    }

    public function testGetLocationByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatLocation')
            ->will($this->returnValue('LOCATION'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('LOCATION', $trace->location);
    }

    public function testGetLocationByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatLocation')
            ->will($this->returnValue('LOCATION'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('LOCATION', $trace['location']);
    }

    public function testFormatFunction()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatFunction')
            ->with(
                $this->equalTo($source['class']),
                $this->equalTo($source['type']),
                $this->equalTo($source['function'])
            );

        $trace = new \Volcanus\Error\Trace($formatter, $source);
        $trace->formatFunction();
    }

    public function testGetFunctionByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatFunction')
            ->will($this->returnValue('FUNCTION'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('FUNCTION', $trace->function);
    }

    public function testGetFunctionByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatFunction')
            ->will($this->returnValue('FUNCTION'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('FUNCTION', $trace['function']);
    }

    public function testFormatArgument()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatArguments')
            ->with($this->equalTo($source['args']));

        $trace = new \Volcanus\Error\Trace($formatter, $source);
        $trace->formatArgument();
    }

    public function testGetArgumentByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatArguments')
            ->will($this->returnValue('ARGUMENT'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('ARGUMENT', $trace->argument);
    }

    public function testGetArgumentByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatArguments')
            ->will($this->returnValue('ARGUMENT'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('ARGUMENT', $trace['argument']);
    }

    public function testToArray()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatLocation')
            ->will($this->returnValue('LOCATION'));

        $formatter->expects($this->once())
            ->method('formatFunction')
            ->will($this->returnValue('FUNCTION'));

        $formatter->expects($this->once())
            ->method('formatArguments')
            ->will($this->returnValue('ARGUMENT'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);
        $array = $trace->toArray();

        $this->assertArrayHasKey('location', $array);
        $this->assertEquals('LOCATION', $array['location']);

        $this->assertArrayHasKey('function', $array);
        $this->assertEquals('FUNCTION', $array['function']);

        $this->assertArrayHasKey('argument', $array);
        $this->assertEquals('ARGUMENT', $array['argument']);

    }

    public function testToString()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('format')
            ->will($this->returnValue('formattedSource'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertEquals('formattedSource', $trace->__toString());
    }

    public function testIsSetByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertTrue(isset($trace->location));
        $this->assertTrue(isset($trace->function));
        $this->assertTrue(isset($trace->argument));
    }

    public function testIsSetByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $this->assertTrue(isset($trace['location']));
        $this->assertTrue(isset($trace['function']));
        $this->assertTrue(isset($trace['argument']));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRaiseExceptionWhenGetUnsupportedPropertyByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        /** @noinspection PhpUndefinedFieldInspection */
        $trace->unsupportedProperty;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRaiseExceptionWhenGetUnsupportedPropertyByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $trace['unsupportedProperty'];
    }

    /**
     * @expectedException \LogicException
     */
    public function testRaiseExceptionWhenSetByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $trace->location = 'foo';
    }

    /**
     * @expectedException \LogicException
     */
    public function testRaiseExceptionWhenSetByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        $trace['location'] = 'foo';
    }

    /**
     * @expectedException \LogicException
     */
    public function testRaiseExceptionWhenUnsetByPropertyAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        unset($trace->location);
    }

    /**
     * @expectedException \LogicException
     */
    public function testRaiseExceptionWhenUnsetByArrayAccess()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');

        $trace = new \Volcanus\Error\Trace($formatter, $source);

        unset($trace['location']);
    }

    public function testClone()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->any())
            ->method('formatLocation')
            ->will($this->returnValue('LOCATION'));

        $formatter->expects($this->any())
            ->method('formatFunction')
            ->will($this->returnValue('FUNCTION'));

        $formatter->expects($this->any())
            ->method('formatArguments')
            ->will($this->returnValue('ARGUMENT'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);
        $cloned = clone $trace;
        $this->assertEquals($trace, $cloned);
        $this->assertNotSame($trace, $cloned);
        $this->assertEquals($trace->location, $cloned->location);
        $this->assertEquals($trace->function, $cloned->function);
        $this->assertEquals($trace->argument, $cloned->argument);
    }

    public function testSerialize()
    {
        $source = $this->getSource();

        /** @var $formatter \Volcanus\Error\TraceFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $formatter = $this->createMock('\Volcanus\Error\TraceFormatterInterface');
        $formatter->expects($this->once())
            ->method('formatLocation')
            ->will($this->returnValue('LOCATION'));

        $formatter->expects($this->once())
            ->method('formatFunction')
            ->will($this->returnValue('FUNCTION'));

        $formatter->expects($this->once())
            ->method('formatArguments')
            ->will($this->returnValue('ARGUMENT'));

        $trace = new \Volcanus\Error\Trace($formatter, $source);
        $deserialized = unserialize(serialize($trace));

        $this->assertEquals($trace, $deserialized);
        $this->assertNotSame($trace, $deserialized);
        $this->assertEquals($trace->location, $deserialized->location);
        $this->assertEquals($trace->function, $deserialized->function);
        $this->assertEquals($trace->argument, $deserialized->argument);
    }

}
