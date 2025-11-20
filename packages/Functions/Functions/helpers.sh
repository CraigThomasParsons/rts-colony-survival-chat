#!/usr/bin/env bash

##########################################################################################
# todo: WHen you want to create a new anything. Like todo, note, log.
#       - Create a case statement that calls other functions so you can do
#         new log instead of newlog
##########################################################################################
new() {
case "$1" in
"Idea" )
        . ~/Functions/Idea/create.sh
        ;;
        "Note" )
        . ~/Functions/Note/create.sh
        ;;
        "Standup" )
        . ~/Functions/Standup/create.sh
        ;;
        "Todo" )
        . ~/Functions/Todo/create.sh
        ;;
          esac
}
##########################################################################################
# get functions = less ~/.bash_functions. Wanted this to be "show"
##########################################################################################
function get() {
case "$1" in
"Idea" )
echo "to be implented Idea"
;;"Note" )
echo "to be implented Note"
;;"Standup" )
echo "to be implented Standup"
;;"Todo" )
echo "to be implented Todo"
;;esac
}

##########################################################################################
# I want this to be a Node.js thing. Using commander.js
# List a bunch of todos for example.
##########################################################################################
list() {
    case "$1" in
"Idea" )
echo "to be implented Idea"
;;"Note" )
echo "to be implented Note"
;;"Standup" )
echo "to be implented Standup"
;;"Todo" )
echo "to be implented Todo"
;;esac
}

##########################################################################################
# Edit, bunch of shortcuts to vim. vim ~/.bash_functions
##########################################################################################
edit() {
case "$1" in
"Idea" )
echo "to be implented Idea"
;;"Note" )
echo "to be implented Note"
;;"Standup" )
echo "to be implented Standup"
;;"Todo" )
echo "to be implented Todo"
;;esac
}