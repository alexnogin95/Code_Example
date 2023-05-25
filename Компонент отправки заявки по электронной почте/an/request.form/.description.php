<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
    "NAME" => GetMessage("COMP_NAME"),
    "DESCRIPTION" => GetMessage("COMP_DESCRIPTION"),
    "PATH" => array(
        "ID" => "an_components",
        "CHILD" => array(
            "ID" => "request_form",
            "NAME" => GetMessage("COMP_CHILD"),
    ),
    ),
);