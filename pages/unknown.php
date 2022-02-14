<?php
return function (&$template, &$vars, &$global)
{
   allowSignin($global);
   $template = "pages/tobeimplemented.html.twig";
   $vars["message"] = "This page isn't listed in the router. If it should be, <a href=\"//github.com/YukisCoffee/hitchhiker/issues\">please make a GitHub issue</a>.";
};