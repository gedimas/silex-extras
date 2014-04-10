<?php

namespace Aptoma\Log;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $app = new Application();
        $request = new Request;
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request_stack'] = $requestStack;

        $fakeToken = $this->getMock('\FakeContext', array('getUsername'));
        $fakeToken->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('testuser'));

        $context = $this->getMock('\FakeContext', array('getToken'));
        $context->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($fakeToken));
        $app['security'] = $context;

        $processor = new RequestProcessor($app);
        $record = $processor(array());

        $this->assertEquals('127.0.0.1', $record['extra']['clientIp']);
        $this->assertEquals('testuser', $record['extra']['user']);
    }

    public function testSetEmptyUsernameWhenNoContextIsFound()
    {
        $app = new Application();
        $processor = new RequestProcessor($app);

        $context = $this->getMock('\FakeContext', array('getToken'));
        $context->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null));
        $app['security'] = $context;

        $record = $processor(array());

        $this->assertEquals('', $record['extra']['user']);
    }
}
