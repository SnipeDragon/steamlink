<?php
namespace SnipeDragon;
/**
 * SteamLink Class
 *
 * @package   SteamLink
 * @author    Spencer Sword <snipedragon@gmail.com>
 * @license   https://opensource.org/licenses/MIT
 * @link      https://github.com/snipedragon/steamlink
 * @version   1.0.2
 */
class SteamLink {
    protected $options = array(
        "apiKey" => "", // Get one from https://steamcommunity.com/dev/apikey
        "domainName" => "", // Shown on the Steam Login page to your users.
        "loginRedirect" => "", // Returns user to this page on login.
        "logoutRedirect" => "", // Returns user to this page on logout.
        "startSession" => false
    );
    public function __construct($apiKey = null, $domainName = null, $loginRedirect = null, $logoutRedirect = null, $startSession = null) {
        if (is_array($apiKey)) {
            foreach ($apiKey as $key => $val) {
                $$key = $val;
            }
        }
        $this->options["apiKey"] = $apiKey;
        $this->options["domainName"] = $domainName;
        $this->options["loginRedirect"] = $loginRedirect;
        $this->options["logoutRedirect"] = $logoutRedirect;
        $this->options["startSession"] = $startSession;
        if ($this->options["apiKey"] == "") {
            die("<b>SteamLink:</b> Please provide an API key from https://steamcommunity.com/dev/apikey");
        }
        if ($this->options["loginRedirect"] == "") {
            $this->options["loginRedirect"] = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        }
        if (session_id() == "" && $startSession) {
            session_start();
        }
        if ($startSession) {
            if (isset($_GET["openid_assoc_handle"]) && !isset($_SESSION["steamdata"]["steamid"])) {
                $steamid = $this->validate();
                if ($steamid != "") {
                    @$resp = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $this->options["apiKey"] . "&steamids=" . $steamid), true);
                    foreach ($resp["response"]["players"][0] as $key => $value) {
                        $_SESSION["steamdata"][$key] = $value;
                    }
                }
            }
            if (isset($_SESSION["steamdata"]["steamid"])) {
                foreach ($_SESSION["steamdata"] as $key => $value) {
                    $this->{$key} = $value;
                } return true;
            }
        } else {
            if (isset($_GET["openid_assoc_handle"])) {
                $steamid = $this->validate();
                if ($steamid != "") {
                    @$resp = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $this->options["apiKey"] . "&steamids=" . $steamid), true);
                    foreach ($resp["response"]["players"][0] as $key => $value) {
                        $this->{$key} = $value;
                    } return true;
                }
            }
        }
    }
    public function loginButton($style="rectangle") {
        $params = array(
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $this->options["loginRedirect"],
            'openid.realm' => $this->options["domainName"],
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        );
        $button = "https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png";
        switch($style) {
            case "rectangle":
                $button = "https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png";
                break;
            case "square":
                $button = "https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_02.png";
                break;
        }
        return '<a href="https://steamcommunity.com/openid/login' . '?' . http_build_query($params, '', "&").'"><img src="'.$button.'"></img></a>';
    }
    private static function validate() {
        $params = array(
            'openid.assoc_handle' => $_GET['openid_assoc_handle'],
            'openid.signed' => $_GET['openid_signed'],
            'openid.sig' => $_GET['openid_sig'],
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
        );
        $signed = explode(',', $_GET['openid_signed']);
        foreach ($signed as $item) {
            $val = $_GET['openid_' . str_replace('.', '_', $item)];
            $params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($val) : $val;
        }
        $params['openid.mode'] = 'check_authentication';
        $data = http_build_query($params);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                "Accept-language: en\r\n" .
                "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data,
            ),
        ));
        $result = file_get_contents("https://steamcommunity.com/openid/login", false, $context);
        preg_match("#^https://steamcommunity.com/openid/id/([0-9]{17,25})#", $_GET['openid_claimed_id'], $matches);
        $steamID64 = is_numeric($matches[1]) ? $matches[1] : 0;
        return preg_match("#is_valid\s*:\s*true#i", $result) == 1 ? $steamID64 : '';
    }
    public function logout() {
        if(isset($_SESSION["steamdata"])) {
        unset($_SESSION["steamdata"]);
        if (!isset($_SESSION[0])) {
            session_destroy();
        }
        if ($this->options["logoutRedirect"] != "") {
            header("Location: " . $this->options["logoutRedirect"]);
        }
        return true;
        }
    }
    public function refreshLink($steamid) {
        if (!isset($steamid)) {
            return false;
        }
        @$resp = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $this->options["apiKey"] . "&steamids=" . $steamid), true);
        foreach ($resp["response"]["players"][0] as $key => $value) {
            $this->{$key} = $value;
        }
        return true;
    }
}