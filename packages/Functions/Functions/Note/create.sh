#!/usr/bin/env bash
  DATE=`date '+%m-%d-%Y_%H-%M-%S'`
  file="notes-$DATE.txt"
  #file="notes.txt"
  (
    # Go to OneDrive - City of Ottawa\Documents\notes\notes.txt
    cd ~/Documents/notes
    touch ${file}
    topstamp $file
    vim $file
  )