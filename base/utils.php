<?php
function extractData($htmlResponse)
{
   preg_match("/var ytInitialData = ({.*)?;</", $htmlResponse, $matches);
   if ($matches[1])
   {
      return $matches[1];
   }
}

function extractParseData($htmlResponse)
{
   return json_decode(extractData($htmlResponse));
}

function runs2str($runs, $htmlMode = true, $delimiter = " ")
{
   // I gave up on elegance don't bully me
   $out = "";
   
   for ($i = 0; $i < count($runs); $i++)
   {
      if ($htmlMode && isset($runs[$i]->navigationEndpoint))
      {
         $endpoint = $runs[$i]->navigationEndpoint->commandMetadata
            ->webCommandMetadata->url;
         $out .= "<a href=\"" . $endpoint . "\">" . 
            $runs[$i]->text . "</a>";
      }
      else
      {
         $out .= $runs[$i]->text;
      }
      
      if ($i < count($runs) - 1)
      {
         $out .= $delimiter;
      }
   }
   
   return $out;
}

function rich_str2str($richStr, $delimiter = " ")
{
   if (isset($richStr->simpleText))
   {
      return $richStr->simpleText;
   }
   else if (isset($richStr->runs))
   {
      return runs2str($richStr->runs, $delimiter);
   }
   else
   {
      return (string)$richStr;
   }
}

function base64url_encode($data) 
{
   return str_replace("=", "%3D", strtr(base64_encode($data), '+/', '-_'));
} 

function int2uleb128($int)
{
   // this is awful
   // i hate the person who wrot ethis
   
   if ($int < 128) return chr($int);
   
   $out = decbin($int);
   
   while (0 != strlen($out) % 7)
   {
      $out = '0' . $out;
   }
   
   $out = str_split($out, 7);
   
   
   for ($i = 0; $i < count($out); $i++)
   {
      if (0 != $i)
      {
         $out[$i] = '1' . $out[$i];
      }
      else
      {
         $out[$i] = '0' . $out[$i];
      }
   }
   
   $out = array_reverse($out);
   $out = implode('', $out);
   
   $out = str_split($out, 8);
   
   $out2 = "";
   
   for ($i = 0; $i < count($out); $i++)
   {
      $out2 .= chr( bindec($out[$i]) );
   }
   
   return $out2;
}

function genVisitorData($visitor)
{
   // Generate visitorData string
   
   $date = time();
   
   return base64url_encode(
      chr(0x0a) . int2uleb128( strlen($visitor) ) . $visitor . chr(0x28) . int2uleb128($date)
   );
}