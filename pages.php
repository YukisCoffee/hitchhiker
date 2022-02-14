<?php
const PAGES = 
   [
      "/" => "pages/home.php",
      "/watch" => "pages/watch.php",
      "/channel/**" => "pages/channel.php",
      "/c/**" => "pages/channel.php",
      "/user/**" => "pages/channel.php",
      "/feed/**" => "pages/feed.php",
      "/results" => "pages/results.php",
      "/playlist" => "pages/playlist.php"
   ];
const PAGES_FALLBACK_PAGE = "pages/unknown.php";

function getPage($path)
{
   $strPath = "/" . implode("/", $path["path"]); // Pure evil
   
   foreach (PAGES as $glob => $pointer)
   {
      // Todo: fnmatch does not work on some configurations,
      // notably old PHP versions on Windows.
      if (fnmatch($glob, $strPath))
      {
         return @include PAGES[$glob];
      }
   }
   
   return include PAGES_FALLBACK_PAGE;
}