<?php
ob_start();
require "base/path.php"; // Prereq path parser

(
function /* preinit */ ()
{
   $path = getPath();
   
   // YTS folder stores resources
   if ("yts" == $path["path"][0])
   {
      staticInit($path); // Avoid unnecessary processing
   }
   else
   {
      mainInit($path);
   }
}
)();

function mainInit($path)
{
   require "vendor/autoload.php"; // Install composer packages
   require "base/auth.php";
   require "base/request.php";
   require "base/utils.php";
   require "base/signin.php";
   require "base/GlobalStore.php";
   require "youtubei/Youtubei.php";
   require "pages.php";
   
   GlobalStore::import("json", "config", "config.json");
   
   $twigVars = [];
   $twigTemplate = "";
   $twigGlobal = [];
   
   // Page Controller callback mechanism
   getPage($path)($twigTemplate, $twigVars, $twigGlobal);
   
   // Init twig and render new data
   $twig = initTwig();
   $twig->addGlobal("global", $twigGlobal);
   
   $output = renderTwig($twig, $twigTemplate, $twigVars);
   
   echo $output;
   ob_end_flush();
}

function staticInit($path)
{
   (require "base/staticld.php")($path);
}

function initTwig()
{
   $fsLoader = new \Twig\Loader\FilesystemLoader("hitchhiker");
   $twig = new \Twig\Environment($fsLoader);
   
   return $twig;
}

function renderTwig($handle, $template, $vars)
{
   return $handle->render($template, $vars);
}