<?php
/**
* Provides the user class
*/

/**
* Manages the current user
*
*/
class user
{
    /**
    * @var int The databaseid of the user
    */
    private $id;
    /**
    * @var int The php session id of the current user
    */
    private $session_id;
     /**
    * @var string The code returned by google when the user is authed
    */
    private $googleAuthCode;
     /**
    * @var string The access token returned by google for refreshing the bearer token
    */
    private $accessToken;
     /**
    * @var string The beared token returned by google for accessing end user data
    */
    private $bearerToken;
     /**
    * @var string The unique id that identifies this user. May cover more than one google 'account' but only one person
    */
    private $googleSub;
     /**
    * @var string The display name the user has specified
    */
    private $displayName;

  
    /**
     * Construtor method
     */

    function __construct() {
        $this->session_id = session_id();
        if(isset($_SESSION['googleSub']))
        {
            $this->googleSub = $_SESSION['googleSub'];
        }

        // Fetch the user id from the database if one exists
        $db = newMysqliObject();
        $vendor = 'google';
        // If we have a user id for this user, assign it to a class var
        $stmtFetchUserId = $db->prepare("SELECT `id` from users WHERE `vendor` = ? AND `vendorid` = ?");
        $stmtFetchUserId->bind_param('ss',$vendor,$this->googleSub);
        $stmtFetchUserId->execute();
        $stmtFetchUserId->bind_result($id);
        while($stmtFetchUserId->fetch())
        {
            $this->id = $id;
        }
       
       
    }




    /**
     * Returns a URL for authenticating this user with google
     */
    public function generateGoogleOauthUrl() {
        global $client_id;
        global $callbackUri;

        // Generate a state nonce

        $url = "https://accounts.google.com/o/oauth2/v2/auth?client_id=" . $client_id .
                "&redirect_uri=" . $callbackUri . 
                "&response_type=code" . 
                "&scope=https://www.googleapis.com/auth/userinfo.email";
        return $url;
    }

    /**
     * Saves the users google auth code
     */
    public function saveGoogleAuthCode ($code)
    {
        $this->googleAuthCode = $code;
    }

    public function requestAccessToken()
    {   
        
        global $client_id;
        global $client_secret;
        global $callbackUri;

 

        // Check we have an auth token
        if(!$this->googleAuthCode) { return false; }
        
        $url = 'https://oauth2.googleapis.com/token';
        
        $paramaters = array('client_id' => $client_id,
                            'client_secret' => $client_secret,
                            'code' => $this->googleAuthCode,
                            'grant_type' => 'authorization_code',
                            'redirect_uri' => $callbackUri);

        $paramatersEncoded = http_build_query($paramaters);

        // Build the curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramatersEncoded);
        $output = curl_exec($ch);
        curl_close($ch);

        // Save the output to object vars
        $decodedOutput = json_decode($output, true);
        $this->accessToken = $decodedOutput['access_token'];
        $this->bearerToken = $decodedOutput['id_token'];

        return true;
        

    }

    /**
     * Fetches the current user information from google and saves it
     */
    public function fetchUserGoogleInfo()
    {
        $url = 'https://www.googleapis.com/oauth2/v3/tokeninfo';

        $paramaters = array('Authorisation' => 'Bearer ' . $this->bearerToken,
                            'id_token' => $this->bearerToken);

        $paramatersEncoded = http_build_query($paramaters);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramatersEncoded);
        $output = curl_exec($ch);
        curl_close($ch);

        // Save the output to object vars
        $decodedOutput = json_decode($output, true);
        $this->googleSub = $decodedOutput['sub'];
        $_SESSION['googleSub'] = $this->googleSub;
        
    }

    /**
     * Checks if the user is authenticated against google
     */
    public function isGoogleAuthed()
    {
        if(isset($this->googleSub)){
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns the users google auth id, if logged in with one
     */
    public function getGoogleAuthId()
    {
        if(isset($this->googleSub)){
            return $this->googleSub;
        }
        else
        {
            return false;
        }
    }

    /**
     * Checks it the user is already in the database
     */
    public function checkIfUserInDatabase()
    {

        $db = newMysqliObject();

        $vendor = 'google';
        // See if a user already exists for this user
        $stmtCheckUserExists = $db->prepare("SELECT `id` from users WHERE `vendor` = ? AND `vendorid` = ?");
        $stmtCheckUserExists->bind_param('ss',$vendor,$this->googleSub);
        $stmtCheckUserExists->execute();
        $stmtCheckUserExists->store_result();
        
        // Test if we got a result
        ($stmtCheckUserExists->num_rows == '1') ? $result = true :  $result = false;
       return $result;
    }

    
    public function addToDatabase()
    {

        $db = newMysqliObject();

        $vendor = 'google';
        // Insert the user into the database
        $stmtCheckAddUserToDatabase = $db->prepare("INSERT INTO users (`vendor`,`vendorid`) VALUES (?,?)");
        $stmtCheckAddUserToDatabase->bind_param('ss',$vendor,$this->googleSub);
        $stmtCheckAddUserToDatabase->execute();
        
    }


    /**
     * If one is assigned, returns user id, else returns false
     */
    public function getUserId()
    {
        if(!empty($this->id))
        {
            return $this->id;
        }
        else
        {
            return false;
        }
    }
}
