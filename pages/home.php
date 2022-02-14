<?php
return function (&$template, &$vars, &$global)
{
   allowSignin($global);
   $vars["ytPageName"] = "home";
   $feedName = "FEwhat_to_watch";
   $vars["guide"] = (require "guide/guide.php")($feedName, $global);
   /*
   echo "<pre>";
   var_dump( $vars["guide"] );
   echo "</pre>";
   // */
   
   $template = "pages/home.html.twig";
   
   $serverResponse = request("https://www.youtube.com/");
   
   $data = extractParseData($serverResponse)
      ->contents->twoColumnBrowseResultsRenderer->tabs[0]
      ->tabRenderer->content->richGridRenderer->contents;
   
   $vars["home"] = lazyCvHpItems($data);
};

function lazyCvHpItems($items)
{
   $new = [];
   
   for ($i = 0; $i < count($items); $i++) if (isset($items[$i]->richItemRenderer))
   {
      $new[] = Youtubei::richItemRenderer($items[$i]->richItemRenderer);
   }
   
   return $new;
}