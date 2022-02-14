<?php
const EXT_TYPES =
   [
      "png" => "image/png",
      "gif" => "image/gif",
      "jpg" => "image/jpeg",
      "jpeg" => "image/jpeg",
      "html" => "text/html",
      "js" => "text/javascript",
      "json" => "application/json",
      "css" => "text/css"
   ];

return function ($path)
{
   error_reporting(0);
   
   $path = implode("/", $path["path"]);
   
   if (file_exists($path))
   {
      $ext = getExt($path);
      $mime = getMimeType($ext);
      
      setContentType($mime);
      
      echo file_get_contents($path);
      
      exit();
   }
};

function setContentType($type)
{
   header("Content-Type: " . $type);
}

function getExt($path)
{
   $path = explode(".", $path);
   $path = $path[count($path) - 1];
   return $path;
}

function getMimeType($ext)
{
   foreach(EXT_TYPES as $iteratedExt => $mimeType)
   {
      if ($iteratedExt == $ext)
      {
         return $mimeType;
      }
   }
   
   return "text/plain";
}