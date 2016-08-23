<?php
namespace Test\Http;

use Framework\Http\HttpRequest;
use Framework\HFC\Exception\ParameterErrorException;

class TestHttpRequest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $this->testGetId();
        $this->testSetAndGetURI();
    }

    public function testSetAndGetURI()
    {
        try {
            new HttpRequest('http://asdf:adsf/asdf@asdfa/asdf');
        } catch (ParameterErrorException $e) {
            // 出现这个exception就对了
        } catch (\Exception $e) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $host = '127.0.0.1';
        $port = 60000;
        $req = new HttpRequest("http://$host:$port/abc?name=%2f");
        if ("$host:$port" != $req->getHeader(HttpRequest::HEADER_HOST)) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if ('/abc?name=%2f' != $req->getRequestURI()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testGetId()
    {
        $req = new HttpRequest();
        $id = $req->getId();
        if (empty($id)) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testSetAndGet()
    {
        $req = new HttpRequest();
        $req->set('name', 'hoheart');
        if ('hoheart' != $req->get('name')) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testSetAndGetMethod()
    {
        $req = new HttpRequest();
        $req->setMethod('PUT');
        if ('PUT' != $req->getMethod()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testIsAjax()
    {
        $req = new HttpRequest();
        $val = $req->setHeader('HTTP_X_REQUESTED_WITH', 'xmlhttpRequest');
        if (! $req->isAjaxRequest()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }
    
    public function testGetClientIp(){
        $req = new HttpRequest();
        $req->setHeader('', $value)
    }

    public function testPack()
    {
        $host = '127.0.0.1';
        $port = 60000;
        
        try {
            new HttpRequest('http://asdf:adsf/asdf@asdfa/asdf');
        } catch (ParameterErrorException $e) {
            // 出现这个exception就对了
        } catch (\Exception $e) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $req = new HttpRequest("http://$host:$port/abc?name=%2f");
        $req->setMethod('POST');
        $body = '01234';
        $req->addBody($body);
        
        $packedData = $req->pack();
        
        $reqStr = 'POST /abc?name=%2f HTTP/1.1' . "\r\n";
        $reqStr .= 'Host: 127.0.0.1:' . "$port\r\n";
        $reqStr .= 'Content-Length: ' . strlen($body) . "\r\n";
        $reqStr .= "\r\n";
        $reqStr .= $body;
        if ($packedData !== $reqStr) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }
}