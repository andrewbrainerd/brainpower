<?php
/**
 * Created by PhpStorm.
 * User: abrainerd
 * Date: 11/10/2016
 * Time: 2:02 PM
 */

global $ldapconn;
$ldapconn = ldap_connect("ldap://umcudc-huron.thedomain.umcu.org");
$dn = "DC=thedomain,DC=umcu,DC=org";

if ($ldapconn) {
    $ldapbind = @ldap_bind($ldapconn, "CN=Andrew Brainerd,CN=Users," . $dn, "b0ggl3sth3m!nd");
    if ($ldapbind) {
        $dn = "OU=Administrators,DC=thedomain,DC=umcu,DC=org";
        $enabled = "(!(userAccountControl:1.2.840.113556.1.4.803:=2))";
        $filter = "(&(objectClass=user)$enabled(samaccountname=asyed-admin)(lockoutTime>=1))";
        $attributes = array(
            "distinguishedName",
            "samaccountname",
            "lastlogontimestamp",
            "lockouttime",
            "badpasswordtime",
            "badpwdcount"
        );
        $ldapresults = ldap_search($ldapconn, $dn, $filter, $attributes);
        if (!$ldapresults) die("Search Failed");
        if (ldap_count_entries($ldapconn, $ldapresults) > 0) {
            $results = ldap_get_entries($ldapconn, $ldapresults);
            ldap_free_result($ldapresults);
            foreach ($results as $key => $value) {
                if (is_array($value)) {
                    $userDN = $value["dn"];
                    $entries = array(
                        "lockoutTime" => 0
                    );
                    if (ldap_modify($ldapconn, $userDN, $entries)) {
                        $file = "/var/www/html/logs/unlocks";
                        $content = file_get_contents($file);
                        $content .= "Unlocked " . getName($userDN) . " [" . date("d-m-Y H:i") . "]\n";
                        file_put_contents($file, $content);
                    }
                }
            }
        }
    }
}
function getName($dn) {
    $groups = "";
    $items = explode(",", $dn);
    foreach ($items as $item) {
        $type = explode("=", $item)[0];
        $name = explode("=", $item)[1];
        if ($type == "CN" && $name != "Users") {
            $groups .= $name;
        }
    }
    return $groups;
}
function getGroup($dn) {
    $groups = "";
    $items = explode(",", $dn);
    foreach ($items as $item) {
        $type = explode("=", $item)[0];
        $name = explode("=", $item)[1];
        if ($type == "OU" && $name != "All Staff") {
            $groups .= $name;
        }
    }
    return $groups;
}
function formatTime($time) {
    return date("m-d-Y h:i", $time / 10000000 - 11644473600);
}