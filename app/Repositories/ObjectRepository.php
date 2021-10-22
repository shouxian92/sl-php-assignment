<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ObjectKVM;

class ObjectRepository
{
    // Returns the list of object KVM
    public function list() {
        $rows = app('db')->table("objects")->select("obj_key","obj_value")->get();

        $objects = array();
        foreach ($rows as $row) {
            $obj = new ObjectKVM();
            $obj->key = $row->obj_key;
            $obj->value = $row->obj_value;
            $objects[] = $obj;
        }

        return $objects;
    }

    /**
     * Returns an object model given a key
     */ 
    public function get($key) {
        $row = app('db')->table('objects')->select("obj_value")->where("obj_key", "=", $key)->first();
        if (!$row) {
            throw new ModelNotFoundException("Key doesn't exist.");
        }

        $obj = new ObjectKVM();
        $obj->key = $key;
        $obj->value = $row->obj_value;
        return $obj;
    }

    /**
     * Given a json key value map, insert or update the values in the objects table
     */ 
    public function create($body) {
        app('db')->transaction(function () use($body) {
            foreach($body as $key => $value) {
                app('db')->insert("INSERT INTO objects (obj_key, obj_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE obj_value=?", [$key, $value, $value]);
                app('db')->insert("INSERT INTO objects_log (obj_key, obj_value) VALUES (?, ?)", [$key, $value]);
            }
        });
    }
}
