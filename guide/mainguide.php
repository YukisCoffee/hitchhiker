<?php
function mainguide($selectedItem, &$global)
{
   $visitor = genVisitorData($_COOKIE["VISITOR_INFO1_LIVE"]);
   $data = request("https://www.youtube.com/youtubei/v1/guide?key=AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8", [
      "post" => true,
      "body" => json_encode( (object)[
         "context" => (object)[
            "client" => (object)[
               "clientName" => "WEB",
               "clientVersion" => "2.20220211.01.00",
               "gl" => "US",
               "hl" => "en",
               "visitorData" => $visitor
            ]
         ],
         "fetchLiveState" => true
      ] ),
      "headers" => [
         "Content-Type" => "application/json",
         "x-goog-auth-user" => "0",
         "x-origin" => "https://www.youtube.com",
         "x-goog-visitor-id" => $visitor
      ]
   ]);
   //echo $data;
     // die(genVisitorData($_COOKIE["VISITOR_INFO1_LIVE"]));
   $data = json_decode($data);
   
   $activeChannelUrl = findActiveChannelUrl($data);
   $global["userInfo"]["activeChannelUrl"] = $activeChannelUrl;
   
   $guide = [];
   
   guideSelectedItem(true, $selectedItem);
   
   $guide = 
      guideToplevel(
         guideSection("",
            guideItem($activeChannelUrl, $global["userInfo"]["activeChannel"]["name"], "/channel/" . $activeChannelUrl),
            guideItem("VLWL", "Watch Later", "/playlist?list=WL"),
            guideItem("FEhistory", "Watch History", "/feed/history"),
            guideItem("FEplaylists", "Playlists", "/channel/" . $activeChannelUrl . '/playlists'),
         ),
         guideSection("",
            guideItem("FEwhat_to_watch", "What to watch", "/"),
            guideItem("FEsubscriptions", "My subscriptions", "/feed/subscriptions"),
            guideItem("FEsocial", "Social", "/feed/social")
         ),
         guideSubscriptionsSection("Subscriptions", ...genGuideSubscriptions($data)),
         guideSection("",
           guideItem("guide_builder", "Browse channels", "/channels", "SYSTEM::guide-management-plus"),
           guideItem("subscription_manager", "Manage subscriptions", "/subscription_manager", "SYSTEM::guide-management-settings")
         )
      );
   
   return $guide;
}

function guideFindTheSection($array, $sectionName)
{
   for ($i = 0; $i < @count($array); $i++)
   {
      if (is_array($array) && isset($array[$sectionName]))
      {
         return $array[$sectionName];
      }
      else if (isset($array->{$sectionName}))
      {
         return $array->{$sectionName};
      }
   }
}

function findActiveChannelUrl($data)
{
   if (isset($data->items))
   {
      $videosSection = guideFindTheSection(
         $data->items[0]->guideSectionRenderer->items[3],
         "guideCollapsibleSectionEntryRenderer"
      )->sectionItems;
      
      for ($i = 0; $i < count($videosSection); $i++)
      {
         if ("MY_VIDEOS" == $videosSection[$i]->guideEntryRenderer->icon->iconType)
         {
            return str_replace(
               ["https://", "studio.youtube.com/channel/", "/video"],
               "",
               $videosSection[$i]->guideEntryRenderer->navigationEndpoint->commandMetadata
                  ->webCommandMetadata->url
            );
         }
      }
   }
}

function genGuideSubscriptions($data)
{
   $subsSection = $data->items[1]->guideSubscriptionsSectionRenderer->items;
   
   $subscriptions = [];
   
   $process = function () use (&$subsSection, &$subscriptions)
   {
      for ($i = 0; $i < count($subsSection) - 1; $i++)
      {
         $subscriptions[] = processGuideItem($subsSection[$i]->guideEntryRenderer);
      }
   };
   
   $process();
   
   
   if (count($subsSection) > 7)
   {
      $subsSection = $subsSection[count($subsSection) - 1]->guideCollapsibleEntryRenderer->expandableItems;
      
      $process();
   }
   
   return $subscriptions;
}

function processGuideItem($data)
{
   return
      [
         "title" => $data->formattedTitle->simpleText,
         "thumb" => $data->thumbnail->thumbnails[0]->url,
         "href" => $data->navigationEndpoint->commandMetadata->webCommandMetadata->url,
         "selected" => ($data->entryData->guideEntryData->guideEntryId == guideSelectedItem())
      ];
}