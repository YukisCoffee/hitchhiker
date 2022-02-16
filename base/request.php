<?php
//require_once "base/GlobalStore.php"; // This breaks the script :(

const REQUESTS_MAX_ATTEMPTS = 50;
function request($url, $options = [])
{
   static $shouldAuth = null;
   if (null === $shouldAuth)
   {
      $shouldAuth = shouldAuth();
   }
   
   defaultReqOptions($options, $url);
   defaultReqHeaders($options["headers"], $shouldAuth);
   
   
   $ch = curl_init($url);
   curl_setopt_array($ch, reqOpts2Curl($options));
   
   $attempts = 0;
   do
   {
      //echo '<pre>'; 
      //echo ' CURLOPT_POST: ' . (string)CURLOPT_POST; 
     // echo ' CURLOPT_POSTFIELDS: ' . (string)CURLOPT_POSTFIELDS; 
     // var_dump(reqOpts2Curl($options)); 
     // echo '</pre>';
      $response = curl_exec($ch);
      $attempts++;
   }
   while (200 !== curl_getinfo($ch, CURLINFO_HTTP_CODE) && $attempts < REQUESTS_MAX_ATTEMPTS);
   
   curl_close($ch);
   
   return $response;
}


function defaultReqOptions(&$options, $url)
{
   // PHP default options can be generated with this
   // pattern since keys are unmodified from the left
   // side if they exist in both
   
   $options = $options + 
      [
         "post" => false,
         "returnTransfer" => true,
         "encoding" => "gzip",
         "headers" => [] // ** Modified later
      ] +
      (
         GlobalStore::get("config.useHostsFile")
            ? ["overrideResolve" => hostsFileWorkaround($url)]
            : []
      );
}

function genCookieHeader()
{
   if (empty($_COOKIE)) return "";
   
   $cookies = "";
   
   foreach ($_COOKIE as $cookie => $value)
   {
      $cookies .= $cookie . '=' . $value . '; ';
   }
   
   return $cookies;
}

function defaultReqHeaders(&$headers, $shouldAuth)
{
   $headers["Cookie"] = genCookieHeader();
   
   if ($shouldAuth)
   {
      $headers["Authorization"] = getAuthHeader( $_COOKIE["SAPISID"] );
   }
}

function reqOpts2Curl($options)
{
   $curlArr = [];
   
   foreach ($options as $option => $value)
   {
      switch ($option)
      {
         // Map option names to CURLOPT names
         case "post": 
            $curlOpt = CURLOPT_POST; 
            break;
         case "returnTransfer": 
            $curlOpt = CURLOPT_RETURNTRANSFER; 
            break;
         case "encoding":
            $curlOpt = CURLOPT_ENCODING;
            break;
         case "body":
            $curlOpt = CURLOPT_POSTFIELDS;
            break;
         case "headers":
            $curlOpt = CURLOPT_HTTPHEADER;
            $value = reqHeaders2Curl($value);
            break;
         case "overrideResolve":
            $curlOpt = CURLOPT_RESOLVE;
            break;
         default: continue 2;
      }
      
      $curlArr[$curlOpt] = $value;
   }
   
   return $curlArr;
}

function reqHeaders2Curl($headers)
{
   $curlHeaders = [];
   
   foreach ($headers as $header => $value)
   {
      $curlHeaders[] = $header . ': '.  $value;
   }
   
   return $curlHeaders;
}

function uriGetHostname($uri, &$out, &$port)
{
   if (0 == strpos($uri, "https"))
   {
      $port = (string)443;
   }
   else if (0 == strpos($uri, "http"))
   {
      $port = (string)80;
   }
   
   $out = preg_replace("/(https)|(http)|(ftp)|(ws)|(wss)|(:\/\/)|(\/.*)/", "", $uri);
}

function nameserverLookup($hostname)
{
   //static $a;
   //if ($a || 0 == ($a = strpos(php_uname("s"), "Windows NT")))
   //{
      $lookup = shell_exec("nslookup {$hostname} 1.1.1.1");
      
      return nslFindIpv4(explode(" ", $lookup));
   //}
}

function nslFindIpv4($addresses)
{
   for ($i = 0; $i < count($addresses); $i++) 
   {
      $addresses[$i] = preg_replace("/\s+/", "", $addresses[$i]);
      if (filter_var($addresses[$i], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
      {
         return $addresses[$i];
      }
   }
   
   return "1.1.1.1";
}

function hostsFileWorkaround($baseUri)
{
   // Override system hosts file
   // by manually querying a nameserver
   // lookup and specifying IP address.
   
   uriGetHostname($baseUri, $hostname, $port);
   
   $nameserver = nameserverLookup($hostname);
   
   return ["{$hostname}:{$port}:{$nameserver}"];
}