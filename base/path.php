<?php
// Please load this at init

function uriParser($uri)
{
   // PHP parse_url is almost good enough
   // but I need to break up path
   $uri = parse_url($uri);
   
   if (null !== $path = &$uri["path"])
   {
      $path = explode("/", $path);
      
      if ("" == $path[0])
      {
         array_splice($path, 0, 1);
      }
   }
   
   return $uri;
}

function getPath()
{
   // Apache webserver return $_SERVER["REQUEST_URI"]
   if (null !== $uri = $_SERVER["REQUEST_URI"])
   {
      return uriParser($uri);
   }
}