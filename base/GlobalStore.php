<?php
class GlobalStore
{
   static $store = [];
   
   static function get($name)
   {
      if (isset(self::$store[$name]))
      {
         return self::$store[$name];
      }
      return null;
   }
   
   static function set($name, $value)
   {
      self::$store[$name] = $value;
   }
   
   static function import($type, $namespace, $file, $require = false)
   {
      switch ($type)
      {
         case "json":
            return self::importJson($namespace, $file, $require);
         break;
      }
   }
   
   static function importJson($namespace, $file, $require)
   {
      if (!file_exists($file))
      {
         self::throwImportJsonError($require);
      }
      
      $json = file_get_contents($file);
      
      $json = @json_decode($json);
      
      if (!is_object($json)) self::throwImportJsonError($require);
      
      foreach ($json as $key => $value)
      {
         self::set($namespace . "." . $key, $value);
      }
      
      return true;
   }
   
   static function throwImportJsonError($require)
   {
      if (!file_exists($file) && $require)
      {
         exit("Failed to import JSON file \"{$file}\"! Does the file exist");
      }
      else
      {
         return null;
      }
   }
}