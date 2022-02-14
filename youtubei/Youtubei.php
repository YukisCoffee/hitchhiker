<?php
const optional = true;

function arrItem($key, $value, $optional = false)
{
   if (!$optional)
   {
      return [$key => $value];
   }
   else
   {
      return isset($key) ? [$key => $value] : [];
   }
}

class Youtubei
{
   // Class for isolated function names
   
   static function Util_RunsUrl($the_run)
   {
      if (isset($the_run->navigationEndpoint->commandMetadata->webCommandMetadata->url))
      {
         return $the_run->navigationEndpoint->commandMetadata->webCommandMetadata->url;
      }
   }
   
   static function thumbnails($context)
   {
      return $context[0]->url;
   }
   
   static function videoRenderer($context)
   {
      $new = [];
      
      $new = 
         arrItem("title", ($context->title->runs[0]->text ?? $context->title->simpleText)) +
         arrItem("thumbnail", self::thumbnails($context->thumbnail->thumbnails)) +
         arrItem("videoId", $context->videoId) +
         arrItem(
            "time", 
            @$context->thumbnailOverlays[0]->thumbnailOverlayTimeStatusRenderer->text->simpleText,
            optional
         ) +
         arrItem("owner", $context->shortBylineText->runs[0]->text) +
         arrItem("ownerUrl", self::Util_RunsUrl($context->shortBylineText->runs[0])) +
         arrItem("views", @$context->viewCountText->simpleText, optional) +
         arrItem("date", @$context->publishedTimeText->simpleText, optional);
         
      return $new;
   }
   
   static function richItemRenderer($context)
   {
      if (isset($context->content->videoRenderer))
      {
         return self::videoRenderer($context->content->videoRenderer);
      }
   }
   
   static function compactVideoRenderer($context)
   {
      // Same structure as videoRenderer
      return self::videoRenderer($context);
   }
}