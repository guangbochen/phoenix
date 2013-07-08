<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * $template: a single html file (required)
 * $data: an array data sent from controller to view (optional)
 * $functions: an array custom function to use in view (optional)
 *
 **/
function twig_render($template, $data = [], $functions = []) 
{
    // Load template from view folder
    $loader = new Twig_Loader_Filesystem('./application/views/');
    $twig = new Twig_Environment($loader);

    //$twig->addFunction(new Twig_SimpleFunction("is_signed_in", "is_signed_in"));
    //$twig->addFunction(new Twig_SimpleFunction("current_user", "current_user"));

    //// Add custom function to twig
    //foreach ($functions as $value) {
        //$twig->addFunction(new Twig_SimpleFunction($value, $value));
    //}

    return $twig->render($template, $data);
}
