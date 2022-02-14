<?php
return function (&$template, &$vars, &$global)
{
   allowSignin($global);
   $template = "pages/tobeimplemented.html.twig";
   $vars["message"] = "This page has yet to be implemented. If you want to help add it, <a href=\"//github.com/YukisCoffee/hitchhiker/pulls\">please make a GitHub pull request.</a>";
};