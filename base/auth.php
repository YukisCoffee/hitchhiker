<?php
function getAuthHeader($sapisid)
{
   // SHA1 hash of SAPISID
   return "SAPISIDHASH " . ($time = time()) . "_" .
      sha1($time . " " . $sapisid . " " . "https://www.youtube.com");
}

function shouldAuth()
{
   return (isset($_COOKIE) && isset($_COOKIE["SAPISID"]));
}