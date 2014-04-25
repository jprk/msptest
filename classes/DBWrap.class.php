<?php
class DBWrap {

	var $_config;   // Holds configuration options
	var $_link;     // Holds the active database link
	var $_host;     // Hostname of the database server
	var $_user;     // Username of the database user
	var $_pass;     // Password of the database user
	var $_database; // Name of the database that we shall connect to
	
	function _construct ( $host, $user, $pass, $database )
	{
		$this->_host     = $host;
		$this->_user     = $user;
		$this->_pass     = $pass;
		$this->_database = $database;
	}
	
	/* Open a database connection */
	function dbOpen ()
	{
		/* Try to connect to the database. Die if the database connection can
		   not be established. */
		$link = mysql_connect ( $this->_host, $this->_user, $this->_pass ) or
				  die ("Cannot connect to mySQL as '".$this->_user."@".$this->_host."'");
		/* Select the proper database */
		mysql_select_db ( $this->_database ) or 
				  die ("Cannot select database '" . $this->_data . 
				       "' as '" . $this->_user .
				       "@" . $this->_host . "'" );
		/* And store the database link. It will be needed at least when calling
		   mysql_close(). */
		$this->_link = $link;
	}

	/* Close database connection. */
	function dbClose ()
	{
		mysql_close ($this->_link);
	}

	/* Query the database and process the result */
	function dbQuery ($query)
	{
	    $result = mysql_query ($query) or 
		          die ("Wrong query: " . mysql_error());
        
		if ( ! is_bool ( $result ))
		{
			while ( $row = mysql_fetch_assoc ($result))
			{
				$asr[] = $row;
        	}
	    	
        	mysql_free_result ( $result );
		}
		
		return $asr;
	}
    
	function dbQuerySingle ($query)
	{
		$result = mysql_query ($query) or 
		          die ("Wrong query: " . mysql_error());
        
		$row = mysql_fetch_assoc ($result);
		
      mysql_free_result($result);
		
		return $row;
	}
	
	function getDbLink ()
	{
		return $this->link;
	}
}
?>
