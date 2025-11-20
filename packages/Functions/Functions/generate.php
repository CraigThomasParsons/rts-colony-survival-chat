#!/usr/bin/env php
<?php
$dir = new DirectoryIterator(dirname(__FILE__));
echo "#!/usr/bin/env bash\n";
$crudObjects = [];
foreach ($dir as $fileinfo)
{
    if ($fileinfo->isDot() === false && $fileinfo->isDir() === true) {
        $crudObjects[] = $fileinfo->getFilename();
    }
}
?>

##########################################################################################
# todo: WHen you want to create a new anything. Like todo, note, log.
#       - Create a case statement that calls other functions so you can do
#         new log instead of newlog
##########################################################################################
new() {
<?php
    echo 'case "$1" in'."\n";
    foreach ($crudObjects as $directoryName)
    {
        echo '"'.$directoryName.'" )'."\n";?>
        . ~/Functions/<?=$directoryName;?>/create.sh
        ;;
        <?php
    }?>
  esac
}
##########################################################################################
# get functions = less ~/.bash_functions. Wanted this to be "show"
##########################################################################################
function get() {
<?php
    echo 'case "$1" in'."\n";
    foreach ($crudObjects as $directoryName)
    {
        echo '"'.$directoryName.'" )'."\n";
        echo 'echo "to be implented '.$directoryName.'"'."\n".';;';
    }
    echo 'esac'."\n";
?>
}

##########################################################################################
# I want this to be a Node.js thing. Using commander.js
# List a bunch of todos for example.
##########################################################################################
list() {
    <?php
    echo 'case "$1" in'."\n";
    foreach ($crudObjects as $directoryName)
    {
        echo '"'.$directoryName.'" )'."\n";
        echo 'echo "to be implented '.$directoryName.'"'."\n".';;';
    }
    echo 'esac'."\n";
?>
}

##########################################################################################
# Edit, bunch of shortcuts to vim. vim ~/.bash_functions
##########################################################################################
edit() {
<?php
    echo 'case "$1" in'."\n";
    foreach ($crudObjects as $directoryName)
    {
        echo '"'.$directoryName.'" )'."\n";
        echo 'echo "to be implented '.$directoryName.'"'."\n".';;';
    }
    echo 'esac'."\n";
?>
}