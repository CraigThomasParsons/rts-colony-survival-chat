#!/usr/bin/env bash
# Go to OneDrive - City of Ottawa\Documents\notes.txt
cd ~/Documents/notes
cat $(ls -t) > ~/Documents/notes.txt
cat ~/Documents/notes.txt