<?php
spl_autoload_register("schemaAutoload");
function schemaAutoload($class_name) {
    //what the loading
    if( ($class_name == "SchText") || ($class_name == "SchNumber") || ($class_name == "SchURL") || ($class_name == "SchDate") || ( strrpos($class_name, "Sch") === false ) )
    {
        return;
    }

    require_once ( $class_name . '.php');
}
require_once "Schema.php";