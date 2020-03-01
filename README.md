PHP-MPD
=======

A PHP library to communicate with the Music Player Daemon (MPD).

This PHP class provides easy interaction with any MPD server. 
You do not have to know much about MPD to use this class, as long as you know 
the IP, Port (and password) of the MPD server.
This class lets you execute any of the functions available in the MPD server 
and returns the information back to you in an easy managable format that lets 
you use the information on your web-interface.

__Useful Resources:__<br/>
MPD Community Wiki: [http://mpd.wikia.com/wiki/MusicPlayerDaemonWiki](http://mpd.wikia.com/wiki/Music_Player_Daemon_Wiki "Title")<br/>
MPD Wiki Page: [http://en.wikipedia.org/wiki/MusicPlayerDaemon](http://en.wikipedia.org/wiki/Music_Player_Daemon "Title")

## Install

```
composer require maxi/php-mpd
```

Quick Usage Example
-------
	require('mpd.class.php');
	$mpd = new MPD('localhost', 6600);
	if ($mpd->get_connection_status()) {
		print_r($mpd->server_status());
	} else {
		print_r($mpd->get_error());
	}

API
--------
    /**
     * Sets consume state to STATE, STATE should be 0 or 1. 
     * When consume is activated, each song played is removed from playlist. 
	 * 
	 * @param integer $state The consume state
	 * @return boolean Status of the command
	 */
    consume($state)
<p/>

    /**
	 * Sets crossfading between songs.
	 * 
	 * @param integer $seconds Crossfade seconds
	 * @return boolean Status of the command
	 */
    xfade($seconds)
<p/>

    /**
	 * Sets random state to STATE, STATE should be 0 or 1.
	 * 
	 * @param integer $state 0 = no and 1 = yes
	 * @return boolean Status of the command
	 */
	random($state)
<p/>

    /**
	 * Sets repeat state to STATE, STATE should be 0 or 1.
	 * 
	 * @param integer $state 0 = no and 1 = yes
	 * @return boolean Status of the command
	 */
	repeat($state)
<p/>

    /**
	 * Sets volume to VOL, the range of volume is 0-100.
	 * 
	 * @param integer $vol The new volume
	 * @return boolean Status of the command
	 */
	setvol($vol)
<p/>

    /**
	 * Adjusts the volume up or down depending if the modifier is positive or negative
	 * 
	 * @param integer $mod Volume modification
	 * @return boolean Status of the command
	 */
	adjust_vol($mod)
<p/>

    /**
	 * Sets single state to STATE, STATE should be 0 or 1. When single is activated, playback 
	 * is stopped after current song, or song is repeated if the 'repeat' mode is enabled. 
	 * 
	 * @param integer $state Activate or deactivate single
	 * @return boolean Status of the command
	 */
	single($state)
<p/>

    /**
	 * Plays next song in the playlist.
	 * 
	 * @return boolean Status of the command
	 */
	next()
<p/>

    /**
	 * Plays previous song in the playlist.
	 * 
	 * @return boolean Status of the command
	 */
	prev()
<p/>

    /**
	 * Toggles pause/resumes playing, PAUSE is 0 or 1. 
	 * 
	 * @param integer $pause 0 = play and 1 = pause
	 * @return boolean Status of the command
	 */
	pause($pause)
<p/>

    /**
	 * Stops playing.
	 * 
	 * @return boolean Status of the command
	 */
	stop()
<p/>

    /**
	 * Begins playing the playlist at song number SONGPOS.
	 * 
	 * @param integer $song_pos The song position in playlist
	 * @return boolean Status of the command
	 */
	play($song_pos)
<p/>

    /**
	 * Begins playing the playlist at song SONGID. 
	 * 
	 * @param integer $id The song ID
	 * @return boolean Status of the command
	 */
	play_id($id)
<p/>

    /**
	 * Seeks to the position TIME (in seconds) of entry SONGPOS in the playlist.
	 * 
	 * @param integer $song_pos The song position in playlist
	 * @param integer $time Position time in seconds
	 * @return boolean Status of the command
	 */
	seek($song_pos, $time)
<p/>

    /**
	 * Seeks to the position TIME (in seconds) of song SONGID.
	 * 
	 * @param integer $song_id ID of the song
	 * @param integer $time Position time in seconds
	 * @return boolean Status of the command
	 */
	seek_id($song_id, $time)
<p/>

    /**
	 * Adds the file URI to the playlist (directories add recursively). URI can also be a single file.
	 * 
	 * @param string $uri The file or dir to add
	 * @return boolean Status of the command
	 */
	playlist_add($uri)
<p/>

     /**
	 * Adds a song to the playlist (non-recursive) and returns the song id.
	 * URI is always a single file or URL. For example:
	 * addid "foo.mp3"
	 * Id: 999
	 * OK
	 * 
	 * @param string $uri The file or dir to add
	 * @param integer $pos Position in the playlist
	 * @return boolean Status of the command
	 */
	playlist_add_id($uri, $pos)
<p/>

    /**
	 * Clears the current playlist. 
	 * 
	 * @return boolean Status of the command
	 */
	playlist_clear()
<p/>
  
    /**
	 * Deletes the song SONGID from the playlist
	 * 
	 * @param integer $id ID to remove
	 * @return boolean Status of the command
	 */
	playlist_remove($id)
<p/>

    /**
	 * Moves the song at FROM to TO in the playlist.
	 * 
	 * @param integer $from From playlist position
	 * @param integer $to To playlist position
	 * @return boolean Status of the command
	 */
	playlist_move($from, $to)
<p/>

    /**
	 * Moves the range of songs at START:END to TO in the playlist.
	 * 
	 * @param integer $start Start position
	 * @param integer $end End position
	 * @param integer $to New position
	 * @return boolean Status of the command
	 */
	playlist_move_multi($start, $end, $to)
<p/>

    /**
	 * Moves the song with FROM (songid) to TO (playlist index) in the playlist. 
	 * If TO is negative, it is relative to the current song in the playlist (if there is one). 
	 * 
	 * @param integer $from From song id
	 * @param integer $to To playlist index
	 * @return boolean Status of the command
	 */
	playlist_move_id($from, $to)
<p/>

    /**
	 * Shuffles the current playlist.
	 * 
	 * @return boolean Status of the command
	 */
	playlist_shuffle()
<p/>

    /**
	 * Lists the contents of the directory URI.
	 * 
	 * @param string $uri The dir to display, default is root dir
	 * @return boolean|array Returns false if command failed and an array containing the dirlist and other stuffs on success ;)
	 */
	dir_list($uri = '')
<p/>

    /**
	 * List all genres
	 * 
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	list_genres()
<p/>

    /**
	 * List all artists
	 * 
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	list_artists()
<p/>

    /**
	 * List all albums
	 * 
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	list_albums()
<p/>

    /**
	 * Searches for any song that contains WHAT. The search WHAT is not case sensitive.
	 * TYPE can be any tag supported by MPD, or one of the two special parameters — 
	 * file to search by full path (relative to database root), and any to match against all available tags
	 * 
	 * @param string $type Type to search for
	 * @param string $what case insensitive search string
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	search($type, $what)
<p/>

    /**
	 * Counts the number of songs and their total playtime in the db matching WHAT exactly.
	 * 
	 * @param string $type Type to search for
	 * @param string $what case sensitive search string
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	count($type, $what)
<p/>

    /**
	 * Updates the music database: find new files, remove deleted files, update modified files.
	 * URI is a particular directory or song/file to update. If you do not specify it, everything is updated.
	 * 
	 * @param string $uri (Optional) If URI is give database only updates files in that URI
	 * @return boolean|array Returns false if command fails and returns updating_db: JOBID where JOBID is a positive number identifying the update job on success
	 */
	update_db($uri = '')
<p/>

    /**
	 * Displays the song info of the current song (same song that is identified in status).
	 * 
	 * @return boolean|array Returns false on failure and current song on success
	 */
	current_song()
<p/>

    /**
	 * Displays a list of all songs in the playlist.
	 * 
	 * @return array Returns an array containing the songs in the playlist
	 */
	playlist()
<p/>

    /**
	 * Reports the current status of the player and the volume level.
	 * 
	 * @return array Returns and array containing the status
	 */
	server_status()
<p/>

    /**
	 * Displays statistics.
	 * 
	 * @return array Returns an array containing the statistics
	 */
	server_stats()
<p/>

    /**
	 * Prints a list of the playlist directory.
	 * 
	 * @return boolean|array Returns false on failure and playlists on success 
	 */
	playlists()
<p/>

    /**
	 * Lists the songs with metadata in the playlist. Playlist plugins are supported. 
	 * 
	 * @param string $playlist Name of the playlist to display
	 * @return boolean|array Returns false on failure and playlist info on success
	 */
	playlistinfo($playlist)
<p/>

    /**
	 * Loads the playlist into the current queue. Playlist plugins are supported. 
	 * 
	 * @param string $playlist Playlist to load
	 * @return boolean Returns false on failure and true on success
	 */
	load_playlist($playlist)
<p/>

    /**
	 * Adds URI to the playlist NAME.m3u.
	 * NAME.m3u will be created if it does not exist.
	 * 
	 * @param string $playlist Playlist to add to
	 * @param string $uri URI to add
	 * @return boolean Returns false on failure and true on success
	 */
	add_to_playlist($playlist, $uri)
<p/>

    /**
	 * Clears the playlist NAME.m3u.
	 * 
	 * @param string $playlist Playlist to clear
	 * @return boolean Returns false on failure and true on success
	 */
	clear_playlist($playlist)
<p/>

    /**
	 * Deletes SONGPOS from the playlist NAME.m3u
	 * 
	 * @param string $playlist Playlist to remove from
	 * @param integer $song_pos Position of the song to remove
	 * @return boolean Returns false on failure and true on success
	 */
	remove_from_playlist($playlist, $song_pos)
<p/>

    /**
	 * Moves SONGID in the playlist NAME.m3u to the position SONGPOS.
	 * 
	 * @param string $playlist The playlist to interact with
	 * @param integer $song_id I of the song to move
	 * @param integer $song_pos Position of the new position in the playlist
	 * @return boolean Returns false on failure and true on success
	 */
	move_song_in_playlist($playlist, $song_id, $song_pos)
<p/>

    /**
	 * Renames the playlist NAME.m3u to NEW_NAME.m3u.
	 * 
	 * @param string $playlist Playlist to rename
	 * @param strnig $new_name New name of playlist
	 * @return boolean Returns false on failure and true on success
	 */
	rename_playlist($playlist, $new_name)
<p/>

    /**
	 * Removes the playlist NAME.m3u from the playlist directory.
	 * 
	 * @param string $playlist Playlist to remove
	 * @return boolean Returns false on failure and true on success
	 */
	remove_playlist($playlist)
<p/>

    /**
	 * Saves the current playlist to NAME.m3u in the playlist directory.
	 * 
	 * @param string $playlist Name of the new playlist
	 * @return boolean Returns false on failure and true on success
	 */
	save_playlist($playlist)
<p/>

    /**
	 * Closes the connection to MPD.
	 * 
	 * @return boolean Returns false on failure and true on success
	 */
	close()
<p/>

    /**
	 * Kills MPD.
	 * 
	 * @return boolean Returns false on failure and true on success
	 */
	kill()
<p/>

    /**
	 * Send a command to the MPD server.
	 * 
	 * @param string $cmd The command to send
	 * @param array $args An array with the command arguments
	 * @return boolean|string Returns false if the command fails else returns the command result from the MPD server
	 */
	cmd($cmd, $args = array())
<p/>

    /**
	 * Updates the object variables.
	 * 
	 * @return boolean Returns false if something went wrong else returns true
	 */
	update()
<p/>

    /**
	 * Get the an array of error messages that has been collected since the object was instantiated.
	 * 
	 * @return array Returns an array with error messages
	 */
	get_error()
<p/>

    /**
	 * Get the MPD connection status.
	 * 
	 * @return boolean Returns true if connected to MPD server, else returns false
	 */
	get_connection_status()
<p/>

    /**
	 * Get the MPD version.
	 * 
	 * @return string Returns the version of the MPD
	 */
	get_version()
<p/>

    /**
	 * Get the current debug log.
	 * 
	 * @return array Returns the debug log
	 */
	get_debug_log()
<p/>

    /**
	 * Get the MPD class version.
	 * 
	 * @return string Returns the currenct version of this MPD class
	 */
	get_php_mpd_version()
