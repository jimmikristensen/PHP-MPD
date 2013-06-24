<?php
/* 
 * PHP MPD (mpd.class.php) is a single PHP class that gives easy access to 
 * a MPD (Music Player Daemon) server from any web application.
 * Copyright (C) 2011  Jimmi Kristensen (jimmi@linuxvibe.dk)
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

define("RES_OK", "OK");
define("RES_ERR", "ACK");
define("CMD_PWD", "password");
define("CMD_STATS", "stats");
define("CMD_STATUS", "status");
define("CMD_PLIST", "playlistinfo");
define("STATE_PLAYING", "play");
define("STATE_STOPPED", "stop");
define("STATE_PAUSED", "pause");
define("CMD_CONSUME", "consume");
define("CMD_XFADE", "crossfade");
define("CMD_RANDOM", "random");
define("CMD_REPEAT", "repeat");
define("CMD_SETVOL", "setvol");
define("CMD_SINGLE", "single");
define("CMD_NEXT", "next");
define("CMD_PREV", "previous");
define("CMD_PAUSE", "pause");
define("CMD_PLAY", "play");
define("CMD_PLAYID", "playid");
define("CMD_SEEK", "seek");
define("CMD_SEEKID", "seekid");
define("CMD_STOP", "stop");
define("CMD_PL_ADD", "add");
define("CMD_PL_ADDID", "addid");
define("CMD_PL_CLEAR", "clear");
define("CMD_PL_DELETEID", "deleteid");
define("CMD_PL_MOVE", "move");
define("CMD_PL_MOVE_MULTI", "move");
define("CMD_PL_MOVE_ID", "moveid");
define("CMD_PL_SHUFFLE", "shuffle");
define("CMD_DB_DIRLIST", "lsinfo");
define("CMD_DB_LIST", "list");
define("CMD_DB_SEARCH", "search");
define("CMD_DB_COUNT", "count");
define("CMD_DB_UPDATE", "update");
define("CMD_CURRENTSONG", "currentsong");
define("CMD_LISTPLAYLISTS", "listplaylists");
define("CMD_PLAYLISTINFO", "listplaylistinfo");
define("CMD_PLAYLISTLOAD", "load");
define("CMD_PLAYLISTADD", "playlistadd");
define("CMD_PLAYLISTCLEAR", "playlistclear");
define("CMD_PLAYLISTDELETE", "playlistdelete");
define("CMD_PLAYLISTMOVE", "playlistmove");
define("CMD_PLAYLISTRENAME", "rename");
define("CMD_PLAYLISTREMOVE", "rm");
define("CMD_PLAYLISTSAVE", "save");
define("CMD_CLOSE", "close");
define("CMD_KILL", "kill");


class MPD {
	
	private $php_mpd_version = '1.1';
	private $host;
	private $port;
	private $pwd;
	private $sock;
	private $conn_timeout;
	private $err_log = array();
	private $is_connected = false;
	private $version;
	private $debug_mode = true;
	private $debug_log = array();
	private $playlist;
	private $server_status;
	private $server_statistics;
	private $state;
	private $current_track_id;
	private $current_track_pos;
	private $current_track_len;
	private $repeat;
	private $random;
	private $volume;
	private $db_last_updated;
	private $uptime;
	private $playtime;
	private $num_artists;
	private $num_songs;
	private $num_albums;
	private $consume;
	private $xfade;
	private $bitrate;
	private $audio;
	private $single;
	
	function MPD() {
		
	}
	
	/**
	 * Instantiate the MPD object 
	 * 
	 * @param string $host The hostname or IP address of MPD server
	 * @param integer $port The port number of the MPD server
	 * @param string $pwd (Optional) The password of the MPD server
	 * @param integer $conn_timeout (Optional) Timeout of the MPD connection
	 * @return boolean Returns the connection state
	 */
	function load_MPD($host, $port, $pwd = '', $conn_timeout = 10) {
		$this->host = $host;
		$this->port = $port;
		$this->pwd = $pwd;
		$this->conn_timeout = $conn_timeout;
		
		$conn = $this->connect();
		if ($conn === false) {
			return false;
		} else {
			list ($this->version) = sscanf($conn, RES_OK . " MPD %s\n");
			if ($pwd != '') {
				if ($this->cmd(CMD_PWD, array($pwd)) === false) {
					$this->is_connected = false;
					$this->err_log[] = "Invalid password";
					return false;
				}
				
				if ($this->update() === false) {
					$this->is_connected = false;
					$this->err_log[] = "Given password does not have read access";
					return false;
				}
				
			} else {
				if ($this->update() === false) {
					$this->err_log[] = "Password required to access server";
					return false;
				}
			}
		}
		return $this->is_connected;
	}
	
	/**
	 * Connects to MPD
	 * 
	 * @return boolean Returns false if it was unable to connect and true if it connected
	 */
	private function connect() {
		$this->sock = fsockopen($this->host, $this->port, $err_no, $err_str, $this->conn_timeout);
		if (!$this->sock) {
			$this->err_log[] = "Connection error: $err_str ($err_no)";
			return false;
		} else {
			while (!feof($this->sock)) {
				$res = fgets($this->sock, 1024);
				if (strncmp(RES_OK, $res, strlen(RES_OK)) == 0) {
					$this->is_connected = true;
					return true;
				} else {
					$this->err_log[] = "Server response: $res";
					return false;
				}
			}
			$this->err_log[] = "Unknown";
			return false;
		}
	}
	
	/**
	 * Sets consume state to STATE, STATE should be 0 or 1. When consume is activated, each song played is removed from playlist. 
	 * 
	 * @param integer $state The consume state
	 * @return boolean Status of the command
	 */
	function consume($state) {
		if (!is_numeric($seconds) || $state < 0 || $state > 1) {
			$this->err_log[] = "Consume state should be 0 or 1";
			return false;
		}
		
		if ($this->cmd(CMD_CONSUME, array($state)) !== false) {
			$this->consume = $state;
			return true;
		}
		return false;
	}

	/**
	 * Sets crossfading between songs.
	 * 
	 * @param integer $seconds Crossfade seconds
	 * @return boolean Status of the command
	 */
	function xfade($seconds) {
		if (!is_numeric($seconds)) {
			$this->err_log[] = "Crossfade should be numeric value in seconds";
			return false;
		}
		
		if ($this->cmd(CMD_XFADE, array($seconds)) !== false) {
			$this->xfade = $seconds;
			return true;
		}
		return false;
	}
	
	/**
	 * Sets random state to STATE, STATE should be 0 or 1.
	 * 
	 * @param integer $state 0 = no and 1 = yes
	 * @return boolean Status of the command
	 */
	function random($state) {
		if (!is_numeric($state) || $state < 0 || $state > 1) {
			$this->err_log[] = "Random should be 0 or 1";
			return false;
		}
		
		if ($this->cmd(CMD_RANDOM, array($state)) !== false) {
			$this->random = $state;
			return true;
		}
		return false;
	}
	
	/**
	 * Sets repeat state to STATE, STATE should be 0 or 1.
	 * 
	 * @param integer $state 0 = no and 1 = yes
	 * @return boolean Status of the command
	 */
	function repeat($state) {
		if (!is_numeric($state) || $state < 0 || $state > 1) {
			$this->err_log[] = "Repeat should be 0 or 1";
			return false;
		}
		
		if ($this->cmd(CMD_REPEAT, array($state)) !== false) {
			$this->repeat = $state;
			return true;
		}
		return false;
	}
	
	/**
	 * Sets volume to VOL, the range of volume is 0-100.
	 * 
	 * @param integer $vol The new volume
	 * @return boolean Status of the command
	 */
	function setvol($vol) {
		if (!is_numeric($vol) || $vol > 100 || $vol < 0) {
			$this->err_log[] = "Volume must be numeric and between 0 and 100";
			return false;
		}
		
		if ($this->cmd(CMD_SETVOL, array($vol)) !== false) {
			$this->volume = $vol;
			return true;
		}
		return false;
	}
	
	/**
	 * Adjusts the volume up or down depending if the modifier is positive or negative
	 * 
	 * @param integer $mod Volume modification
	 * @return boolean Status of the command
	 */
	function adjust_vol($mod) {
		if (!is_numeric($mod)) {
			$this->err_log[] = "Volume modification must be a numeric value";
			return false;
		}
		
		$vol = $this->volume + $mod;
		if ($this->setvol($vol) === true) {
			return true;
		}
		return false;
	}
	
	/**
	 * Sets single state to STATE, STATE should be 0 or 1. When single is activated, playback 
	 * is stopped after current song, or song is repeated if the 'repeat' mode is enabled. 
	 * 
	 * @param integer $state Activate or deactivate single
	 * @return boolean Status of the command
	 */
	function single($state) {
		if (!is_numeric($state) || $state < 0 || $state > 1) {
			$this->err_log[] = "Single should be 0 or 1";
			return false;
		}
		
		if ($this->cmd(CMD_SINGLE, array($state)) !== false) {
			$this->single = $state;
			return true;
		}
		return false;
	}
	
	/**
	 * Plays next song in the playlist.
	 * 
	 * @return boolean Status of the command
	 */
	function next() {
		if ($this->cmd(CMD_NEXT) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Plays previous song in the playlist.
	 * 
	 * @return boolean Status of the command
	 */
	function prev() {
		if ($this->cmd(CMD_PREV) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Toggles pause/resumes playing, PAUSE is 0 or 1. 
	 * 
	 * @param integer $pause 0 = play and 1 = pause
	 * @return boolean Status of the command
	 */
	function pause($pause) {
		if (!is_numeric($pause) || $pause < 0 || $pause > 1) {
			$this->err_log[] = "Pause should be 0 or 1";
			return false;
		}
		
		if ($this->cmd(CMD_PAUSE, array($pause)) !== false) {
			if ($pause == 1) {
				$this->state = STATE_PAUSED;
			} else {
				$this->state = STATE_PLAYING;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Stops playing.
	 * 
	 * @return boolean Status of the command
	 */
	function stop() {
		if ($this->cmd(CMD_STOP) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Begins playing the playlist at song number SONGPOS.
	 * 
	 * @param integer $song_pos The song position in playlist
	 * @return boolean Status of the command
	 */
	function play($song_pos) {
		if (!is_numeric($song_pos)) {
			$this->err_log[] = "Song position should be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PLAY, array($song_pos)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Begins playing the playlist at song SONGID. 
	 * 
	 * @param integer $id The song ID
	 * @return boolean Status of the command
	 */
	function play_id($id) {
		if (!is_numeric($id)) {
			$this->err_log[] = "Song ID should be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PLAYID, array($id)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Seeks to the position TIME (in seconds) of entry SONGPOS in the playlist.
	 * 
	 * @param integer $song_pos The song position in playlist
	 * @param integer $time Position time in seconds
	 * @return boolean Status of the command
	 */
	function seek($song_pos, $time) {
		if (!is_numeric($song_pos) || !is_numeric($time)) {
			$this->err_log[] = "Song position and time should both be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_SEEK, array($song_pos, $time)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Seeks to the position TIME (in seconds) of song SONGID.
	 * 
	 * @param integer $song_id ID of the song
	 * @param integer $time Position time in seconds
	 * @return boolean Status of the command
	 */
	function seek_id($song_id, $time) {
		if (!is_numeric($song_id) || !is_numeric($time)) {
			$this->err_log[] = "Song ID and time should both be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_SEEKID, array($song_pos, $time)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Adds the file URI to the playlist (directories add recursively). URI can also be a single file.
	 * 
	 * @param string $uri The file or dir to add
	 * @return boolean Status of the command
	 */
	function playlist_add($uri) {
		if ($this->cmd(CMD_PL_ADD, array($uri)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
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
	function playlist_add_id($uri, $pos) {
		if (!is_numeric($pos)) {
			$this->err_log[] = "Song ID and time should both be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PL_ADDID, array($uri, $pos)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Clears the current playlist. 
	 * 
	 * @return boolean Status of the command
	 */
	function playlist_clear() {
		if ($this->cmd(CMD_PL_CLEAR) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Deletes the song SONGID from the playlist
	 * 
	 * @param integer $id ID to remove
	 * @return boolean Status of the command
	 */
	function playlist_remove($id) {
		if (!is_numeric($id)) {
			$this->err_log[] = "Song ID must be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PL_DELETEID, array($id)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Moves the song at FROM to TO in the playlist.
	 * 
	 * @param integer $from From playlist position
	 * @param integer $to To playlist position
	 * @return boolean Status of the command
	 */
	function playlist_move($from, $to) {
		if (!is_numeric($from) || !is_numeric($to)) {
			$this->err_log[] = "From and To has to be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PL_MOVE, array($from, $to)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Moves the range of songs at START:END to TO in the playlist.
	 * 
	 * @param integer $start Start position
	 * @param integer $end End position
	 * @param integer $to New position
	 * @return boolean Status of the command
	 */
	function playlist_move_multi($start, $end, $to) {
		if (!is_numeric($start) || !is_numeric($end) || !is_numeric($end)) {
			$this->err_log[] = "Start, End and To has to be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PL_MOVE_MULTI, array("$start:$end", $to)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Moves the song with FROM (songid) to TO (playlist index) in the playlist. 
	 * If TO is negative, it is relative to the current song in the playlist (if there is one). 
	 * 
	 * @param integer $from From song id
	 * @param integer $to To playlist index
	 * @return boolean Status of the command
	 */
	function playlist_move_id($from, $to) {
		if (!is_numeric($from) || !is_numeric($end)) {
			$this->err_log[] = "From and To has to be numeric";
			return false;
		}
		
		if ($this->cmd(CMD_PL_MOVE_ID, array($from, $to)) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Shuffles the current playlist.
	 * 
	 * @return boolean Status of the command
	 */
	function playlist_shuffle() {
		if ($this->cmd(CMD_PL_SHUFFLE) !== false) {
			$this->update();
			return true;
		}
		return false;
	}
	
	/**
	 * Lists the contents of the directory URI.
	 * 
	 * @param string $uri The dir to display, default is root dir
	 * @return boolean|array Returns false if command failed and an array containing the dirlist and other stuffs on success ;)
	 */
	function dir_list($uri = '') {
		$dir_list = $this->cmd(CMD_DB_DIRLIST, array($uri));
		if ($dir_list !== false) {
			return $this->parse_playlist($dir_list);
		}
		return false;
	}
	
	/**
	 * List all genres
	 * 
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	function list_genres() {
		$genre_list = $this->cmd(CMD_DB_LIST, array('genre'));
		if ($genre_list !== false) {
			return $this->parse_list($genre_list);
		}
		return false;
	}
	
	/**
	 * List all artists
	 * 
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	function list_artists() {
		$artist_list = $this->cmd(CMD_DB_LIST, array('artist'));
		if ($artist_list !== false) {
			return $this->parse_list($artist_list);
		}
		return false;
	}
	
	/**
	 * List all albums
	 * 
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	function list_albums() {
		$album_list = $this->cmd(CMD_DB_LIST, array('album'));
		if ($album_list !== false) {
			return $this->parse_list($album_list);
		}
		return false;
	}
	
	/**
	 * Searches for any song that contains WHAT. The search WHAT is not case sensitive.
	 * TYPE can be any tag supported by MPD, or one of the two special parameters — 
	 * file to search by full path (relative to database root), and any to match against all available tags
	 * 
	 * @param string $type Type to search for
	 * @param string $what case insensitive search string
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	function search($type, $what) {
		if ($this->validate_type($type) === false || $what == '') {
			$this->err_log[] = "Invalid TYPE or WHAT empty";
			return false;
		}
		
		$search_res = $this->cmd(CMD_DB_SEARCH, array($type, $what));
		if ($search_res !== false) {
			return $this->parse_playlist($search_res);
		}
		return false;
	}
	
	/**
	 * Counts the number of songs and their total playtime in the db matching WHAT exactly.
	 * 
	 * @param string $type Type to search for
	 * @param string $what case sensitive search string
	 * @return boolean|array Returns false if command failed and an array containing the result on success
	 */
	function count($type, $what) {
		if ($this->validate_type($type) === false || $what == '') {
			$this->err_log[] = "Invalid TYPE or WHAT empty";
			return false;
		}
		
		$count_res = $this->cmd(CMD_DB_COUNT, array($type, $what));
		if ($count_res !== false) {
			return $this->parse_list($count_res, true);
		}
	}
	
	/**
	 * Updates the music database: find new files, remove deleted files, update modified files.
	 * URI is a particular directory or song/file to update. If you do not specify it, everything is updated.
	 * 
	 * @param string $uri (Optional) If URI is give database only updates files in that URI
	 * @return boolean|array Returns false if command fails and returns updating_db: JOBID where JOBID is a positive number identifying the update job on success
	 */
	function update_db($uri = '') {
		$update_res = $this->cmd(CMD_DB_UPDATE, array($uri));
		if ($update_res !== false) {
			$this->update();
			return $this->parse_list($update_res, true);
		}
	}
	
	/**
	 * Displays the song info of the current song (same song that is identified in status).
	 * 
	 * @return boolean|array Returns false on failure and current song on success
	 */
	function current_song() {
		$cur_song_res = $this->cmd(CMD_CURRENTSONG);
		if ($cur_song_res !== false) {
			return $this->parse_playlist($cur_song_res);
		}
		return false;
	}
	
	/**
	 * Displays a list of all songs in the playlist.
	 * 
	 * @return array Returns an array containing the songs in the playlist
	 */
	function playlist() {
		return $this->playlist;
	}
	
	/**
	 * Reports the current status of the player and the volume level.
	 * 
	 * @return array Returns and array containing the status
	 */
	function server_status() {
		return $this->server_status;
	}
	
	/**
	 * Displays statistics.
	 * 
	 * @return array Returns an array containing the statistics
	 */
	function server_stats() {
		return $this->server_statistics;
	}
	
	/**
	 * Prints a list of the playlist directory.
	 * 
	 * @return boolean|array Returns false on failure and playlists on success 
	 */
	function playlists() {
		$list_pl_res = $this->cmd(CMD_LISTPLAYLISTS);
		if ($list_pl_res !== false) {
			return $this->parse_playlist($list_pl_res);
		}
		return false;
	}
	
	/**
	 * Lists the songs with metadata in the playlist. Playlist plugins are supported. 
	 * 
	 * @param string $playlist Name of the playlist to display
	 * @return boolean|array Returns false on failure and playlist info on success
	 */
	function playlistinfo($playlist) {
		if ($playlist == '') {
			$this->err_log[] = "Playlist name must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTINFO, array($playlist));
		if ($pl_res !== false) {
			return $this->parse_playlist($pl_res);
		}
		return false;		
	}
	
	/**
	 * Loads the playlist into the current queue. Playlist plugins are supported. 
	 * 
	 * @param string $playlist Playlist to load
	 * @return boolean Returns false on failure and true on success
	 */
	function load_playlist($playlist) {
		if ($playlist == '') {
			$this->err_log[] = "Playlist name must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTLOAD, array($playlist));
		if ($pl_res !== false) {
			return true;
		}
		return false;		
	}
	
	/**
	 * Adds URI to the playlist NAME.m3u.
	 * NAME.m3u will be created if it does not exist.
	 * 
	 * @param string $playlist Playlist to add to
	 * @param string $uri URI to add
	 * @return boolean Returns false on failure and true on success
	 */
	function add_to_playlist($playlist, $uri) {
		if ($playlist == '' || $uri == '') {
			$this->err_log[] = "Playlist name and URI must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTADD, array($playlist, $uri));
		if ($pl_res !== false) {
			return true;
		}
		return false;	
	}
	
	/**
	 * Clears the playlist NAME.m3u.
	 * 
	 * @param string $playlist Playlist to clear
	 * @return boolean Returns false on failure and true on success
	 */
	function clear_playlist($playlist) {
		if ($playlist == '') {
			$this->err_log[] = "Playlist name must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTCLEAR, array($playlist));
		if ($pl_res !== false) {
			return true;
		}
		return false;	
	}
	
	/**
	 * Deletes SONGPOS from the playlist NAME.m3u
	 * 
	 * @param string $playlist Playlist to remove from
	 * @param integer $song_pos Position of the song to remove
	 * @return boolean Returns false on failure and true on success
	 */
	function remove_from_playlist($playlist, $song_pos) {
		if ($playlist == '' || !is_numeric($song_pos)) {
			$this->err_log[] = "Playlist name and song position must not be empty and song position must be numeric";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTDELETE, array($playlist, $song_pos));
		if ($pl_res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Moves SONGID in the playlist NAME.m3u to the position SONGPOS.
	 * 
	 * @param string $playlist The playlist to interact with
	 * @param integer $song_id I of the song to move
	 * @param integer $song_pos Position of the new position in the playlist
	 * @return boolean Returns false on failure and true on success
	 */
	function move_song_in_playlist($playlist, $song_id, $song_pos) {
		if ($playlist == '' || !is_numeric($song_id) || !is_numeric($song_pos)) {
			$this->err_log[] = "Playlist name, song ID and song position must not be empty and song ID and song position must be numeric";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTMOVE, array($playlist, $song_id, $song_pos));
		if ($pl_res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Renames the playlist NAME.m3u to NEW_NAME.m3u.
	 * 
	 * @param string $playlist Playlist to rename
	 * @param strnig $new_name New name of playlist
	 * @return boolean Returns false on failure and true on success
	 */
	function rename_playlist($playlist, $new_name) {
		if ($playlist == '' || $new_name == '') {
			$this->err_log[] = "Playlist name and new name must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTRENAME, array($playlist, $new_name));
		if ($pl_res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Removes the playlist NAME.m3u from the playlist directory.
	 * 
	 * @param string $playlist Playlist to remove
	 * @return boolean Returns false on failure and true on success
	 */
	function remove_playlist($playlist) {
		if ($playlist == '') {
			$this->err_log[] = "Playlist name must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTREMOVE, array($playlist));
		if ($pl_res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Saves the current playlist to NAME.m3u in the playlist directory.
	 * 
	 * @param string $playlist Name of the new playlist
	 * @return boolean Returns false on failure and true on success
	 */
	function save_playlist($playlist) {
		if ($playlist == '') {
			$this->err_log[] = "Playlist name must not be empty";
			return false;
		}
		
		$pl_res = $this->cmd(CMD_PLAYLISTSAVE, array($playlist));
		if ($pl_res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Closes the connection to MPD.
	 * 
	 * @return boolean Returns false on failure and true on success
	 */
	function close() {
		$res = $this->cmd(CMD_CLOSE);
		if ($res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Kills MPD.
	 * 
	 * @return boolean Returns false on failure and true on success
	 */
	function kill() {
		$res = $this->cmd(CMD_KILL);
		if ($res !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Send a command to the MPD server.
	 * 
	 * @param string $cmd The command to send
	 * @param array $args An array with the command arguments
	 * @return boolean|string Returns false if the command fails else returns the command result from the MPD server
	 */
	function cmd($cmd, $args = array()) {
		if (!$this->is_connected) {
			$this->err_log[] = "Not connected";
			return false;
			
		} else if (!is_array($args)) {
			$this->err_log[] = "Arguments not presented as an array";
			return false;
			
		} else {
			$response_str = '';
			
			$cmd_str = '';
			foreach($args as $arg) {
				$cmd_str .= ' "'.$arg.'"';
			}
			$cmd_str = $cmd.$cmd_str;
			
			$this->log_input($cmd_str);
			
			fputs($this->sock, "$cmd_str\n");
			while (!feof($this->sock)) {
				$res = fgets($this->sock, 1024);
				
				$this->log_output($res);

				// ignoring OK signal at end of transmission
				if (strncmp(RES_OK, $res, strlen(RES_OK)) == 0) {
					break;
				}
				
				// catch the message at the end of transmission
				if (strncmp(RES_ERR, $res, strlen(RES_ERR)) == 0) {
					list ($tmp, $err) = explode(RES_ERR . ' ', $res);
					$this->err_log[] = strtok($err, "\n");
				}
				
				if (count($this->err_log) > 0) {
					return false;
				}
				
				$response_str .= $res;
			}
			return $response_str;
		}
	}
	
	/**
	 * Updates the object variables.
	 * 
	 * @return boolean Returns false if something went wrong else returns true
	 */
	function update() {
		$srv_stats = array();
		$srv_status = array();
		
		// get server stats
		$stats_res = $this->cmd(CMD_STATS);
		if ($stats_res === false) {
			$this->err_log[] = "Unable to retrieve server statistics";
			return false;
		} else {
			$srv_stats = $this->parse_list($stats_res, true);
		}
		$this->server_statistics = $srv_stats;
		
		// get Server Status
		$status_res = $this->cmd(CMD_STATUS);
		if ($status_res === false) {
			$this->err_log[] = "Unable to retrieve server status";
			return false;
		} else {
			$srv_status = $this->parse_list($status_res, true);
		}
		$this->server_status = $srv_status;

		// get playlist
		$plist_res = $this->cmd(CMD_PLIST);
		$this->playlist = $this->parse_playlist($plist_res);
		
		// other useful info
		$this->state = $srv_status['state'];
		if ($this->state == STATE_PLAYING || $this->state == STATE_PAUSED) {
			$this->current_track_id = $srv_status['songid'];
			list ($this->current_track_pos, $this->current_track_len) = explode(":", $srv_status['time']);
		} else {
			$this->current_track_id = -1;
			$this->current_track_pos = -1;
			$this->current_track_len = -1;
		}

		$this->repeat = $srv_status['repeat'];
		$this->random = $srv_status['random'];
		$this->volume = $srv_status['volume'];
		$this->consume = $srv_status['consume'];
		$this->xfade = $srv_status['xfade'];
		$this->bitrate = isset($srv_status['bitrate']) ? $srv_status['bitrate'] : 0;
		$this->audio = isset($srv_status['audio']) ? $srv_status['audio'] : 0;
		$this->single = $srv_status['single'];
		
		$this->db_last_updated = $srv_stats['db_update'];
		$this->uptime = $srv_stats['uptime'];
		$this->playtime = $srv_stats['playtime'];
		$this->num_artists = $srv_stats['artists'];
		$this->num_songs = $srv_stats['songs'];
		$this->num_albums = $srv_stats['albums'];
		
		return true;
	}

	/**
	 * Get the an array of error messages that has been collected since the object was instantiated.
	 * 
	 * @return array Returns an array with error messages
	 */
	function get_error() {
		return $this->err_log;
	}
	
	/**
	 * Get the MPD connection status.
	 * 
	 * @return boolean Returns true if connected to MPD server, else returns false
	 */
	function get_connection_status() {
		return $this->is_connected;
	}
	
	/**
	 * Get the MPD version.
	 * 
	 * @return string Returns the version of the MPD
	 */
	function get_version() {
		return $this->version;
	}
	
	/**
	 * Get the current debug log.
	 * 
	 * @return array Returns the debug log
	 */
	function get_debug_log() {
		return $this->debug_log;
	}
	
	/**
	 * Get the MPD class version.
	 * 
	 * @return string Returns the currenct version of this MPD class
	 */
	function get_php_mpd_version() {
		return $this->$php_mpd_version;
	}
	
	private function validate_type($type) {
		$type_valid = false;
		switch (strtolower($type)) {
			case 'file':
			case 'time':
			case 'artist':
			case 'albumartist':
			case 'title':
			case 'album':
			case 'track':
			case 'date':
			case 'genre':
			case 'any':
				$type_valid = true;
				break;
			default:
				$type_valid = false;
				break;
		}
		return $type_valid;
	}
	
	private function parse_list($list_res, $use_str_assoc = false) {
		$list = array();
		if ($list_res === false) {
			$this->err = "List is empty";
		} else {
			$list_line = strtok($list_res, "\n");
			while ($list_line) {
				list ($key, $value) = explode(": ", $list_line);
				if ($value != '') {
					if ($use_str_assoc === true) {
						$list[$key] = $value;
					} else {
						$list[] = $value;
					}
				}
				$list_line = strtok("\n");
			}
		}
		return $list;
	}
	
	private function parse_playlist($plist_res) {
		$playlist = array();
		
		if ($plist_res === false) {
			$this->err = "Playlist empty";
		} else {
			$plist_line = strtok($plist_res, "\n");
			$counter = -1;
			$type = 'unknown';
			while ($plist_line) {
				list ($key, $value) = explode(": ", $plist_line);
				if ($key == 'file' || $key == 'directory' || $key == 'playlist') {
					$type = $key;
					$counter++;
					
					$playlist[$counter]['type'] = $type;
					$playlist[$counter]['name'] = $value;
					$playlist[$counter]['basename'] = basename($value);
				} else {
					$playlist[$counter][$key] = $value;
				}
				
				$plist_line = strtok("\n");
			}
		}
		
		return $playlist;
	}
	
	private function log_input($msg) {
		if ($this->debug_mode == true) {
			$this->debug_log[] = "-> $msg";
		}
	}
	
	private function log_output($msg) {
		if ($this->debug_mode == true) {
			$this->debug_log[] = "<- $msg";
		}
	}
}
?>