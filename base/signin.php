<?php
function allowSignin(&$global)
{
   if (shouldAuth())
   {
      $global["signedIn"] = true;
      $global["userInfo"] = getSigninData( requestSigninData() );
   }
   else
   {
      $global["signedIn"] = false;
   }
}

function requestSigninData()
{
   $response = request("https://www.youtube.com/getAccountSwitcherEndpoint");
   
   return json_decode(substr($response, 4, strlen($response)));
}

function getSigninData($response)
{
   $info = [
      "googleAccount" => signinDataGetGoogAccInfo($response),
      "activeChannel" => signinDataGetActiveChannel($response),
      "channelPicker" => signinDataGetChannels($response)
   ];
   
   return $info;
}

function signinDataGetGoogAccInfo($data)
{
   $header = $data->data->actions[0]->getMultiPageMenuAction->
      menu->multiPageMenuRenderer->sections[0]->accountSectionListRenderer->
      header->googleAccountHeaderRenderer;
      
   return
      [
         "email" => $header->email->simpleText,
         "name" => $header->name->simpleText
      ];
}

function signinDataGetActiveChannel($data)
{
   $items = $data->data->actions[0]->getMultiPageMenuAction->
      menu->multiPageMenuRenderer->sections[0]->accountSectionListRenderer->
      contents[0]->accountItemSectionRenderer->contents;
   
   for ($i = 0; $i < count($items); $i++) if (isset($items[$i]->accountItem) && $items[$i]->accountItem->isSelected)
   {
      return AccountData::accountItem($items[$i]->accountItem);
   }
}

function signinDataGetChannels($data)
{
   $items = $data->data->actions[0]->getMultiPageMenuAction->
      menu->multiPageMenuRenderer->sections[0]->accountSectionListRenderer->
      contents[0]->accountItemSectionRenderer->contents;
      
   $channels = [];
   
   for ($i = 0; $i < count($items); $i++) if (isset($items[$i]->accountItem))
   {
      $channels[] = AccountData::accountItem($items[$i]->accountItem);
   }
   
   return $channels;
}

class AccountData
{
   static function accountItem($account)
   {
      return
         [
            "name" => $account->accountName->simpleText,
            "photo" => $account->accountPhoto->thumbnails[0]->url,
            "byline" => $account->accountByline->simpleText,
            "selected" => $account->isSelected,
            "hasChannel" => $account->hasChannel
         ];
   }
}