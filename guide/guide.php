<?php
require "loguide.php";
require "mainguide.php";
return function ($selectedItem = "", &$global)
{
   return
      [
         "guideToplevel" => shouldAuth() ? mainguide($selectedItem, $global) : loguide($selectedItem)
      ] +
      [
         "selectedItem" => $selectedItem
      ];
};

function guideSelectedItem($update = false, $value = "")
{
   static $selectedItem = "";
   
   if ($update)
   {
      $selectedItem = $value;
   }
   
   return $selectedItem;
}

function guideToplevel(...$sections)
{
   return ["sections" => $sections];
}

function guideSection($title = "", ...$items)
{
   $useTitle = ("" != $title);
   
   return
      ($useTitle ? ["title" => $title] : []) +
      ["items" => $items];
}

function guideSubscriptionsSection($title = "", ...$items)
{
   $useTitle = ("" != $title);
   
   return
      ["type" => "subscriptionsSection"] +
      ($useTitle ? ["title" => $title] : []) +
      ["items" => $items];
}

function guideItem($id, $title, $href, $thumb = "", $unseen = 0, $subtitle = "")
{
   $selected = ($id == guideSelectedItem());
   
   $useThumb = ("" != $thumb);
   $useUnseen = (0 != $unseen);
   $useSubtitle = ("" != $subtitle);
   $useSystemIcon = ("SYSTEM::" == substr($thumb, 0, 8)) 
      && $systemIcon = substr($thumb, 8, strlen($thumb));
   
   // PHP complains about unenclosed ? : operators
   return
      [
         "ITEM_TYPE" => "guideItem",
         "id" => $id,
         "title" => $title,
         "href" => $href,
         "selected" => $selected
      ] +
      ($useThumb ? (
         $useSystemIcon ? ["systemIcon" => $systemIcon] : ["thumb" => $thumb] 
      ) : []) +
      ($useUnseen ? ["unseen" => (string)$unseen] : []) +
      ($useSubtitle ? ["subtitle" => $subtitle] : []);
}