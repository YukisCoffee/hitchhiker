<?php
function loguide($selectedItem)
{
   $guide = [];
   
   guideSelectedItem(true, $selectedItem);
   
   $guide = 
      guideToplevel(
         guideSection("",
            guideItem("FEwhat_to_watch", "Popular on YouTube", "/", "/yts/guide/popular_on_youtube.jpg"),
            guideItem("music", "Music", "/music", "/yts/guide/music.jpg"),
            guideItem("sports", "Sports", "/sports", "/yts/guide/sports.jpg"),
            guideItem("education", "Education", "/education", "/yts/guide/education.jpg"),
            guideItem("news", "News", "/news", "/yts/guide/news.jpg"),
            guideItem("live", "Live", "/live", "/yts/guide/live.jpg")
         ),
         guideSection("Channels for you",
            ...getRecommendedChannels()
         ),
         guideSection("",
           guideItem("guide_builder", "Browse channels", "/channels", "SYSTEM::guide-management-plus") 
         )
      ) + 
      [
         "signupPromo" => [
            "text" => "Sign in now to see your channels and recommendations!",
            "button" => [
               "text" => "Sign in"
            ]
         ]
      ];
   
   return $guide;
}

function getRecommendedChannels()
{
   $data = extractParseData(request("https://www.youtube.com/feed/guide_builder"))
      ->contents->twoColumnBrowseResultsRenderer->tabs[0]->tabRenderer->content
      ->sectionListRenderer->contents;
   
   array_splice($data, 0, 1);
   
   $channels = [];
   
   for ($i = 0; $i < 6; $i++)
   {
      $rng0 = rand(0, count($data));
      $rng1 = rand(0, 7);
      
      if (isset($data[$rng0]->itemSectionRenderer->contents[0]->shelfRenderer->content->horizontalListRenderer->items[$rng1]))
      {
         $channels[] = processRecChannel($data[$rng0]->itemSectionRenderer->contents[0]->shelfRenderer->content->horizontalListRenderer->items[$rng1]->gridChannelRenderer);
      }
   }
   
   return $channels;
}

function processRecChannel($data)
{
   $title = $data->title->simpleText;
   $thumbnail = $data->thumbnail->thumbnails[0]->url;
   $endpoint = $data->navigationEndpoint->commandMetadata->webCommandMetadata->url;
   $id = $data->channelId;
   
   return guideItem($id, $title, $endpoint, $thumbnail);
}