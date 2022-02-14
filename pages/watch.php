<?php
return function (&$template, &$vars, &$global)
{
   allowSignin($global);
   $vars["ytPageName"] = "watch";
   $vars["guide"] = (require "guide/guide.php")("", $global);
   $vars["guide"]["collapsible"] = true;
   
   $template = "pages/watch.html.twig";
   $videoId = $_GET["v"];
   
   $serverResponse = extractParseData(request("https://www.youtube.com/watch?v=" . $videoId))
      ->contents->twoColumnWatchNextResults;
      
   $primaryInfo = findContent($serverResponse->results->results->contents, "videoPrimaryInfoRenderer");
   $secondaryInfo = findContent($serverResponse->results->results->contents, "videoSecondaryInfoRenderer");
   $recommended = isset($serverResponse->secondaryResults->secondaryResults->results[0]->relatedChipCloudRenderer)
      ? $serverResponse->secondaryResults->secondaryResults->results[1]->itemSectionRenderer->contents
      : $serverResponse->secondaryResults->secondaryResults->results;
   
   
   $countsResponse = json_decode(request("https://returnyoutubedislikeapi.com/votes?videoId=" . $videoId));
   
   $vars += genVideoInfo($primaryInfo, $secondaryInfo) + 
      ["recommended" => genRecommended($recommended)] +
      genCountsInfo($countsResponse);
   
   $global["ytPageTitle"] = $vars["title"];
   $global["ytPlayerClass"] = "watch-small"; // Disables off-screen player
};

function findContent($contents, $name)
{
   foreach ($contents as $index => $content) if (isset($content->{$name}))
   {
      return $content->{$name};
   }
}

function genVideoInfo($primary, $secondary)
{
   $title = $primary->title->runs[0]->text;
   $date = $primary->dateText->simpleText;
   $views = $primary->viewCount->videoViewCountRenderer->viewCount->simpleText;
   $ownerName = $secondary->owner->videoOwnerRenderer->title->runs[0]->text;
   $ownerPhoto = $secondary->owner->videoOwnerRenderer->thumbnail->thumbnails[0]->url;
   $ownerUrl = $secondary->owner->videoOwnerRenderer->navigationEndpoint->commandMetadata->webCommandMetadata->url;
   $description = genDescription($secondary->description->runs);
   
   return
      [
         "title" => $title,
         "date" => $date,
         "views" => $views,
         "ownerName" => $ownerName,
         "ownerPhoto" => $ownerPhoto,
         "ownerUrl" => $ownerUrl,
         "description" => $description
      ];
}

function genDescription($runs)
{
   $out = "";
   
   foreach ($runs as $index => $run)
   {
      if ($hasLink = isset($run->navigationEndpoint))
      {
         $href = $run->navigationEndpoint->commandMetadata->webCommandMetadata->url;
         $out .= "<a href=\"{$href}\">";
      }
      
      $out .= str_replace("\n", "<br>", $run->text);
      
      if ($hasLink)
      {
         $out .= "</a>";
      }
   }
   
   return $out;
}

function genRecommended($data)
{
   $out = [];
   
   for ($i = 0; $i < count($data); $i++) if (isset($data[$i]->compactVideoRenderer))
   {
      $out[] = Youtubei::compactVideoRenderer($data[$i]->compactVideoRenderer);
   }
   
   return $out;
}

function genCountsInfo($data)
{
   $sparkbarsLikePercentage = ($data->likes) / ($data->likes + $data->dislikes) * 100;
   $sparkbarsDislikePercentage = 100 - $sparkbarsLikePercentage;
   return
      [
         "likes" => number_format($data->likes),
         "dislikes" => number_format($data->dislikes),
         "sparkbarsLikePercentage" => (string)$sparkbarsLikePercentage,
         "sparkbarsDislikePercentage" => (string)$sparkbarsDislikePercentage
      ];
}