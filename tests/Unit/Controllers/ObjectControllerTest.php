<?php

use App\Repositories\ObjectRepository;
use App\Http\Requests\ObjectRequest;
use App\Http\Controllers\ObjectController;
use App\Models\ObjectKVM;
use Illuminate\Http\Request;
use App\Exceptions\BadRequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ObjectControllerTest extends TestCase
{
    protected $controller;
    protected $repository;
    protected $request;

    public function setUp(): void {
        parent::setUp();
        $repoMock = $this->getMockBuilder(ObjectRepository::class)
            ->setMethods(['list', 'create', 'get'])
            ->getMock();
        $this->request = $this->getMockBuilder(Request::class)
            ->setMethods(['get', 'isJson', 'getContent'])
            ->getMock();
        $this->repository = $repoMock;
        $this->controller = new ObjectController($repoMock);
    }

    public function test_list() {
        $mockObjectList = [];
        for ($i=0; $i<2; $i++) {
            $mockObject = new ObjectKVM();
            $mockObject->key = "foo" . $i;
            $mockObject->value = "bar" . $i;
            $mockObjectList[] = $mockObject;
        }
        
        $this->repository->method('list')->willReturn($mockObjectList);

        $response = $this->controller->list();
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"foo0":"bar0","foo1":"bar1"}', $response->content());
    }

    public function test_list_empty() {
        $mockObjectList = [];
        $this->repository->method('list')->willReturn($mockObjectList);

        $response = $this->controller->list();
        $this->assertEquals(200, $response->status());
        $this->assertEquals('[]', $response->content());
    }

    public function test_get() {
        $mockObject = new ObjectKVM();
        $mockObject->key = "foo";
        $mockObject->value = "bar";
        $this->repository->method('get')->willReturn($mockObject);

        $mockRequest = $this->getMockBuilder(Request::class)
        ->setMethods(['get'])
        ->getMock();
        $mockRequest->expects($this->any())->method('get')->willReturn(0);
        $response = $this->controller->get($mockRequest, $mockObject->key);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('"bar"', $response->content());
    }

    public function test_get_timestamp() {
        $mockObject = new ObjectKVM();
        $mockObject->key = "foo";
        $mockObject->value = "bzz";

        $expectedRequest = new ObjectRequest();
        $expectedRequest->key = $mockObject->key;
        $expectedRequest->timestamp = 1000;
        $this->repository->method('get')->with($this->equalTo($expectedRequest))->willReturn($mockObject);
        $this->request->expects($this->any())->method('get')->willReturn($expectedRequest->timestamp);

        $response = $this->controller->get($this->request, $mockObject->key);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('"bzz"', $response->content());
    }

    public function test_get_invalid_timestamp() {
        $mockObject = new ObjectKVM();
        $mockObject->key = "foo";
        $mockObject->value = "bzz";

        $expectedRequest = new ObjectRequest();
        $expectedRequest->key = $mockObject->key;
        $expectedRequest->timestamp = "fizz";
        $this->repository->method('get')->with($this->equalTo($expectedRequest))->willReturn($mockObject);
        $this->request->expects($this->any())->method('get')->willReturn($expectedRequest->timestamp);

        $this->expectException(BadRequestException::class);
        $response = $this->controller->get($this->request, $mockObject->key);
    }

    public function test_post() {
        $arbitraryBody = ["valid"=>"json"];
        $this->request->expects($this->any())->method('isJson')->willReturn(true);
        $this->request->expects($this->any())->method('getContent')->willReturn('{"valid": "json"}');
        
        $this->repository->method("create")->with($this->equalTo($arbitraryBody))->willReturn(1000);

        $response = $this->controller->post($this->request);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"timestamp":1000}', $response->content());
    }

    public function test_post_missing_json_header() {
        $arbitraryBody = new class{};
        $this->request->expects($this->any())->method('isJson')->willReturn(false);

        $this->expectException(BadRequestException::class);
        $this->controller->post($this->request);
    }

    public function test_post_malformed_json_syntax() {
        $arbitraryBody = new class{};
        $arbitraryBody->valid = "json";
        $this->request->expects($this->any())->method('isJson')->willReturn(true);
        $this->request->expects($this->any())->method('getContent')->willReturn('"invalid": "json"}');

        $this->expectException(BadRequestException::class);
        $response = $this->controller->post($this->request);
    }

    public function test_post_empty_payload() {
        $arbitraryBody = new class{};
        $arbitraryBody->valid = "json";
        $this->request->expects($this->any())->method('isJson')->willReturn(true);
        $this->request->expects($this->any())->method('getContent')->willReturn("{}");

        $response = $this->controller->post($this->request);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"No data to process."}', $response->content());
    }
}