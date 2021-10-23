<?php

use App\Repositories\ObjectRepository;
use App\Http\Requests\ObjectRequest;
use App\Models\ObjectKVM;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ObjectRepositoryTest extends TestCase
{
    protected $objectRepository;

    public function setUp(): void {
        parent::setUp();
        $this->objectRepository = new ObjectRepository();
    }

    public function test_list_success() {
        $expectedFromDb = new class{};
        $expectedFromDb->obj_key = "foo";
        $expectedFromDb->obj_value = "bar";
        
        DB::shouldReceive("table")->once()->with("objects")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("obj_key","obj_value")->andReturnSelf();
        DB::shouldReceive('get')->once()->andReturn([$expectedFromDb]);

        $objects = $this->objectRepository->list();
        $this->assertNotEmpty($objects);
    }

    public function test_list_empty() {
        DB::shouldReceive("table")->once()->with("objects")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("obj_key","obj_value")->andReturnSelf();
        DB::shouldReceive('get')->once()->andReturn([]);

        $objects = $this->objectRepository->list();
        $this->assertEmpty($objects);
    }

    public function test_get_with_key() {
        $request = new ObjectRequest();
        $request->key = "foo";
        $expectedValue = new class{};
        $expectedValue->obj_value = "bar";

        DB::shouldReceive("table")->once()->with("objects")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("obj_value")->andReturnSelf();
        DB::shouldReceive("where")->once()->with("obj_key", "=", $request->key)->andReturnSelf();
        DB::shouldReceive("first")->once()->andReturn($expectedValue);
        
        $expectedObj = new ObjectKVM();
        $expectedObj->key = $request->key;
        $expectedObj->value = $expectedValue->obj_value;

        $obj = $this->objectRepository->get($request);
        $this->assertEquals($expectedObj, $obj);
    }

    public function test_get_with_key_notfound() {
        $request = new ObjectRequest();
        $request->key = "foo";
        $expectedValue = null;

        DB::shouldReceive("table")->once()->with("objects")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("obj_value")->andReturnSelf();
        DB::shouldReceive("where")->once()->with("obj_key", "=", $request->key)->andReturnSelf();
        DB::shouldReceive("first")->once()->andReturn($expectedValue);
        
        $this->expectException(ModelNotFoundException::class);
        $this->objectRepository->get($request);
    }

    public function test_get_with_key_and_timestamp() {
        $request = new ObjectRequest();
        $request->key = "foo";
        $request->timestamp = 1000;
        $expectedValue = new class{};
        $expectedValue->obj_value = "baz";

        DB::shouldReceive("table")->once()->with("objects_log")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("obj_value")->andReturnSelf();
        DB::shouldReceive("where")->once()->with("obj_key", "=", $request->key)->andReturnSelf();
        DB::shouldReceive("where")->once()->with("ts", ">=", date('Y-m-d H:i:s', $request->timestamp))->andReturnSelf();
        DB::shouldReceive("oldest")->once()->with("ts")->andReturnSelf();
        DB::shouldReceive("first")->once()->andReturn($expectedValue);
        
        $expectedObj = new ObjectKVM();
        $expectedObj->key = $request->key;
        $expectedObj->value = $expectedValue->obj_value;

        $obj = $this->objectRepository->get($request);
        $this->assertEquals($expectedObj, $obj);
    }

    public function test_get_with_key_and_timestamp_notfound() {
        $request = new ObjectRequest();
        $request->key = "foo";
        $request->timestamp = 1000;
        $expectedValue = null;

        DB::shouldReceive("table")->once()->with("objects_log")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("obj_value")->andReturnSelf();
        DB::shouldReceive("where")->once()->with("obj_key", "=", $request->key)->andReturnSelf();
        DB::shouldReceive("where")->once()->with("ts", ">=", date('Y-m-d H:i:s', $request->timestamp))->andReturnSelf();
        DB::shouldReceive("oldest")->once()->with("ts")->andReturnSelf();
        DB::shouldReceive("first")->once()->andReturn($expectedValue);
        
        $this->expectException(ModelNotFoundException::class);
        $this->objectRepository->get($request);
    }

    public function test_create() {
        $body = ["foo" => "bar", "ping" => "pong"];

        DB::shouldReceive("beginTransaction")->once()->andReturn(null);
        DB::shouldReceive("table")->twice()->with("objects")->andReturnSelf();
        DB::shouldReceive("updateOrInsert")->once()->with(["obj_key" => "foo"], ["obj_value" => "bar"])->andReturnSelf();
        DB::shouldReceive("updateOrInsert")->once()->with(["obj_key" => "ping"], ["obj_value" => "pong"])->andReturnSelf();        
        
        DB::shouldReceive("table")->once()->with("objects_log")->andReturnSelf();
        $expectedRowsToInsert = [["obj_key"=>"foo", "obj_value"=>"bar"], ["obj_key"=>"ping", "obj_value"=>"pong"]];
        DB::shouldReceive("insert")->once()->with($expectedRowsToInsert)->andReturnSelf();

        $mockRowTs = new class{};
        $mockRowTs->ts = date('Y-m-d H:i:s', 1000);
        DB::shouldReceive("table")->once()->with("objects_log")->andReturnSelf();
        DB::shouldReceive("select")->once()->with("ts")->andReturnSelf();
        DB::shouldReceive("latest")->once()->with("ts")->andReturnSelf();
        DB::shouldReceive("first")->once()->andReturn($mockRowTs);
        DB::shouldReceive("commit")->once()->andReturn(null);

        $ts = $this->objectRepository->create($body);
        $this->assertEquals(1000, $ts);
    }
}