#!/bin/bash
php scripts/reset_queue.php
rm assets/songs/*.mp3
killall mplayer
