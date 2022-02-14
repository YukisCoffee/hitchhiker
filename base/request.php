<?php
function request($url, $options = [])
{
   static $shouldAuth = null;
   if (null === $shouldAuth)
   {
      $shouldAuth = shouldAuth();
   }
   
   defaultReqOptions($options);
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
   while (200 !== curl_getinfo($ch, CURLINFO_HTTP_CODE) && $attempts < 150);
   
   curl_close($ch);
   
   return $response;
}


function defaultReqOptions(&$options)
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
      ];
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