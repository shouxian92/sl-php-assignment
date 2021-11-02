<?php

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\ObjectKVM;
use Illuminate\Support\Facades\DB;

class ObjectRepository
{
    protected $dateFormat = "Y-m-d H:i:s";
    // Returns the list of object KVM
    public function list() {
        $insertRows = DB::table("objects")->select("obj_key","obj_value")->get();

        $objects = array();
        foreach ($insertRows as $row) {
            $obj = new ObjectKVM();
            $obj->key = $row->obj_key;
            $obj->value = $row->obj_value;
            $objects[] = $obj;
        }
        return $objects;
    }

    /**
     * Returns an ObjectKVM instance given a key
     */ 
    public function get($request) {
        $query = $this->getDBWithKey($request);
        $row = $query->first();
        if (!$row) {
            throw new NotFoundException("Key was not found." );
        }
        $obj = new ObjectKVM();
        $obj->key = $request->key;
        $obj->value = $row->obj_value;
        return $obj;
    }

    /**
     * Builds a query between tables if the timestamp is present
     */
    protected function getDBWithKey($request) {
         if ($request->timestamp) {
            return DB::table("objects_log")
                            ->select("obj_value")
                            ->where("obj_key", "=", $request->key)
                            ->where("ts",  ">=", date($this->dateFormat, $request->timestamp))
                            ->oldest("ts");
        }
        return DB::table("objects")->select("obj_value")->where("obj_key", "=", $request->key);
    }

    /**
     * Given a json key value map, insert or update the values in the objects table. Returns the time the row was inserted in UNIX timestamp format (db time).
     */ 
    public function create($body) {
        DB::beginTransaction();
        try {
            $ts = $this->executeInsert($body);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return strtotime($ts);
    }

    protected function executeInsert($body) {
        $insertRows = array();
        foreach($body as $key => $value) {
            $row = array();
            $row["obj_key"] = $key;
            $row["obj_value"] = $value;
            $insertRows[] = $row;
            // allows for "last insert wins", this can be replaced with upsert() in laravel framework 8.9.X to save on the insert calls
            DB::table("objects")->updateOrInsert(
                ["obj_key" => $key],
                ["obj_value" => $value]
            );
        }
        
        DB::table("objects_log")->insert($insertRows);
        $tsRow = DB::table("objects_log")->select("ts")->latest("ts")->first();
        return $tsRow->ts;
    }
}
